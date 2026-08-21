import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';
import { assertAddRowIncrementsRepeater } from '../../../Core/Tests/E2E/error-capture.js';

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
