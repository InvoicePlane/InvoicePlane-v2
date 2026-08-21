import { test, expect } from '@playwright/test';

const TEST_EMAIL = 'testuser@invoiceplane.test';
const TEST_PASSWORD = 'password';

test.describe('Invoices', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login');
    await page.fill('[name="email"]', TEST_EMAIL);
    await page.fill('[name="password"]', TEST_PASSWORD);
    await page.click('[type="submit"]');
    await page.waitForNavigation();
  });

  test('invoices page loads without errors', async ({ page }) => {
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
