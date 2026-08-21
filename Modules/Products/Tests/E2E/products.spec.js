import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';

test.describe('Products', () => {
  test('list page shows real, correctly-scoped seeded products', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/products'));

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('creating a product persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    // Products have no dedicated /products/create page — ProductResource
    // only registers an 'index' route; creation happens through the
    // "New Product" header modal on the list page.
    await page.goto(tenantPath('/products'));
    await page.getByRole('button', { name: 'New Product' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    const sku = `E2E-SKU-${Date.now()}`;
    await modal.getByLabel('Code (SKU)*').fill(sku);
    const productName = `E2E Product ${Date.now()}`;
    await modal.getByLabel('Product name*').fill(productName);

    await modal.getByLabel('Product type*').click();
    await page.getByRole('option', { name: 'Product', exact: true }).click();

    await modal.getByLabel('Family*').click();
    await page.getByRole('option', { name: 'General', exact: true }).click();

    await modal.getByLabel('Price*').fill('19.99');

    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    await page.goto(tenantPath('/products'));
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(sku, { delay: 30 });

    const resultRow = page.locator('table tbody tr').first();
    await expect(resultRow).toContainText(sku, { timeout: 10000 });
    // Product name column is server-truncated for long values — a short
    // leading slice survives that regardless of name length.
    await expect(resultRow).toContainText(productName.slice(0, 10));
  });

  test('product categories page shows real, correctly-scoped seeded categories', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/product-categories'));

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('creating a product category persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/product-categories'));
    await page.getByRole('button', { name: 'New Family' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    const categoryName = `E2E Category ${Date.now()}`;
    await modal.getByLabel('Family*').fill(categoryName);
    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    // This table has no searchable columns (Modules/Products/Filament/
    // Company/Resources/ProductCategories/Tables/ProductCategoriesTable.php)
    // — no search box to filter by, so just confirm the new row rendered.
    await page.goto(tenantPath('/product-categories'));
    await expect(page.getByText(categoryName)).toBeVisible({ timeout: 15000 });
  });

  test('product units page shows real, correctly-scoped seeded units', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/product-units'));

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('creating a product unit persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/product-units'));
    await page.getByRole('button', { name: 'New Unit' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    const unitName = `e2eunit${Date.now()}`;
    await modal.getByLabel('Unit*').fill(unitName);
    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    // No searchable columns on this table either (ProductUnitsTable.php) —
    // just confirm the new row rendered.
    await page.goto(tenantPath('/product-units'));
    await expect(page.getByText(unitName)).toBeVisible({ timeout: 15000 });
  });
});
