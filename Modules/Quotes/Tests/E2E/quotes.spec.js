import { test, expect } from '@playwright/test';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';

test.describe('Quotes', () => {
  test('list page renders the quotes table', async ({ page }) => {
    await page.goto(tenantPath('/quotes'));

    await expect(page.locator('table')).toBeVisible();
  });

  test('create page renders the quote form', async ({ page }) => {
    await page.goto(tenantPath('/quotes/create'));

    await expect(page.getByRole('heading', { name: 'Create Quote' })).toBeVisible();
    // Every Filament create page also has a hidden topbar logout <form> —
    // `form.fi-sc-form` is the real one; bare `form` is a strict-mode
    // violation (resolves to 2 elements) on every create page in this app.
    await expect(page.locator('form.fi-sc-form')).toBeVisible();
  });
});
