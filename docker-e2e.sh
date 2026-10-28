#!/bin/bash
# Docker E2E Test Script for InvoicePlane v2
# Usage: DOCKER_APP_DIR=/var/www/projects/invoiceplane/ivpl-devcheck \
#        IVPLDOCK_WORKSPACE=ivpldock-workspace-1 sh docker-e2e.sh --headed

set -eu

# Default values
APP_DIR="${DOCKER_APP_DIR:-/var/www/projects/invoiceplane/ivpl-devcheck}"
WORKSPACE="${IVPLDOCK_WORKSPACE:-ivpldock-workspace-1}"
HEADLED="${1:-headed}"

# Export environment variables for playwright
export APP_DIR
export WORKSPACE
export HEADLED

# Run playwright tests inside the ivpldock workspace container
docker exec -e SKIP_LOGIN=1 \
    -e APP_ENV=testing \
    -e DB_CONNECTION=mariadb \
    -e DB_HOST=mariadb \
    -e DB_DATABASE=invoiceplane_test \
    -e DOCKER_APP_DIR="${APP_DIR}" \
    -e IVPLDOCK_WORKSPACE="${WORKSPACE}" \
    -e HEADLED="${HEADLED}" \
    ivpldock-workspace-1 \
    bash -c '
      cd /var/www/projects/invoiceplane-2/ivplv2
      
      # Check if headed mode
      if [ "$HEADLED" = "headed" ]; then
        export HEADLESS=false
      else
        export HEADLESS=true
      fi
      
      # Run playwright tests
      CI=true npx playwright test --config=playwright.config.js "${@:2}"
'

exit $?
