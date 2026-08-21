import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';

test.describe('Payments', () => {
  test('list page shows real, correctly-scoped seeded payments', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/payments'));

    /* Act & Assert */
    // Modules/Payments/Enums/PaymentStatus.php — completed, failed, pending,
    // refunded, partially_refunded.
    await assertRealListContent(page, /^(completed|failed|pending|refunded|partially[ _]refunded)$/i);
  });
});
