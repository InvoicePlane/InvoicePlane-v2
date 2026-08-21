import { test, expect } from '../E2E/fixtures.js';

test.describe('Invoices', () => {
  test('invoices page loads without errors', async ({ authenticatedPage }) => {
    await authenticatedPage.goto('/invoices');

    // Verify page loaded successfully
    await expect(authenticatedPage).not.toHaveTitle(/error/i);
    await expect(authenticatedPage.locator('text=Invoices')).toBeVisible();
  });

  test('create invoice page is accessible', async ({ authenticatedPage }) => {
    await authenticatedPage.goto('/invoices/create');

    // Verify form loads
    await expect(authenticatedPage).not.toHaveTitle(/error/i);
    await expect(authenticatedPage.locator('form')).toBeVisible();
  });

  test('invoice list shows records', async ({ authenticatedPage }) => {
    await authenticatedPage.goto('/invoices');

    // Should have a data table or records visible
    const table = authenticatedPage.locator('table');
    await expect(table).toBeVisible({ timeout: 5000 });
  });
});
