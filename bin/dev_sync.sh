#!/bin/bash

root="$(git rev-parse --show-toplevel)"

# shellcheck disable=SC1090,SC1091
source "$root/.env"

rsync -a "$XZ_SOLUTIONS_JSON_DIR" "$XZ_SOLUTIONS_TRACKED_DIR"
rsync -a --delete "$XZ_SOLUTIONS_TRACKED_DIR" "$XZ_SOLUTIONS_MU_PLUGIN_DIR"
