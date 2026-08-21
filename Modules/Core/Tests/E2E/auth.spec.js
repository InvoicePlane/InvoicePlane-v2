import { test, expect } from '@playwright/test';
import { login, logout } from './auth-helpers.js';

test.describe('Authentication', () => {
  test('user can access dashboard when authenticated', async ({ page }) => {
    // Page is pre-authenticated from global-setup.js
    await page.goto('/dashboard');

    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('text=Dashboard')).toBeVisible();
  });

  test('logout redirects to login page', async ({ page }) => {
    // Start authenticated
    await page.goto('/dashboard');

    // Logout
    await logout(page);

    // Should be back at login
    await expect(page).toHaveURL(/login/);
  });

  test('login works with valid credentials', async ({ page }) => {
    // Logout first to test login flow
    await page.goto('/dashboard');
    await logout(page);

    // Now test login
    await login(page);

    // Should be on dashboard
    await expect(page).toHaveURL(/dashboard/);
  });
});
