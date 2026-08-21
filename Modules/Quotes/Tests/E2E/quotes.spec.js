import { test, expect } from '@playwright/test';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';

/**
 * Per the e2e-behavioral-testing skill: these tests prove the feature works,
 * not that a tag exists. Seeded quote data (customer names, quote numbers)
 * isn't deterministic across fresh seeds, so the list test asserts real
 * rendered content by shape (a genuine status value, a non-empty row) rather
 * than a hardcoded value — and the create test performs the actual create
 * flow and confirms the record is persisted and findable afterward.
 */
test.describe('Quotes', () => {
  test('list page shows seeded quotes', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/quotes'));

    /* Act */
    const rows = page.locator('table tbody tr');

    /* Assert */
    await expect(rows).not.toHaveCount(0);
    // A real quote-status value can only render from an actual seeded row —
    // an empty or broken query would leave this absent.
    await expect(
      rows.first().getByText(/^(Draft|Sent|Viewed|Approved|Rejected|Converted)$/)
    ).toBeVisible();
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
});
