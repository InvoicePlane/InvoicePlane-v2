import { test, expect } from './test.js';
import { tenantPath } from './tenant-path.js';
import { assertRealListContent } from './list-assertions.js';
import { captureConsoleErrors } from './error-capture.js';

test.describe('Company Users', () => {
  test('list page shows real, correctly-scoped seeded team members', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/company-users'));

    /* Act & Assert */
    await assertRealListContent(page);
  });

  test('"Add Team Member" adds a known existing user and they appear in the list', async ({ page }) => {
    /* Arrange */
    const errors = captureConsoleErrors(page);

    // Seeded deterministically by database/seeders/DatabaseSeeder.php: a
    // real User row that belongs to no company, specifically so this test
    // can exercise the success path (look up an existing user, add them to
    // the current tenant) without depending on another test's random data.
    const knownEmail = 'e2e-unattached-user@invoiceplane.test';

    await page.goto(tenantPath('/company-users'));
    const addButton = page.getByRole('button', { name: 'Add Team Member' });
    await expect(addButton).toBeVisible();

    /* Act */
    await addButton.click();
    const modal = page.getByRole('dialog');
    const emailField = modal.getByLabel(/email/i);
    await expect(emailField).toBeVisible();
    await emailField.fill(knownEmail);
    await modal.getByRole('button', { name: 'Add Member' }).click();

    /* Assert */
    await expect(page.getByText('Team Member Added')).toBeVisible();
    await page.reload();
    await expect(page.getByText('E2E Unattached User')).toBeVisible();
    expect(errors, `unexpected error(s) adding a known team member:\n${errors.join('\n')}`).toHaveLength(0);
  });

  test('"Add Team Member" opens without error and rejects an unknown email gracefully', async ({ page }) => {
    /* Arrange */
    const errors = captureConsoleErrors(page);

    await page.goto(tenantPath('/company-users'));
    const addButton = page.getByRole('button', { name: 'Add Team Member' });
    await expect(addButton).toBeVisible();

    /* Act */
    await addButton.click();

    // The modal's outer role="dialog" wrapper is `position: static; height:
    // 0` by Filament's own CSS (its window content is `position: fixed`,
    // outside its parent's box) — assert on real content inside it instead.
    const modal = page.getByRole('dialog');
    const emailField = modal.getByLabel(/email/i);
    await expect(emailField).toBeVisible();

    // A syntactically valid but nonexistent email exercises the real
    // server-side lookup path (a literally empty field is blocked by native
    // HTML5 `required` validation before any request fires, which doesn't
    // prove anything about this action's own error handling).
    const unknownEmail = `nonexistent-e2e-user-${Date.now()}@example.invalid`;
    await emailField.fill(unknownEmail);
    await modal.getByRole('button', { name: 'Add Member' }).click();

    /* Assert */
    // The single most important assertion: adding an unrecognized email is
    // a real user mistake, not a bug — it must fail gracefully, never throw.
    await expect(page.getByText('User Not Found')).toBeVisible();
    expect(errors, `unexpected error(s) submitting "Add Team Member":\n${errors.join('\n')}`).toHaveLength(0);
  });
});
