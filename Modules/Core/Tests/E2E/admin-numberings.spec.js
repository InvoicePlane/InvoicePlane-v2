import { test, expect } from './test.js';
import { assertRealListContent } from './list-assertions.js';

test.describe('Admin: Numberings', () => {
  test('list page shows real numbering schemes', async ({ page }) => {
    /* Arrange */
    await page.goto('/admin/numberings');

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('creating a numbering scheme persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    // NumberingResource only registers an 'index' route — creation
    // happens through the "New numbering" header modal.
    await page.goto('/admin/numberings');
    await page.getByRole('button', { name: 'New numbering' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    // Company is a searchable Choices.js-style combobox (real listbox,
    // reachable via its own aria-controls id — needed because a bare
    // option-role match also hits unrelated hidden elements elsewhere in
    // the DOM). Type is a plain dropdown with no aria-controls at all;
    // its options are reachable directly by their visible text.
    //
    // Deliberately pick the admin's own active company ("InvoicePlane
    // Corporation" / search_code ivplv2) rather than an arbitrary one:
    // the Numbering model uses BelongsToCompany, whose global scope
    // filters every query (including this admin list) by the admin's
    // own currently-active company session — a numbering scheme created
    // here for a DIFFERENT company saves correctly but then silently
    // never appears in this same list, search or no search. That's a
    // real architectural gap worth flagging on its own, not something
    // this create-flow test should paper over by working around it
    // entirely.
    await modal.getByRole('combobox').nth(0).click();
    const companyListboxId = await modal.getByRole('combobox').nth(0).getAttribute('aria-controls');
    await page.locator(`#${companyListboxId}`).getByRole('option', { name: 'InvoicePlane Corporation', exact: true }).click();

    // Type renders as a genuine native <select> (fi-fo-select-native),
    // unlike Company's searchable Choices.js-style combobox above.
    await modal.getByLabel('Type*').selectOption('Customer');

    const name = `E2E Admin Numbering ${Date.now()}`;
    await modal.getByLabel('Name*').fill(name);
    await modal.getByLabel('Next ID*').fill('1');

    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    await page.goto('/admin/numberings');
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(name, { delay: 30 });
    await expect(page.locator('table tbody tr').first()).toContainText(name.slice(0, 10), { timeout: 15000 });
  });
});
