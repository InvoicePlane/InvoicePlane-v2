import { test, expect } from './test.js';
import { loginAdmin } from './admin.js';

test.describe('Admin: Integrations', () => {
  test('admin can integrate a connector with fresh session', async ({ page }) => {
    /* Arrange */
    await loginAdmin(page);

    /* Act */
    await page.goto('/admin/integrations');

    /* Assert */
    await expect(page.getByTestId('connect-wrapper')).toBeVisible({ timeout: 30000 });
  });
});
