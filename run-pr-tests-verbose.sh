#!/bin/bash

# Verbose Multi-PR Parallel Test Runner for InvoicePlane v2
# Shows every test name as it executes in real-time
# Tests specified PRs with maximum verbosity and test profiling

set -e

PROJECT_DIR="/data/Projects/ip2"
RESULTS_FILE="$PROJECT_DIR/test-results-verbose.log"
ORIGINAL_BRANCH=$(git rev-parse --abbrev-ref HEAD)

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Branch mapping
declare -A BRANCHES=(
  [develop]="develop"
  [709]="feat/subscriptions"
  [700]="feature/370-invoice-line-item-numbering"
  [692]="235-243-company-settings"
  [685]="codex/trivial-fixes"
  [684]="feature/32-invoice-list-enter-payment"
)

# Cleanup
> "$RESULTS_FILE"

echo -e "${BLUE}========================================"
echo -e "InvoicePlane v2 - Verbose Parallel Tests"
echo -e "========================================${NC}"
echo ""
echo "📋 Testing branches: develop, #709, #700, #692, #685, #684"
echo "📊 Test output streaming in real-time below:"
echo "⚡ Using: php artisan test -p --profile -vvv (maximum verbosity)"
echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

test_count=0
pass_count=0
fail_count=0

for pr in develop 709 700 692 685 684; do
  branch="${BRANCHES[$pr]}"
  label="PR#$pr ($branch)"
  test_count=$((test_count + 1))

  echo -e "${BLUE}[${test_count}/6]${NC} Testing $label"
  echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

  cd "$PROJECT_DIR"

  # Fetch and checkout branch
  if ! git rev-parse --verify "$branch" >/dev/null 2>&1; then
    if ! git fetch origin "$branch" 2>/dev/null; then
      echo -e "${YELLOW}⚠️  Skipped - branch not found${NC}"
      echo "⚠️  PR#$pr - SKIPPED (branch not found)" >> "$RESULTS_FILE"
      echo ""
      continue
    fi
  fi

  git checkout "$branch" --quiet 2>/dev/null || git checkout -b "$branch" "origin/$branch" --quiet 2>/dev/null || true

  # Run tests with MAXIMUM verbosity and profiling
  # -vvv shows every test name as it runs
  # --profile shows top 10 slowest tests after run
  echo ""
  if php artisan test -p -vvv --profile --exclude-group failing,flaky,troubleshooting 2>&1 | tee -a /tmp/test-$pr-verbose.log; then
    echo -e ""
    echo -e "${GREEN}✅ PR#$pr - PASSED${NC}"
    echo "✅ PR#$pr - PASSED" >> "$RESULTS_FILE"
    pass_count=$((pass_count + 1))
  else
    echo -e ""
    echo -e "${RED}❌ PR#$pr - FAILED${NC}"
    echo "❌ PR#$pr - FAILED" >> "$RESULTS_FILE"
    fail_count=$((fail_count + 1))
  fi

  echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
  echo ""
done

# Go back to original branch
cd "$PROJECT_DIR"
git checkout "$ORIGINAL_BRANCH" --quiet 2>/dev/null || true

# Summary
echo -e "${BLUE}========================================"
echo -e "Results Summary"
echo -e "========================================${NC}"
echo -e "Total:  ${BLUE}$test_count${NC}"
echo -e "Passed: ${GREEN}$pass_count${NC}"
echo -e "Failed: ${RED}$fail_count${NC}"
echo ""

if [ $fail_count -eq 0 ]; then
  echo -e "${GREEN}✅ All test suites passed!${NC}"
  exit 0
else
  echo -e "${RED}❌ Some test suites failed.${NC}"
  echo ""
  echo "Detailed results: $RESULTS_FILE"
  echo "Individual logs: /tmp/test-*-verbose.log"
  exit 1
fi
