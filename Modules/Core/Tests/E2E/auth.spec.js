import { test, expect } from '@playwright/test';

const TEST_EMAIL = 'testuser@invoiceplane.test';
const TEST_PASSWORD = 'password';

test.describe('Authentication', () => {
  test('user can login and access dashboard', async ({ page }) => {
    await page.goto('/login');
    
    // Fill login form
    await page.fill('[name="email"]', TEST_EMAIL);
    await page.fill('[name="password"]', TEST_PASSWORD);
    await page.click('[type="submit"]');

    // Should redirect to dashboard
    await page.waitForNavigation();
    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('text=Dashboard')).toBeVisible();
  });

  test('logout redirects to login page', async ({ page }) => {
    // Login first
    await page.goto('/login');
    await page.fill('[name="email"]', TEST_EMAIL);
    await page.fill('[name="password"]', TEST_PASSWORD);
    await page.click('[type="submit"]');
    await page.waitForNavigation();

    // Find and click logout
    await page.click('[aria-label="User menu"]');
    await page.click('text=Log out');
    await page.waitForNavigation();

    // Should be back at login
    await expect(page).toHaveURL(/login/);
  });
});
