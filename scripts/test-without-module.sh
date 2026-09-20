#!/usr/bin/env bash
#
# Deletes a module, runs the whole suite, puts it back.
#
#   ./scripts/test-without-module.sh Access
#
# The claim this repository makes about module boundaries is checkable, and
# this is how it is checked.
#
set -euo pipefail

cd "$(dirname "$0")/.."

MODULE="${1:?usage: $0 <ModuleName>}"
DIR="app/Modules/${MODULE}"
CONFIG="config/modules.php"
STASH="$(mktemp -d)"

[[ -d $DIR ]] || { echo "No such module: ${DIR}" >&2; exit 1; }

restore() {
    rm -rf "$DIR"
    [[ -d "$STASH/$MODULE" ]] && mv "$STASH/$MODULE" "$DIR"
    [[ -f "$STASH/modules.php" ]] && mv "$STASH/modules.php" "$CONFIG"
    rm -rf "$STASH"
    echo "restored ${MODULE}"
}
trap restore EXIT

cp "$CONFIG" "$STASH/modules.php"
cp -R "$DIR" "$STASH/$MODULE"

echo "removing ${DIR} and its line in ${CONFIG}"
rm -rf "$DIR"
grep -v "${MODULE}ServiceProvider" "$STASH/modules.php" > "$CONFIG"

vendor/bin/phpunit
