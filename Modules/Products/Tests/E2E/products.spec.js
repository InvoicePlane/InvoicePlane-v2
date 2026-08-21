import { test, expect } from '@playwright/test';
import { tenantPath } from '../../Core/Tests/E2E/tenant-path.js';

test.describe('Products', () => {
  test('list page renders the products table', async ({ page }) => {
    await page.goto(tenantPath('/products'));

    await expect(page.locator('table')).toBeVisible();
  });

  test('product categories page loads', async ({ page }) => {
    await page.goto(tenantPath('/product-categories'));

    await expect(page.locator('table')).toBeVisible();
  });

  test('product units page loads', async ({ page }) => {
    await page.goto(tenantPath('/product-units'));

    await expect(page.locator('table')).toBeVisible();
  });
});
