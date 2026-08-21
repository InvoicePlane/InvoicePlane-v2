import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';

test.describe('Relations (Customers)', () => {
  test('list page shows real, correctly-scoped seeded relations', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/relations'));

    /* Act & Assert */
    // Modules/Clients/Enums/RelationStatus.php — ACTIVE, INACTIVE.
    await assertRealListContent(page, /^(Active|Inactive)$/i);
  });
});
