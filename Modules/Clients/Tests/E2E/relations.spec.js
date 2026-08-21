import { test, expect } from '@playwright/test';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';

test.describe('Relations (Customers)', () => {
  test('list page renders the relations table', async ({ page }) => {
    await page.goto(tenantPath('/relations'));

    await expect(page.locator('table')).toBeVisible();
  });
});
