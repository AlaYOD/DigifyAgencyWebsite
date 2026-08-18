#!/usr/bin/env bash
set -euo pipefail

backup_dir="/var/backups/digify"
mkdir -p "$backup_dir"
timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
pg_dump --format=custom --file="$backup_dir/digify-$timestamp.dump" "$DB_DATABASE"
find "$backup_dir" -type f -name 'digify-*.dump' -mtime +14 -delete
