import { test, expect } from './test.js';
import { loginAdmin } from './admin.js';

test.describe('Admin: Connect', () => {
  test('admin can connect a connector with fresh session', async ({ page }) => {
    /* Arrange */
    await loginAdmin(page);

    /* Act */
    await page.goto('/admin/tracks');

    /* Assert */
    await expect(page.getByTestId('connect-wrapper')).toBeVisible({ timeout: 30000 });
  });
});
