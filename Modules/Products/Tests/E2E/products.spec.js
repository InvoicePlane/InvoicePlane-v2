import { test, expect } from '@playwright/test';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';

test.describe('Products', () => {
  test('list page shows real, correctly-scoped seeded products', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/products'));

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('product categories page shows real, correctly-scoped seeded categories', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/product-categories'));

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('product units page shows real, correctly-scoped seeded units', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/product-units'));

    /* Act & Assert */
    await assertRealListContent(page);
  });
});
