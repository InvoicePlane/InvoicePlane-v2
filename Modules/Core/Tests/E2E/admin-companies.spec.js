import { test, expect } from './test.js';
import { assertRealListContent } from './list-assertions.js';

test.describe('Admin: Companies', () => {
  test('list page shows real companies', async ({ page }) => {
    /* Arrange */
    await page.goto('/admin/companies');

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('creating a company persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    // CompanyResource only registers an 'index' route — creation happens
    // through the "New company" header modal on the list page.
    await page.goto('/admin/companies');
    await page.getByRole('button', { name: 'New company' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    const name = `E2E Admin Company ${Date.now()}`;
    await modal.getByLabel('Name*').fill(name);
    // companies.search_code is varchar(10) in the database.
    const searchCode = `e2e${Date.now()}`.slice(0, 10);
    await modal.getByLabel('Search code*').fill(searchCode);

    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    await page.goto('/admin/companies');
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(searchCode, { delay: 30 });
    const resultRow = page.locator('table tbody tr').first();
    await expect(resultRow).toContainText(searchCode, { timeout: 10000 });
    await expect(resultRow).toContainText(name.slice(0, 10));
  });

  test('a duplicate search code is rejected gracefully, not with a 500', async ({ page }) => {
    /* Arrange */
    // Regression guard for a real bug: companies.search_code has a unique
    // DB constraint but the form had no matching ->unique() rule, so a
    // duplicate value passed client-side validation and blew up as an
    // unhandled SQL integrity-constraint error instead of a validation
    // message (Modules/Core/Filament/Admin/Resources/Companies/Schemas/
    // CompanyForm.php). Same failure class as, and found alongside, the
    // overlong-search_code bug fixed above.
    const errors = [];
    page.on('console', (msg) => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', (err) => errors.push(err.message));

    // Any existing seeded company's search_code works as the duplicate —
    // the seeded super_admin's own company ("ivplv2") always exists.
    const duplicateCode = 'ivplv2';

    await page.goto('/admin/companies');
    await page.getByRole('button', { name: 'New company' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    await modal.getByLabel('Name*').fill(`E2E Duplicate Code Co ${Date.now()}`);
    await modal.getByLabel('Search code*').fill(duplicateCode);
    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    // The modal stays open with a validation error — it must NOT close as
    // if the (impossible) save had succeeded, and must never surface as
    // an unhandled server exception.
    await page.waitForTimeout(1000);
    await expect(modal.getByLabel('Name*')).toBeVisible();
    expect(errors, `unexpected error(s) submitting a duplicate search code:\n${errors.join('\n')}`).toHaveLength(0);
  });
});
