import { test, expect } from '@playwright/test';
import { login, logout } from './auth-helpers.js';
import { tenantPath } from './tenant-path.js';

test.describe('Authentication', () => {
  test('authenticated session reaches the tenant dashboard', async ({ page }) => {
    // Session is pre-authenticated via global-setup.js.
    await page.goto(tenantPath('/dashboard'));

    await expect(page).toHaveURL(new RegExp(tenantPath('/dashboard')));
    await expect(page.getByRole('heading', { name: /dashboard/i })).toBeVisible();
  });

  test('logout redirects to the login page', async ({ page }) => {
    await page.goto(tenantPath('/dashboard'));

    await logout(page);

    await expect(page).toHaveURL(/\/login/);
  });

  test('login redirects back to the tenant dashboard', async ({ page }) => {
    await page.goto(tenantPath('/dashboard'));
    await logout(page);

    await login(page);

    await expect(page).toHaveURL(new RegExp(tenantPath('/dashboard')));
  });

  test('invalid credentials are rejected', async ({ page }) => {
    await page.goto('/login');
    await page.fill('[name="email"]', 'nobody@invoiceplane.test');
    await page.fill('[name="password"]', 'wrong-password');
    await page.click('[type="submit"]');

    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('body')).toContainText(/these credentials do not match|failed/i);
  });
});
