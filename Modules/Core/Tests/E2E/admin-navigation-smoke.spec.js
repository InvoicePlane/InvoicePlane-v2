import { test, expect } from './test.js';

/**
 * Boot-error smoke test for the ADMIN panel — the sibling of
 * navigation-smoke.spec.js (company panel). AdminPanelProvider::panel()
 * registers its own resource/page list independently of the company
 * panel's, so a bad import there can break every admin page without
 * touching the company panel at all — this is the one guard that
 * exercises that boot path. Routes confirmed live via
 * `php artisan route:list`; the admin panel is not tenant-scoped, so
 * these are absolute paths, not run through tenantPath().
 */
const KNOWN_ADMIN_ROUTES = [
  '/admin',
  '/admin/companies',
  '/admin/users',
  '/admin/email-templates',
  '/admin/numberings',
  '/admin/tax-rates',
  '/admin/settings',
  '/admin/profile',
  '/admin/role-permissions-page',
  '/admin/import-v1-page',
];

const ERROR_MARKERS = [
  'Whoops',
  'Server Error',
  'Class "',
  'not found',
  'Exception',
  'Stack trace',
];

for (const route of KNOWN_ADMIN_ROUTES) {
  test(`admin page loads without a boot error: ${route}`, async ({ page }) => {
    /* Arrange & Act */
    const response = await page.goto(route);

    /* Assert */
    expect(response, `no response for ${route}`).not.toBeNull();
    expect(response.status(), `HTTP ${response.status()} on ${route}`).toBeLessThan(400);

    const bodyText = await page.locator('body').innerText();
    for (const marker of ERROR_MARKERS) {
      expect(bodyText, `found error marker "${marker}" on ${route}`).not.toContain(marker);
    }
  });
}

test('admin sidebar navigation is present on the dashboard', async ({ page }) => {
  /* Arrange & Act */
  await page.goto('/admin');

  /* Assert */
  await expect(page.getByRole('heading', { name: /dashboard/i })).toBeVisible();
});
