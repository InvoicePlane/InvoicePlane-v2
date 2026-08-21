import { test, expect } from '@playwright/test';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';

test.describe('Projects', () => {
  test('list page renders the projects table', async ({ page }) => {
    await page.goto(tenantPath('/projects'));

    await expect(page.locator('table')).toBeVisible();
  });

  test('tasks page renders the tasks table', async ({ page }) => {
    await page.goto(tenantPath('/tasks'));

    await expect(page.locator('table')).toBeVisible();
  });
});
