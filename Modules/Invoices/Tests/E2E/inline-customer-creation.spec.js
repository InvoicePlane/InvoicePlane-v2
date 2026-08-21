import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { captureConsoleErrors } from '../../../Core/Tests/E2E/error-capture.js';

/**
 * Regression coverage for two real bugs this exact workflow has already
 * produced in production: the ReportTemplates boot crash, and later a 500
 * (`relation_type` NOT NULL violation — Filament's default createOptionUsing
 * omitted required columns) that the modal's "Create" button silently threw
 * on every submit. This test exists specifically so a user clicking "+" next
 * to Customer on the invoice form can never regress into an exception again
 * — that failure mode is asserted directly, not inferred from a timeout.
 *
 *   Invoices -> Create -> "+" next to Customer -> fill name -> Save
 *
 * The "+" trigger and modal are Filament framework chrome, not custom
 * markup, so we target them by role/label rather than brittle CSS hooks.
 */
test.describe('Invoice: inline customer creation', () => {
  test('creating a customer from the invoice form assigns it to the invoice, with no errors', async ({ page }) => {
    /* Arrange */
    const errors = captureConsoleErrors(page);

    await page.goto(tenantPath('/invoices/create'));

    // getByLabel(/customer/i) is ambiguous on its own (matches the sidebar
    // "Customers" nav toggle and the field's section region too) — the
    // actual select renders as an ARIA combobox with accessible name
    // "Customer*", so that's what pins it down to exactly one element.
    const customerField = page.getByRole('combobox', { name: /^customer/i });
    await expect(customerField).toBeVisible();

    /* Act */
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

    /* Assert */
    // Wait for the real success signal (modal closing) — but if the save
    // throws (the exact 500 this test was written to catch), surface the
    // actual server/console error text in the failure, not a bare timeout
    // that leaves whoever's on call guessing.
    try {
      await expect(nameInput).toBeHidden();
    } catch (timeoutError) {
      throw new Error(
        errors.length
          ? `Creating the customer failed with error(s):\n${errors.join('\n')}`
          : timeoutError.message
      );
    }

    // Even on the success path, fail on any error that fired without
    // blocking the modal from closing (e.g. a non-fatal console warning) —
    // "no exceptions, ever" means checking this unconditionally, not only
    // when something visibly broke.
    expect(errors, `unexpected error(s) while creating the customer:\n${errors.join('\n')}`).toHaveLength(0);

    await expect(customerField).toHaveText(new RegExp(uniqueName));
  });
});
