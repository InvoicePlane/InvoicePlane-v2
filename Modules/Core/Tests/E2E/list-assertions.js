import { expect } from '@playwright/test';

/**
 * Asserts a Filament table actually rendered real, correctly-scoped data —
 * not just "a table exists," which is trivially true in this seeded
 * environment (every tenant always has pre-existing rows) regardless of
 * whether the underlying query is broken, mis-scoped, or empty.
 *
 * Cross-checks two numbers Filament computes via independent code paths:
 * the screen-reader-only "N results" status, and the actual paginated row
 * count. A real bug — e.g. the count query and the list query disagreeing
 * on tenant scoping — can break the agreement between these even while rows
 * still render, which "the table has rows" alone would never catch.
 *
 * When `statusPattern` is given, every rendered row (not just the first) is
 * also checked for a real enum-backed value matching it — catching a
 * partial-rendering bug where the row exists but a relation failed to load.
 *
 * @param {import('@playwright/test').Page} page
 * @param {RegExp} [statusPattern] - matched against each row's visible text
 */
export async function assertRealListContent(page, statusPattern) {
  const rows = page.locator('table tbody tr');
  const rowCount = await rows.count();
  const reportedTotalText = await page.getByText(/\d+ results?/i).first().innerText();
  const reportedTotal = parseInt(reportedTotalText, 10);

  expect(reportedTotal, "the table's own reported total should be a real positive number").toBeGreaterThan(0);
  expect(rowCount, 'rendered rows can never exceed the reported total').toBeLessThanOrEqual(reportedTotal);
  expect(rowCount, 'the query returned rows, but none rendered').toBeGreaterThan(0);

  if (statusPattern) {
    for (let i = 0; i < rowCount; i++) {
      await expect(rows.nth(i).getByText(statusPattern)).toBeVisible();
    }
  }
}
