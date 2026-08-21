import { test, expect } from '@playwright/test';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';

test.describe('Projects', () => {
  test('list page shows real, correctly-scoped seeded projects', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/projects'));

    /* Act & Assert */
    // Modules/Projects/Enums/ProjectStatus.php — planned, active, completed,
    // on_hold, cancelled.
    await assertRealListContent(page, /^(planned|active|completed|on[ _]hold|cancelled)$/i);
  });

  test('tasks page shows real, correctly-scoped seeded tasks', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/tasks'));

    /* Act & Assert */
    // Modules/Projects/Enums/TaskStatus.php — cancelled, completed,
    // in_progress, not_started, open, paid.
    await assertRealListContent(page, /^(cancelled|completed|in[ _]progress|not[ _]started|open|paid)$/i);
  });
});
