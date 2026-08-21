import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';

test.describe('Relations (Customers)', () => {
  test('list page shows real, correctly-scoped seeded relations', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/relations'));

    /* Act & Assert */
    // Modules/Clients/Enums/RelationStatus.php — ACTIVE, INACTIVE.
    await assertRealListContent(page, /^(Active|Inactive)$/i);
  });

  test('creating a relation persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    // Relations have no dedicated /relations/create page — RelationResource
    // only registers 'index'/'view' routes (Modules/Clients/Filament/
    // Company/Resources/Relations/RelationResource.php:getPages());
    // creation happens through ListRelations' header "New relation" modal.
    await page.goto(tenantPath('/relations'));
    await page.getByRole('button', { name: 'New relation' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    await modal.getByLabel('Status*').click();
    await page.getByRole('option', { name: 'Active', exact: true }).click();

    await modal.getByLabel('Type*').click();
    await page.getByRole('option', { name: 'Customer', exact: true }).click();

    const uniqueName = `E2E Relation ${Date.now()}`;
    // .fill() doesn't reliably trigger the live(debounce: 500) hook that
    // auto-populates the required Unique Name field from this one; type it
    // out like a real user would.
    await modal.getByLabel('Customer Name*').pressSequentially(uniqueName, { delay: 20 });
    await page.waitForTimeout(700);

    const relationNumber = `E2ER-${Date.now()}`;
    await modal.getByLabel('Relation Number*').fill(relationNumber);
    await modal.getByLabel('Registration Date*').fill('2024-01-15');

    // Two elements share the accessible name "Create" while the modal is
    // open (the modal's own submit button plus the header trigger button
    // underneath it) — .last() is the actual submit button.
    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden();

    /* Act & Assert */
    await page.goto(tenantPath('/relations'));
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(relationNumber, { delay: 30 });

    const resultRow = page.locator('table tbody tr').first();
    await expect(resultRow).toContainText(relationNumber, { timeout: 10000 });
    // The customer-name column is server-truncated (e.g. "E2E Relati...") —
    // a short leading slice survives that regardless of name length.
    await expect(resultRow).toContainText(uniqueName.slice(0, 10));
    await expect(resultRow).toContainText('Active');
  });
});
