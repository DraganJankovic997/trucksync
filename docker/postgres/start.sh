#!/usr/bin/env sh
set -e

: "${DB_DATABASE:?DB_DATABASE is required}"
: "${DB_USERNAME:?DB_USERNAME is required}"
: "${DB_PASSWORD:?DB_PASSWORD is required}"

export POSTGRES_DB="$DB_DATABASE"
export POSTGRES_USER="$DB_USERNAME"
export POSTGRES_PASSWORD="$DB_PASSWORD"

exec docker-entrypoint.sh "$@"
