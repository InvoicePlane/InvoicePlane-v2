import { test, expect } from './test.js';
import { tenantPath } from './tenant-path.js';
import { trans } from './lang-helper.js';
import {
  writeReportTemplateFixture,
  deleteReportTemplateFixture,
  spacerBrick,
  bandFrame,
} from './report-builder-fixture.js';

/**
 * RB-10 (#763) — one golden-path smoke through the report builder: mount the
 * Livewire page + Mason canvas + per-band iframes, persist the template, open
 * the preview slide-over. This is the wiring (asset build, canvas JS, action
 * routes, the S3-1 ReportRenderer::renderPreview path) that the PHP suite
 * structurally cannot exercise. The download itself is covered by
 * InvoiceDownloadPdfActionTest and ReportBuilderSecurityTest; here we only
 * assert the row action is wired onto the list.
 */

const SLUG = 'smoke-e2e';

test.describe('Report Builder — save / preview smoke', () => {
  test.beforeEach(() => {
    writeReportTemplateFixture(SLUG, { footer: [spacerBrick(20)] });
  });

  test.afterEach(() => {
    deleteReportTemplateFixture(SLUG);
  });

  test('mounts the canvas, saves the template, and opens the preview slide-over', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (err) => errors.push(err.message));

    await page.goto(tenantPath(`/report-builder/company/invoice/${SLUG}`));

    /* the fixture brick painted into its band iframe */
    await expect(bandFrame(page, 'footer').locator('.mason-block')).toHaveCount(1);

    /* page-level Save */
    await page.getByRole('button', { name: trans('save'), exact: true }).click();
    await expect(page.getByText(trans('template_saved'))).toBeVisible({ timeout: 15000 });

    /* Preview slide-over renders a non-empty document */
    await page.getByRole('button', { name: trans('report_preview'), exact: true }).click();
    const dialog = page.getByRole('dialog');
    await expect(dialog.getByRole('heading', { name: trans('report_preview') })).toBeVisible({ timeout: 15000 });
    await expect(dialog.locator('.report-row, .report-block').first()).toBeVisible({ timeout: 15000 });
    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden({ timeout: 15000 });

    expect(errors, `page errors during the smoke flow:\n${errors.join('\n')}`).toHaveLength(0);
  });

  test('the invoice list exposes the Download PDF row action', async ({ page }) => {
    await page.goto(tenantPath('/invoices'));

    const firstRow = page.locator('table tbody tr').first();
    await expect(firstRow).toBeVisible({ timeout: 15000 });

    /*
     * The row actions sit inside a Filament ActionGroup: every row's dropdown
     * panel is pre-rendered into <body> and stays hidden until its trigger is
     * clicked. Open the first row's trigger, then assert only against the
     * items in the panel that is now visible — a page-wide getByText() matches
     * every rendered row's copy of the item and trips strict mode.
     */
    await firstRow.getByRole('button').last().click();
    await expect(
      page.locator('.fi-dropdown-list-item-label:visible', { hasText: trans('download_pdf') }),
    ).toHaveCount(1, { timeout: 15000 });
  });
});
