import { test, expect } from './test.js';
import { assertRealListContent } from './list-assertions.js';

test.describe('Admin: Tax Rates', () => {
  test('list page shows real tax rates', async ({ page }) => {
    /* Arrange */
    await page.goto('/admin/tax-rates');

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('creating a tax rate persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    // TaxRateResource only registers an 'index' route — creation happens
    // through the "New tax rate" header modal.
    await page.goto('/admin/tax-rates');
    await page.getByRole('button', { name: 'New tax rate' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    const name = `E2E Admin Tax Rate ${Date.now()}`;
    await modal.getByLabel('Name*').fill(name);
    await modal.getByLabel('Tax rate code*').fill(`E2E-${Date.now()}`);

    await modal.getByLabel('Tax rate type*').click();
    await page.getByRole('option', { name: 'Exclusive', exact: true }).click();

    await modal.getByLabel('Percentage*').fill('7.5');

    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    await page.goto('/admin/tax-rates');
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(name, { delay: 30 });
    await expect(page.locator('table tbody tr').first()).toContainText(name.slice(0, 10), { timeout: 10000 });
  });

  test('a tax rate submitted without a code is rejected gracefully, not with a silent 500', async ({ page }) => {
    /* Arrange */
    // Regression guard for a real bug: tax_rates.code is NOT NULL with no
    // DB default, but the "Tax rate code" field had no asterisk and no
    // ->required() rule — leaving it blank (the reasonable reading of an
    // unmarked field) passed client validation and blew up as an
    // unhandled SQLSTATE 500 on every submission (Modules/Core/Filament/
    // Admin/Resources/TaxRates/Schemas/TaxRateForm.php). Worse: the
    // failure was invisible — the modal's outer wrapper always reports
    // hidden/zero-height regardless of state, so it looked like nothing
    // was wrong at all.
    const errors = [];
    page.on('console', (msg) => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', (err) => errors.push(err.message));

    await page.goto('/admin/tax-rates');
    await page.getByRole('button', { name: 'New tax rate' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    await modal.getByLabel('Name*').fill(`E2E No Code Rate ${Date.now()}`);
    await modal.getByLabel('Tax rate type*').click();
    await page.getByRole('option', { name: 'Exclusive', exact: true }).click();
    await modal.getByLabel('Percentage*').fill('5');
    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    // The form stays open with a validation error, not a silent "success".
    await page.waitForTimeout(1000);
    await expect(modal.getByLabel('Name*')).toBeVisible();
    expect(errors, `unexpected error(s) submitting without a code:\n${errors.join('\n')}`).toHaveLength(0);
  });
});
