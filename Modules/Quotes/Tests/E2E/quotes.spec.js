import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';

/**
 * Per the e2e-behavioral-testing skill: these tests prove the feature works,
 * not that a tag exists. Seeded quote data (customer names, quote numbers)
 * isn't deterministic across fresh seeds, so the list test asserts real
 * rendered content by shape (a genuine status value, cross-checked against
 * the table's own independently-computed total) rather than a hardcoded
 * value — and the create test performs the actual create flow and confirms
 * the record is persisted and findable afterward.
 */
test.describe('Quotes', () => {
  test('list page shows real, correctly-scoped seeded quotes', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/quotes'));

    /* Act & Assert */
    await assertRealListContent(page, /^(Draft|Sent|Viewed|Approved|Rejected|Converted)$/i);
  });

  test('creating a quote persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/quotes/create'));
    await expect(page.getByRole('heading', { name: 'Create Quote' })).toBeVisible();
    await expect(page.locator('form.fi-sc-form')).toBeVisible();

    /* Act */
    // Customer Name (a Relation, preloaded/searchable select) — pick
    // whichever seeded relation sorts first; the assertion below checks
    // this exact one ends up on the created quote, so it doesn't matter
    // which.
    await page.getByRole('combobox', { name: /^customer name/i }).click();
    const firstCustomerOption = page.getByRole('option').first();
    const customerName = (await firstCustomerOption.innerText()).trim();
    await firstCustomerOption.click();

    await page.getByRole('combobox', { name: /^status/i }).click();
    await page.getByRole('option', { name: 'Draft', exact: true }).click();

    // Numbering scheme is required to save; "Quote" is the only one seeded.
    await page.getByRole('combobox', { name: /^numbering/i }).click();
    await page.getByRole('option', { name: 'Quote', exact: true }).click();

    const quoteNumber = await page.locator('#form\\.quote_number').inputValue();

    await page.locator('button[type="submit"]', { hasText: 'Create' }).click();

    /* Assert */
    // Outcome #1: redirected to the new record's own edit page, not back to
    // the form (validation failure) or an error page.
    await expect(page).toHaveURL(/\/quotes\/\d+\/edit$/);
    await expect(page.getByRole('heading', { name: 'Edit Quote' })).toBeVisible();

    /* Act & Assert */
    // Outcome #2: the record is actually persisted and independently
    // findable through the list/search UI — not just "the redirect looked
    // right." (The list defaults to an older-first page, so the new row
    // isn't necessarily on page 1 without searching.)
    await page.goto(tenantPath('/quotes'));
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    // .fill() doesn't reliably trigger Livewire's debounced live-search
    // binding; type it out like a real user would.
    await search.pressSequentially(quoteNumber, { delay: 30 });

    const resultRow = page.locator('table tbody tr').first();
    await expect(resultRow).toContainText(quoteNumber);
    // The customer-name column is server-truncated (e.g. "Beahan, Te...") —
    // a short leading slice survives that regardless of name length.
    await expect(resultRow).toContainText(customerName.slice(0, 8));
  });

  test('"Add New Row" on the quote items repeater adds a real row, with no errors', async ({ page }) => {
    /* Arrange */
    const errors = [];
    page.on('console', (msg) => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', (err) => errors.push(err.message));

    await page.goto(tenantPath('/quotes/create'));
    // The "Quote items" section starts collapsed — the "Add New Row" button
    // doesn't exist in the DOM until it's expanded.
    await page.getByRole('heading', { name: 'Quote items' }).click();
    const addButton = page.getByRole('button', { name: 'Add New Row' });
    await expect(addButton).toBeVisible();
    const itemsBefore = await page.locator('.fi-fo-repeater-item').count();

    /* Act */
    await addButton.click();

    /* Assert */
    await expect(page.locator('.fi-fo-repeater-item')).toHaveCount(itemsBefore + 1);
    expect(errors, `unexpected error(s) adding a quote item row:\n${errors.join('\n')}`).toHaveLength(0);
  });
});
