#!/usr/bin/env bash

set -euo pipefail

PROFILE=""
NON_INTERACTIVE=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --profile=*)
      PROFILE="${1#*=}"
      shift
      ;;
    --profile)
      PROFILE="${2:-}"
      shift 2
      ;;
    --non-interactive)
      NON_INTERACTIVE="--non-interactive"
      shift
      ;;
    *)
      echo "Unknown option: $1"
      echo "Usage: ./setup.sh [--profile local|cloud] [--non-interactive]"
      exit 1
      ;;
  esac
done

if [[ -n "$PROFILE" && "$PROFILE" != "local" && "$PROFILE" != "cloud" ]]; then
  echo "Invalid profile: $PROFILE (expected local|cloud)"
  exit 1
fi

ARGS=("framework" "setup")

if [[ -n "$PROFILE" ]]; then
  ARGS+=("--profile=$PROFILE")
fi

if [[ -n "$NON_INTERACTIVE" ]]; then
  ARGS+=("$NON_INTERACTIVE")
fi

php "${ARGS[@]}"

