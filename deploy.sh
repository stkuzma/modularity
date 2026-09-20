#!/usr/bin/env bash
#
# Blue-green rollout.
#
#   ./deploy.sh green                    build, gate, switch traffic to green
#   ./deploy.sh green --tag v7           the same, running image modularity:v7
#   ./deploy.sh green --tag v7 --no-build   deploy an image built elsewhere
#   ./deploy.sh --rollback               send traffic back to the idle colour
#   ./deploy.sh --status                 report which colour is live
#
# The order of the steps below is the whole design. Traffic moves in step 6,
# and everything before it is undone by doing nothing.
#
set -euo pipefail

cd "$(dirname "$0")"

UPSTREAM_FILE=docker/nginx/upstream.conf
DRAIN_SECONDS="${DRAIN_SECONDS:-10}"
READY_TIMEOUT="${READY_TIMEOUT:-60}"
PORT="${APP_PORT:-8080}"

compose() { docker compose "$@"; }

say() { printf '\n\033[1m==> %s\033[0m\n' "$*"; }

live_colour() {
    grep -oE 'app_(blue|green)' "$UPSTREAM_FILE" | head -1 | sed 's/app_//'
}

other_colour() {
    [[ $1 == blue ]] && echo green || echo blue
}

running() {
    [[ -n "$(compose ps -q "app_$1" 2>/dev/null)" ]]
}

point_at() {
    cat > "$UPSTREAM_FILE" <<EOF
# Rewritten by deploy.sh on $(date -u +%Y-%m-%dT%H:%M:%SZ). Reload, not restart:
# in-flight requests finish on the colour that was serving them.
upstream app {
    server app_$1:9000;
}
EOF
    compose exec -T edge nginx -s reload
}

smoke() {
    curl -fsS --max-time 10 "http://localhost:${PORT}/api/health" > /dev/null
}

# --- status -----------------------------------------------------------------

if [[ "${1:-}" == "--status" ]]; then
    echo "live: $(live_colour)"
    compose ps --format 'table {{.Service}}\t{{.Status}}\t{{.Image}}'
    exit 0
fi

# --- rollback ---------------------------------------------------------------
#
# A rollback moves the arrow and nothing else. It deliberately does not start
# or rebuild anything: the idle colour is already running the previous image,
# and "starting" it here would recreate the container from whatever tag the
# compose file defaults to, quietly destroying the thing being rolled back to.

if [[ "${1:-}" == "--rollback" ]]; then
    from="$(live_colour)"
    to="$(other_colour "$from")"

    running "$to" || {
        echo "app_$to is not running, so there is nothing to roll back to." >&2
        exit 1
    }

    say "rolling back from $from to $to"
    point_at "$to"

    smoke || {
        echo "the idle colour does not answer either. Returning to $from." >&2
        point_at "$from"
        exit 1
    }

    echo "traffic is on $to"
    exit 0
fi

# --- deploy -----------------------------------------------------------------

TARGET="${1:-}"
[[ $TARGET == blue || $TARGET == green ]] || {
    echo "usage: $0 <blue|green> [--tag <tag>] [--no-build]" >&2
    echo "       $0 --rollback | --status" >&2
    exit 1
}
shift

TAG=local
BUILD=1

while [[ $# -gt 0 ]]; do
    case "$1" in
        --tag) TAG="${2:?--tag needs a value}"; shift 2 ;;
        --no-build) BUILD=0; shift ;;
        *) echo "unknown option: $1" >&2; exit 1 ;;
    esac
done

CURRENT="$(live_colour)"

[[ $TARGET != "$CURRENT" ]] || {
    echo "$TARGET is already live. Deploy to $(other_colour "$CURRENT") so this one stays as the rollback target." >&2
    exit 1
}

# The tag has to be exported for every compose call in this script, not just
# the build: `compose up app_blue` with it unset would recreate the container
# from the default tag.
if [[ $TARGET == blue ]]; then export BLUE_TAG="$TAG"; else export GREEN_TAG="$TAG"; fi

# --- 1. build ---------------------------------------------------------------
# --no-build is the normal shape in CI: the pipeline builds and publishes the
# tag, and the machine being deployed to only pulls and switches.
if (( BUILD )); then
    say "1/7  build modularity:$TAG for $TARGET"
    compose build "app_$TARGET"
else
    say "1/7  skip the build, using modularity:$TAG"
    docker image inspect "modularity:$TAG" > /dev/null 2>&1 || {
        echo "modularity:$TAG is not available locally and --no-build was given." >&2
        exit 1
    }
fi

# --- 2. start the idle colour on the new image ------------------------------
say "2/7  start $TARGET"
compose up -d database "app_$TARGET"

# --- 3. wait until it can reach its dependencies ----------------------------
say "3/7  wait for $TARGET to report ready"
deadline=$(( SECONDS + READY_TIMEOUT ))
until compose exec -T "app_$TARGET" php artisan health:check --quiet-on-success; do
    if (( SECONDS >= deadline )); then
        echo "$TARGET never became ready. Traffic was not moved." >&2
        compose logs --tail 40 "app_$TARGET" >&2
        exit 1
    fi
    sleep 2
done

# --- 4. migrate -------------------------------------------------------------
# Expand-only, so the colour still serving keeps working against the new
# schema. That constraint is what makes the whole rollout possible.
say "4/7  migrate and sync the permission catalogue"
compose exec -T "app_$TARGET" php artisan migrate --force

# --- 5. warm the caches -----------------------------------------------------
# Additive only, for the same reason the migrations are: the colour still
# serving must keep working. Pruning is a separate, deliberate step.
compose exec -T "app_$TARGET" php artisan access:sync-permissions

say "5/7  cache config and routes"
compose exec -T "app_$TARGET" php artisan config:cache
compose exec -T "app_$TARGET" php artisan route:cache

# --- 6. switch --------------------------------------------------------------
say "6/7  point traffic at $TARGET"
point_at "$TARGET"

smoke || {
    echo "the smoke test failed after switching. Returning to $CURRENT." >&2
    point_at "$CURRENT"
    exit 1
}

# --- 7. drain ---------------------------------------------------------------
say "7/7  drain $CURRENT for ${DRAIN_SECONDS}s"
sleep "$DRAIN_SECONDS"

echo
echo "live: $TARGET (modularity:$TAG)"
echo "$CURRENT is still running its previous image; ./deploy.sh --rollback returns to it."
