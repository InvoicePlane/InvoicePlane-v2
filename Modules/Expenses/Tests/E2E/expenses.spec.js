import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { tenantPath } from '../../../Core/Tests/E2E/tenant-path.js';
import { assertRealListContent } from '../../../Core/Tests/E2E/list-assertions.js';
import { assertAddRowIncrementsRepeater } from '../../../Core/Tests/E2E/error-capture.js';
import { registerRequiredFieldOmissionTests } from '../../../Core/Tests/E2E/required-field-helpers.js';

test.describe('Expenses', () => {
  test('list page shows real, correctly-scoped seeded expenses', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/expenses'));

    /* Act & Assert */
    // Modules/Expenses/Enums/ExpenseStatus.php — draft, submitted, approved,
    // reimbursed, billed, paid.
    await assertRealListContent(page, /^(draft|submitted|approved|reimbursed|billed|paid)$/i);
  });

  test('create page renders the expense form', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/expenses/create'));

    /* Act */
    const heading = page.getByRole('heading', { name: 'Create Expense' });
    // Every Filament create page also has a hidden topbar logout <form> —
    // `form.fi-sc-form` is the real one; bare `form` is a strict-mode
    // violation (resolves to 2 elements) on every create page in this app.
    const form = page.locator('form.fi-sc-form');

    /* Assert */
    await expect(heading).toBeVisible();
    await expect(form).toBeVisible();
  });

  test('"Add New Row" on the expense items repeater adds a real row, with no errors', async ({ page }) => {
    /* Arrange, Act & Assert */
    await assertAddRowIncrementsRepeater(page, { createPath: '/expenses/create', itemLabel: 'expense item' });
  });

  test('expense categories page shows real, correctly-scoped seeded categories', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/expense-categories'));

    /* Act & Assert */
    await assertRealListContent(page);
  });
});

/**
 * mind-the-gap-again: real frontend counterpart to this module's PHPUnit
 * "it_fails_to_create_X_without_required_Y" tests — for each field listed,
 * fills a valid create form except that one field and asserts the browser
 * rejects it. See Core/Tests/E2E/required-field-helpers.js.
 *
 * company_id (both resources) is left off — tenant-injected, not a form field.
 */
registerRequiredFieldOmissionTests('Expenses', {
  'company/expenses': ['expense_number', 'expense_status', 'expense_type', 'expensed_at', 'expense_amount'],
  'company/expense-categories': ['category_name'],
});
