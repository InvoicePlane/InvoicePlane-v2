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
