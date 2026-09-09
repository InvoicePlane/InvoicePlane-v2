import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';
import { assertAddRowIncrementsRepeater, captureConsoleErrors } from '../../../Core/Tests/E2E/error-capture.js';
import { registerRequiredFieldOmissionTests } from '../../../Core/Tests/E2E/required-field-helpers.js';

test.describe('Invoices', () => {
  test('list page shows real, correctly-scoped seeded invoices', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/invoices'));

    /* Act & Assert */
    // Modules/Invoices/Enums/InvoiceStatus.php — draft, sent, viewed,
    // partially_paid, paid, overdue.
    await assertRealListContent(page, /^(draft|sent|viewed|partially[ _]paid|paid|overdue)$/i);
  });

  test('create page renders the invoice form', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/invoices/create'));

    /* Act */
    const heading = page.getByRole('heading', { name: 'Create Invoices' });
    // Every Filament create page also has a hidden topbar logout <form> —
    // `form.fi-sc-form` is the real one; bare `form` is a strict-mode
    // violation (resolves to 2 elements) on every create page in this app.
    const form = page.locator('form.fi-sc-form');
    // getByLabel(/customer/i) is ambiguous (matches the sidebar "Customers"
    // nav toggle and the field's section region too) — the select renders
    // as an ARIA combobox with accessible name "Customer*", which pins it
    // to exactly one element.
    const customerField = page.getByRole('combobox', { name: /^customer/i });

    /* Assert */
    await expect(heading).toBeVisible();
    await expect(form).toBeVisible();
    await expect(customerField).toBeVisible();
  });

  test('"Add New Row" on the invoice items repeater adds a real row, with no errors', async ({ page }) => {
    /* Arrange, Act & Assert */
    // The "Invoice Items" section starts collapsed — the "Add New Row"
    // button doesn't exist in the DOM until it's expanded.
    await assertAddRowIncrementsRepeater(page, {
      createPath: '/invoices/create',
      sectionHeading: 'Invoice Items',
      itemLabel: 'invoice item',
    });
  });
});

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

/**
 * mind-the-gap-again: real frontend counterpart to this module's PHPUnit
 * "it_fails_to_create_X_without_required_Y" tests — for each field listed,
 * fills a valid create form except that one field and asserts the browser
 * rejects it. See Core/Tests/E2E/required-field-helpers.js.
 *
 * Left off deliberately: company_id (tenant-injected), user_id (the acting
 * user), and item_tax_total / invoice_item_subtotal / invoice_tax_total /
 * invoice_total (computed by InvoiceService from the line-item repeater).
 */
registerRequiredFieldOmissionTests('Invoices', {
  'company/invoices': ['customer_id', 'invoice_status'],
});
