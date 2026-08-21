import { test, expect } from './test.js';
import { assertRealListContent } from './list-assertions.js';

test.describe('Admin: Users', () => {
  test('list page shows real users', async ({ page }) => {
    /* Arrange */
    await page.goto('/admin/users');

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('creating a user persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    // UserResource only registers an 'index' route — creation happens
    // through the "New Users" header modal on the list page.
    await page.goto('/admin/users');
    await page.getByRole('button', { name: 'New Users' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    const userName = `E2E Admin User ${Date.now()}`;
    await modal.getByLabel('Name*').fill(userName);
    const email = `e2e-admin-user-${Date.now()}@invoiceplane.test`;
    await modal.getByLabel('Email*').fill(email);
    await modal.getByLabel('Password*').fill('E2ePassword!2024');

    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    await page.goto('/admin/users');
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(email, { delay: 30 });
    const resultRow = page.locator('table tbody tr').first();
    await expect(resultRow).toContainText(email, { timeout: 10000 });
    await expect(resultRow).toContainText(userName.slice(0, 10));
  });
});
