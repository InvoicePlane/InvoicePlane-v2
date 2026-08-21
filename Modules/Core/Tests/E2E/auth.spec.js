import { test, expect } from '@playwright/test';
import { login, logout } from './auth-helpers.js';
import { tenantPath } from './tenant-path.js';

// This file is the one place that legitimately manipulates auth state, so it
// opts out of the suite-wide pre-authenticated storageState from
// global-setup.js: a logout() here doesn't just log this test's page out, it
// invalidates the server-side session that auth.json's storageState
// represents, silently un-authenticating every other test file that reuses
// it afterward. Each test in this file starts logged out and authenticates
// explicitly.
test.use({ storageState: { cookies: [], origins: [] } });

test.describe('Authentication', () => {
  test('authenticated session reaches the tenant dashboard', async ({ page }) => {
    await login(page);
    await page.goto(tenantPath('/dashboard'));

    await expect(page).toHaveURL(new RegExp(tenantPath('/dashboard')));
    await expect(page.getByRole('heading', { name: /dashboard/i })).toBeVisible();
  });

  test('logout redirects to the login page', async ({ page }) => {
    await login(page);
    await page.goto(tenantPath('/dashboard'));

    await logout(page);

    await expect(page).toHaveURL(/\/login/);
  });

  test('login redirects back to the tenant dashboard', async ({ page }) => {
    await login(page);

    await expect(page).toHaveURL(new RegExp(tenantPath('/dashboard')));
  });

  test('invalid credentials are rejected', async ({ page }) => {
    await page.goto('/login');
    // Filament v5 form inputs bind via wire:model, not a native `name`
    // attribute — `[name="email"]` never matches anything real.
    await page.fill('input[type="email"]', 'nobody@invoiceplane.test');
    await page.fill('input[type="password"]', 'wrong-password');
    await page.click('[type="submit"]');

    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('body')).toContainText(/these credentials do not match|failed/i);
  });
});
