import { test, expect } from '@playwright/test';

test.describe('Invoices', () => {
  test('invoices page loads without errors', async ({ page }) => {
    // Page is pre-authenticated from global-setup.js
    await page.goto('/invoices');

    // Verify page loaded successfully
    await expect(page).not.toHaveTitle(/error/i);
    await expect(page.locator('text=Invoices')).toBeVisible();
  });

  test('create invoice page is accessible', async ({ page }) => {
    await page.goto('/invoices/create');

    // Verify form loads
    await expect(page).not.toHaveTitle(/error/i);
    await expect(page.locator('form')).toBeVisible();
  });

  test('invoice list shows records', async ({ page }) => {
    await page.goto('/invoices');

    // Should have a data table or records visible
    const table = page.locator('table');
    await expect(table).toBeVisible({ timeout: 5000 });
  });
});
