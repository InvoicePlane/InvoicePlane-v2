#!/bin/bash

# Multi-PR Parallel Test Runner for InvoicePlane v2
# Tests specified PRs using Make targets with parallelization

set -e

PROJECT_DIR="/data/Projects/ip2"
RESULTS_FILE="$PROJECT_DIR/test-results.log"
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
echo -e "InvoicePlane v2 - Parallel Test Suite"
echo -e "========================================${NC}"
echo ""
echo "Testing branches: develop, #709, #700, #692, #685, #684"
echo "Using: make artisan-parallel (PHPUnit with --parallel flag)"
echo ""

test_count=0
pass_count=0
fail_count=0

for pr in develop 709 700 692 685 684; do
  branch="${BRANCHES[$pr]}"
  label="PR#$pr ($branch)"
  test_count=$((test_count + 1))

  echo -ne "${BLUE}[$test_count/6] Testing $label...${NC}"

  cd "$PROJECT_DIR"

  # Fetch and checkout branch
  if ! git rev-parse --verify "$branch" >/dev/null 2>&1; then
    if ! git fetch origin "$branch" 2>/dev/null; then
      echo -e " ${YELLOW}SKIPPED${NC} (branch not found)"
      echo "⚠️  PR#$pr - SKIPPED (branch not found)" >> "$RESULTS_FILE"
      continue
    fi
  fi

  git checkout "$branch" --quiet 2>/dev/null || git checkout -b "$branch" "origin/$branch" --quiet 2>/dev/null || true

  # Run tests with php artisan directly, streaming output live
  # Use -vv to see test names as they run, --profile to show slowest tests
  if php artisan test -p --profile --exclude-group failing,flaky,troubleshooting 2>&1 | tee /tmp/test-$pr.log; then
    echo -e " ${GREEN}PASSED${NC}"
    echo "✅ PR#$pr - PASSED" >> "$RESULTS_FILE"
    pass_count=$((pass_count + 1))

    # Extract test count
    test_count_line=$(grep -i "Tests:" /tmp/test-$pr.log | head -1 || echo "")
    if [ -n "$test_count_line" ]; then
      echo "   $test_count_line" >> "$RESULTS_FILE"
    fi
  else
    echo -e " ${RED}FAILED${NC}"
    echo "❌ PR#$pr - FAILED" >> "$RESULTS_FILE"
    fail_count=$((fail_count + 1))

    # Extract failure info
    echo "   Error details:" >> "$RESULTS_FILE"
    tail -30 /tmp/test-$pr.log | grep -E "(Error|Failed|Exception|\[)" >> "$RESULTS_FILE" 2>/dev/null || true
  fi

  echo "" >> "$RESULTS_FILE"
done

# Go back to original branch
cd "$PROJECT_DIR"
git checkout "$ORIGINAL_BRANCH" --quiet 2>/dev/null || true

# Summary
echo ""
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
  echo "Details saved to: $RESULTS_FILE"
  exit 1
fi
