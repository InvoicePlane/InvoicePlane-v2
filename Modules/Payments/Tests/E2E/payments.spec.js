import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';

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

    const uniqueAmount = '4217.93';
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

    const resultRow = page.locator('table tbody tr').first();
    await expect(resultRow).toContainText(invoiceNumber, { timeout: 10000 });
    await expect(resultRow).toContainText(uniqueAmount);
    // The payment_status column renders the raw enum value (lowercase),
    // not its Title Case ->label() — matches the list test's own regex.
    await expect(resultRow).toContainText('completed');
  });
});
