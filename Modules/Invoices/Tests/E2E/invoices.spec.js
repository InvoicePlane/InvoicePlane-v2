import { test, expect } from '@playwright/test';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';

test.describe('Invoices', () => {
  test('list page renders the invoices table', async ({ page }) => {
    await page.goto(tenantPath('/invoices'));

    await expect(page.getByRole('heading', { name: /invoices/i })).toBeVisible();
    await expect(page.locator('table')).toBeVisible();
  });

  test('create page renders the invoice form', async ({ page }) => {
    await page.goto(tenantPath('/invoices/create'));

    await expect(page.getByRole('heading', { name: 'Create Invoices' })).toBeVisible();
    // Every Filament create page also has a hidden topbar logout <form> —
    // `form.fi-sc-form` is the real one; bare `form` is a strict-mode
    // violation (resolves to 2 elements) on every create page in this app.
    await expect(page.locator('form.fi-sc-form')).toBeVisible();
    // getByLabel(/customer/i) is ambiguous (matches the sidebar "Customers"
    // nav toggle and the field's section region too) — the select renders
    // as an ARIA combobox with accessible name "Customer*", which pins it
    // to exactly one element.
    await expect(page.getByRole('combobox', { name: /^customer/i })).toBeVisible();
  });
});
