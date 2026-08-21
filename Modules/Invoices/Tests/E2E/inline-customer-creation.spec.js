import { test, expect } from '@playwright/test';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';

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

    // getByLabel(/customer/i) is ambiguous on its own (matches the sidebar
    // "Customers" nav toggle and the field's section region too) — the
    // actual select renders as an ARIA combobox with accessible name
    // "Customer*", so that's what pins it down to exactly one element.
    const customerField = page.getByRole('combobox', { name: /^customer/i });
    await expect(customerField).toBeVisible();

    // Filament's create-option trigger renders as a button next to the
    // select, accessible name defaults to "Create" (or the field's
    // translated label with a plus icon).
    const createButton = page
      .locator('section', { has: customerField })
      .getByRole('button', { name: /create/i });
    await createButton.click();

    // The modal's outer role="dialog" wrapper is `position: static; height:
    // 0` by Filament's own CSS (its window content is `position: fixed`,
    // outside its parent's box) — it can never satisfy toBeVisible()/
    // toBeHidden(), regardless of whether the modal is actually open. Assert
    // on real content inside it instead.
    const modal = page.getByRole('dialog');
    const nameInput = modal.getByLabel(/customer name/i);
    await expect(nameInput).toBeVisible();

    const uniqueName = `E2E Test Customer ${Date.now()}`;
    await nameInput.fill(uniqueName);
    await modal.getByRole('button', { name: /^create$/i }).click();

    await expect(nameInput).toBeHidden();
    await expect(customerField).toHaveText(new RegExp(uniqueName));
  });
});
