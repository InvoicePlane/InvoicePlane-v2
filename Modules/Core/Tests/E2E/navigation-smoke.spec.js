import { test, expect } from './test.js';
import { tenantPath } from './tenant-path.js';

/**
 * Boot-error smoke test.
 *
 * The company panel's page/resource list is registered inside
 * CompanyPanelProvider::panel(), which runs on every request the panel
 * handles. A single bad import there (e.g. a deleted page class still
 * referenced — see the ReportTemplates/ReportBuilder incident) throws a
 * fatal "Class not found" on *every* company panel page, not just the one
 * that references it.
 *
 * PHPUnit/Livewire tests never exercise this boot path the way a real HTTP
 * request does, so this is the one guard that actually catches it. Every
 * route below is confirmed live via `php artisan route:list` — keep this
 * list in sync with CompanyPanelProvider's registered resources/pages.
 */
const KNOWN_ROUTES = [
  '/dashboard',
  '/relations',
  '/contacts',
  '/quotes',
  '/quotes/create',
  '/invoices',
  '/invoices/create',
  '/expenses',
  '/expenses/create',
  '/expense-categories',
  '/payments',
  '/products',
  '/product-categories',
  '/product-units',
  '/projects',
  '/tasks',
  '/note-templates',
  '/email-templates',
  '/company-users',
  '/settings',
  '/my-profile',
  '/my-companies',
];

const ERROR_MARKERS = [
  'Whoops',
  'Server Error',
  'Class "',
  'not found',
  'Exception',
  'Stack trace',
];

for (const route of KNOWN_ROUTES) {
  test(`page loads without a boot error: ${route}`, async ({ page }) => {
    /* Arrange & Act */
    const response = await page.goto(tenantPath(route));

    /* Assert */
    expect(response, `no response for ${route}`).not.toBeNull();
    expect(response.status(), `HTTP ${response.status()} on ${route}`).toBeLessThan(400);

    const bodyText = await page.locator('body').innerText();
    for (const marker of ERROR_MARKERS) {
      expect(bodyText, `found error marker "${marker}" on ${route}`).not.toContain(marker);
    }
  });
}

test('sidebar navigation is present on the dashboard', async ({ page }) => {
  /* Arrange & Act */
  await page.goto(tenantPath('/dashboard'));

  /* Assert */
  // The page has two <nav> landmarks (topbar + sidebar) — bare
  // getByRole('navigation') is a strict-mode violation; scope to the one
  // this test actually means.
  await expect(page.getByRole('navigation', { name: 'Sidebar navigation' })).toBeVisible();
});
