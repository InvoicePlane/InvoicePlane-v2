import { test, expect } from '@playwright/test';
import { tenantPath } from '../../Core/Tests/E2E/tenant-path.js';

/**
 * Regression coverage for the exact workflow that surfaced the
 * ReportTemplates boot crash during a live demo:
 *
 *   Invoices -> Create -> "+" next to Customer -> fill name -> Save
 *
 * This is a Filament Select::make('customer_id')->createOptionForm([...]);
 * the "+" trigger and modal are Filament framework chrome, not custom
 * markup, so we target them by role/label rather than brittle CSS hooks.
 */
test.describe('Invoice: inline customer creation', () => {
  test('creating a customer from the invoice form assigns it to the invoice', async ({ page }) => {
    await page.goto(tenantPath('/invoices/create'));

    const customerField = page.getByLabel(/client/i);
    await expect(customerField).toBeVisible();

    // Filament's create-option trigger renders as a button next to the
    // select, accessible name defaults to "Create" (or the field's
    // translated label with a plus icon).
    const createButton = page
      .locator('section', { has: customerField })
      .getByRole('button', { name: /create/i });
    await createButton.click();

    const modal = page.getByRole('dialog');
    await expect(modal).toBeVisible();

    const uniqueName = `E2E Test Customer ${Date.now()}`;
    await modal.getByLabel(/customer name/i).fill(uniqueName);
    await modal.getByRole('button', { name: /^create$/i }).click();

    await expect(modal).toBeHidden();
    await expect(customerField).toHaveText(new RegExp(uniqueName));
  });
});
