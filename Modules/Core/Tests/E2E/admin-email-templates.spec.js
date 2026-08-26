import { test, expect } from './test.js';
import { assertRealListContent } from './list-assertions.js';

test.describe('Admin: Email Templates', () => {
  test('list page shows real email templates', async ({ page }) => {
    /* Arrange */
    await page.goto('/admin/email-templates');

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('creating an email template persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    // EmailTemplateResource only registers an 'index' route — creation
    // happens through the "New email template" header modal.
    await page.goto('/admin/email-templates');
    await page.getByRole('button', { name: 'New email template' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    const title = `E2E Admin Template ${Date.now()}`;
    await modal.getByLabel('Title*').fill(title);

    // This Select uses plain enum ->options(), so it renders as a real
    // native <select> (unlike the searchable Choices.js-style selects
    // elsewhere in this suite) — selectOption(), not click-an-option.
    await modal.getByLabel('Type*').selectOption('text');

    await modal.getByLabel('Subject').fill('E2E subject line');

    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    await page.goto('/admin/email-templates');
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(title, { delay: 30 });
    await expect(page.locator('table tbody tr').first()).toContainText(title.slice(0, 10), { timeout: 10000 });
  });
});
