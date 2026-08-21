import { test, expect } from '../../../Core/Tests/E2E/test.js';
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

  test('creating a project persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    // Projects have no dedicated /projects/create page — ProjectResource
    // only registers an 'index' route; creation happens through the
    // "New Project" header modal on the list page.
    await page.goto(tenantPath('/projects'));
    await page.getByRole('button', { name: 'New Project' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    await modal.getByRole('combobox', { name: /^customer/i }).click();
    // A bare option-role match also hits the hidden native "records per
    // page" select (5/10/25/50) that's always present in the DOM; filter
    // those pure-digit entries out to reach the real customer options.
    await page.getByRole('option').filter({ hasNotText: /^\s*\d+\s*$/ }).first().click();

    const projectName = `E2E Project ${Date.now()}`;
    await modal.getByLabel('Project Name*').fill(projectName);

    await modal.getByLabel('Project Status*').click();
    await page.getByRole('option', { name: 'Planned', exact: true }).click();

    // Start At renders as a readonly display input backed by a calendar
    // popup (Filament's date-time picker) — not fillable via .fill();
    // click it open and pick the highlighted "today" cell. Two matching
    // calendar-day-today elements exist in the DOM (Start At + End At
    // pickers share the same markup); .first() is the one that's open.
    await modal.getByLabel('Start At*').click();
    await page.locator('.fi-fo-date-time-picker-calendar-day-today').first().click();

    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    await page.goto(tenantPath('/projects'));
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(projectName, { delay: 30 });
    await expect(page.locator('table tbody tr').first()).toContainText(projectName.slice(0, 10), { timeout: 10000 });
  });

  test('tasks page shows real, correctly-scoped seeded tasks', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath('/tasks'));

    /* Act & Assert */
    // Modules/Projects/Enums/TaskStatus.php — cancelled, completed,
    // in_progress, not_started, open, paid.
    await assertRealListContent(page, /^(cancelled|completed|in[ _]progress|not[ _]started|open|paid)$/i);
  });

  test('creating a task persists it and it appears in the list', async ({ page }) => {
    /* Arrange */
    // Tasks have no dedicated /tasks/create page — TaskResource only
    // registers an 'index' route; creation happens through the "New Task"
    // header modal on the list page.
    await page.goto(tenantPath('/tasks'));
    await page.getByRole('button', { name: 'New Task' }).click();
    const modal = page.getByRole('dialog');

    /* Act */
    const taskName = `E2E Task ${Date.now()}`;
    await modal.getByLabel('Task Name*').fill(taskName);

    // Project's search results render as plain text (custom
    // getSearchResultsUsing), same as Payments' Invoice field — typing is
    // required even though ->preload() is set, and a bare option-role
    // match also hits the hidden "records per page" select.
    await modal.getByRole('combobox', { name: /^project/i }).click();
    await page.keyboard.type('e', { delay: 30 });
    await page.waitForTimeout(600);
    await page.getByRole('option').filter({ hasNotText: /^\s*\d+\s*$/ }).first().click();

    await modal.getByLabel('Task status*').click();
    await page.getByRole('option').filter({ hasNotText: /^\s*\d+\s*$/ }).first().click();

    // Task finish date is a readonly display input backed by a calendar
    // popup, same as Projects' Start At. Escape would close the whole
    // action modal (not just the calendar), so dismiss it by clicking a
    // neutral spot inside the modal instead.
    await modal.getByLabel('Task finish date*').click();
    await page.locator('.fi-fo-date-time-picker-calendar-day-today').first().click();
    await page.getByRole('heading', { name: 'Create Task' }).click();

    await modal.getByRole('combobox', { name: /^tax rate/i }).click();
    await page.getByRole('option').filter({ hasNotText: /^\s*\d+\s*$/ }).first().click();

    await modal.getByRole('button', { name: 'Create', exact: true }).last().click();

    /* Assert */
    await expect(modal).toBeHidden({ timeout: 10000 });

    /* Act & Assert */
    await page.goto(tenantPath('/tasks'));
    const search = page.getByPlaceholder(/search/i);
    await search.click();
    await search.pressSequentially(taskName, { delay: 30 });
    await expect(page.locator('table tbody tr').first()).toContainText(taskName.slice(0, 10), { timeout: 10000 });
  });
});
