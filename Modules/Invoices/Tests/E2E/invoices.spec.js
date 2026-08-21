import { test, expect } from '@playwright/test';
import { tenantPath } from '../../Core/Tests/E2E/tenant-path.js';

test.describe('Invoices', () => {
  test('list page renders the invoices table', async ({ page }) => {
    await page.goto(tenantPath('/invoices'));

    await expect(page.getByRole('heading', { name: /invoices/i })).toBeVisible();
    await expect(page.locator('table')).toBeVisible();
  });

  test('create page renders the invoice form', async ({ page }) => {
    await page.goto(tenantPath('/invoices/create'));

    await expect(page.locator('form')).toBeVisible();
    await expect(page.getByLabel(/client/i)).toBeVisible();
  });
});
