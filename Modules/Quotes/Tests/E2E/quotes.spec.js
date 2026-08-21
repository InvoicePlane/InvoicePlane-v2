import { test, expect } from '@playwright/test';
import { tenantPath } from '../../Core/Tests/E2E/tenant-path.js';

test.describe('Quotes', () => {
  test('list page renders the quotes table', async ({ page }) => {
    await page.goto(tenantPath('/quotes'));

    await expect(page.locator('table')).toBeVisible();
  });

  test('create page renders the quote form', async ({ page }) => {
    await page.goto(tenantPath('/quotes/create'));

    await expect(page.locator('form')).toBeVisible();
  });
});
