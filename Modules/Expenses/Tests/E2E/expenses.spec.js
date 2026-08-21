import { test, expect } from '@playwright/test';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';

test.describe('Expenses', () => {
  test('list page renders the expenses table', async ({ page }) => {
    await page.goto(tenantPath('/expenses'));

    await expect(page.locator('table')).toBeVisible();
  });

  test('create page renders the expense form', async ({ page }) => {
    await page.goto(tenantPath('/expenses/create'));

    await expect(page.getByRole('heading', { name: 'Create Expense' })).toBeVisible();
    // Every Filament create page also has a hidden topbar logout <form> —
    // `form.fi-sc-form` is the real one; bare `form` is a strict-mode
    // violation (resolves to 2 elements) on every create page in this app.
    await expect(page.locator('form.fi-sc-form')).toBeVisible();
  });

  test('expense categories page loads', async ({ page }) => {
    await page.goto(tenantPath('/expense-categories'));

    await expect(page.locator('table')).toBeVisible();
  });
});
