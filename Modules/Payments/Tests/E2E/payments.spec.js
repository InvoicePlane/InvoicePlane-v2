import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';
import { registerRequiredFieldOmissionTests } from '../../../Core/Tests/E2E/required-field-helpers.js';

test.describe('Payments', () => {
  test('list page shows real, correctly-scoped seeded payments', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/payments'));

    /* Act & Assert */
    // Modules/Payments/Enums/PaymentStatus.php — completed, failed, pending,
    // refunded, partially_refunded.
    await assertRealListContent(page, /^(completed|failed|pending|refunded|partially[ _]refunded)$/i);
  });

  test('creating a payment persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    // Payments have no dedicated /payments/create page — PaymentResource
    // only registers an 'index' route; creation happens through the
    // "New Payment" header modal on the list page.
    await page.goto(tenantPath('/payments'));
    await page.getByRole('button', { name: 'New Payment' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    await modal.getByRole('combobox', { name: /^invoice/i }).click();
    await page.keyboard.type('1', { delay: 30 });
    // The Invoice field's own search results render as plain text, not
    // native <option>s — a bare "INV-" match also hits the invoice number
    // shown in the (still-present-but-covered) list table behind the
    // modal, so the " – <customer>" suffix disambiguates the real option.
    const invoiceOption = page.getByText(/^INV-\d+-\d+ . /).first();
    const invoiceLabel = (await invoiceOption.innerText()).trim();
    const invoiceNumber = invoiceLabel.split(' ')[0];
    await invoiceOption.click();

    await modal.getByLabel('Payment Date*').fill('2024-01-15');

    await modal.getByLabel('Payment Method*').click();
    await page.getByRole('option', { name: 'Bank Transfer', exact: true }).click();

    await modal.getByLabel('Payment Status*').click();
    await page.getByRole('option', { name: 'Completed', exact: true }).click();

    // Must be unique per run: seeded payments exist and the search below is
    // by invoice number, which can match several payments against the same
    // invoice — a hardcoded amount collides across runs and the "first row"
    // is then not necessarily this one.
    const uniqueAmount = `${4000 + (Date.now() % 1000)}.${String(Date.now()).slice(-2)}`;
    await modal.getByLabel('Payment Amount*').fill(uniqueAmount);

    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    // Not expect(modal).toBeHidden(): this app's modal wrapper (role="dialog")
    // is `position: static; height: 0` by Filament's own CSS regardless of
    // open/closed state (see company-users.spec.js), so that assertion is
    // trivially true even while the modal is still open. Assert on the
    // submitted field itself instead.
    await expect(modal.getByLabel('Payment Amount*')).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    await page.goto(tenantPath('/payments'));
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(invoiceNumber, { delay: 30 });

    // Search by invoice number can return several payment rows for that
    // invoice (seed data + this one), so match the row by this run's unique
    // amount rather than assuming .first() is the new record.
    const resultRow = page.locator('table tbody tr').filter({ hasText: uniqueAmount });
    await expect(resultRow).toHaveCount(1, { timeout: 10000 });
    await expect(resultRow).toContainText(invoiceNumber);
    // The payment_status column renders the raw enum value (lowercase),
    // not its Title Case ->label() — matches the list test's own regex.
    await expect(resultRow).toContainText('completed');
  });
});

/**
 * mind-the-gap-again: real frontend counterpart to this module's PHPUnit
 * "it_fails_to_create_X_without_required_Y" tests — for each field listed,
 * fills a valid create form except that one field and asserts the browser
 * rejects it. See Core/Tests/E2E/required-field-helpers.js.
 *
 * Left off: company_id (tenant-injected); customer_id (copied from the chosen
 * invoice); payment_method / payment_status / payment_amount — the omission
 * driver can't fill the Invoice select (custom text-search results) as a
 * valid sibling, and the "creating a payment" test above already exercises
 * all three.
 */
registerRequiredFieldOmissionTests('Payments', {
  'company/payments': ['invoice_id'],
});
