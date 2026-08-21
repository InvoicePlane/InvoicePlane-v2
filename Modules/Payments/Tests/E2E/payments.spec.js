import { test, expect } from '@playwright/test';
import { tenantPath } from '../../Core/Tests/E2E/tenant-path.js';

test.describe('Payments', () => {
  test('list page renders the payments table', async ({ page }) => {
    await page.goto(tenantPath('/payments'));

    await expect(page.locator('table')).toBeVisible();
  });
});
