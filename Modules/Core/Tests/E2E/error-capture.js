import { expect } from '@playwright/test';
import { tenantPath } from './tenant-path.js';

/**
 * Wires up local console/pageerror listeners and returns the array they
 * push into. Separate from test.js's own fixture-level listeners (which
 * exist for whole-run visibility via error-summary-reporter.js) — this is
 * for tests that need to assert directly "this specific action produced
 * zero errors", so they need their own array to make assertions on.
 */
export function captureConsoleErrors(page) {
  const errors = [];
  page.on('console', (msg) => { if (msg.type() === 'error') errors.push(msg.text()); });
  page.on('pageerror', (err) => errors.push(err.message));
  return errors;
}

/**
 * Shared "Add New Row" repeater regression check: navigates to a create
 * page, expands a collapsed section if one is given, clicks "Add New Row",
 * and asserts both the row count incremented and nothing errored. Used by
 * every module whose create form has a repeater (Invoices, Expenses,
 * Quotes, ...).
 */
export async function assertAddRowIncrementsRepeater(page, { createPath, sectionHeading, itemLabel }) {
  const errors = captureConsoleErrors(page);

  await page.goto(tenantPath(createPath));
  if (sectionHeading) {
    await page.getByRole('heading', { name: sectionHeading }).click();
  }
  const addButton = page.getByRole('button', { name: 'Add New Row' });
  await expect(addButton).toBeVisible();
  const itemsBefore = await page.locator('.fi-fo-repeater-item').count();

  await addButton.click();

  await expect(page.locator('.fi-fo-repeater-item')).toHaveCount(itemsBefore + 1);
  expect(errors, `unexpected error(s) adding a ${itemLabel} row:\n${errors.join('\n')}`).toHaveLength(0);
}

/**
 * Bumps a Filament table's page size to its maximum, for list pages with
 * no searchable columns (no search box to filter by — e.g. Product
 * Categories, Product Units). Without this, a test asserting a newly
 * created row is visible is only sturdy up to the default page size (10):
 * every run that doesn't clean up after itself adds a row, so a suite run
 * repeatedly over time eventually pushes its own earlier rows past page 1
 * and starts failing — not from a real regression, just accumulated test
 * data outliving the page size. Call after navigating to the list page.
 */
export async function maximizeTableRecordsPerPage(page) {
  // Two copies of this <select> exist in the DOM (only one visible at a
  // time depending on viewport) — pick the visible one explicitly rather
  // than .first(), which can resolve to the hidden copy.
  const candidates = await page.locator('select[wire\\:model\\.live="tableRecordsPerPage"]').all();
  for (const candidate of candidates) {
    if (await candidate.isVisible()) {
      const optionValues = await candidate.locator('option').allInnerTexts();
      const largest = optionValues.map((v) => v.trim()).filter((v) => /^\d+$/.test(v)).map(Number).sort((a, b) => b - a)[0];
      if (largest) {
        await candidate.selectOption(String(largest));
      }
      return;
    }
  }
}
