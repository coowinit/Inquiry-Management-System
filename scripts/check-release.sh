#!/usr/bin/env bash
set -e
php "$(cd "$(dirname "$0")/.." && pwd)/scripts/check-release.php"
