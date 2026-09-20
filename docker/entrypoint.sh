#!/bin/sh
#
# Publishes this colour's built assets into the directory the edge proxy
# serves from. Vite fingerprints every filename, so two colours can share one
# directory without either overwriting the other: the copy is additive and the
# manifest each colour renders points at its own files.
#
# No error suppression here on purpose. A container that cannot publish its
# assets serves a page with no stylesheet, and failing to start is a far more
# useful way to find that out than a 404 in a browser.
#
set -e

if [ -d /var/www/html/public ]; then
    cp -a /var/www/html/public/. /srv/public/
fi

exec "$@"
