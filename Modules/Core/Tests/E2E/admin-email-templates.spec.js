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

    // email_templates.body is a NOT NULL longText column with no default and
    // the form marks it ->required() (EmailTemplateForm.php) — without this
    // the create silently fails client-side validation and no row is written.
    await modal.getByLabel('Body*').fill('E2E body content');

    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    // Not expect(modal).toBeHidden(): this app's modal wrapper (role="dialog")
    // is `position: static; height: 0` by Filament's own CSS regardless of
    // open/closed state, so that assertion is trivially true even while the
    // modal is still open. Assert on a submitted field instead.
    await expect(modal.getByLabel('Title*')).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    await page.goto('/admin/email-templates');
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(title, { delay: 30 });
    await expect(page.locator('table tbody tr').first()).toContainText(title.slice(0, 10), { timeout: 10000 });
  });
});
