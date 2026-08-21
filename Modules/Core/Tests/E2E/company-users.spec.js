import { test, expect } from './test.js';
import { tenantPath } from './tenant-path.js';

test.describe('Company Users', () => {
  test('"Add Team Member" opens without error and rejects an unknown email gracefully', async ({ page }) => {
    /* Arrange */
    const errors = [];
    page.on('console', (msg) => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', (err) => errors.push(err.message));

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
