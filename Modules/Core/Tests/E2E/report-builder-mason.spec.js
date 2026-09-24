import { test, expect } from './test.js';
import { tenantPath } from './tenant-path.js';
import { captureConsoleErrors } from './error-capture.js';
import { writeReportTemplateFixture, deleteReportTemplateFixture, spacerBrick } from './report-builder-fixture.js';

/**
 * Regression coverage for 5 Mason report-builder bugs fixed alongside the
 * banded report builder (see CLAUDE.md's "Report Builder Guardrails"). Every
 * band renders its own iframe (name="mason-preview-iframe-data.bands.<band>"
 * — see resources/views/vendor/mason/iframe-preview-content.blade.php and
 * vendor/awcodes/mason/resources/views/mason.blade.php), so every locator
 * that touches drag/drop or block content must go through
 * `page.frame({ name })`, never `page` directly.
 *
 * Fixture bricks are written straight to the report_templates disk (see
 * report-builder-fixture.js) rather than built by dragging bricks in from
 * the sidebar — that's the one thing Mason has no non-drag way to do, and
 * it would make the fixture setup exercise the very code path some of these
 * tests exist to check.
 */

function bandFrame(page, band) {
  return page.frame({ name: `mason-preview-iframe-data.bands.${band}` });
}

async function dragAndDrop(frame, source, target) {
  const dataTransfer = await frame.evaluateHandle(() => new DataTransfer());
  await source.dispatchEvent('dragstart', { dataTransfer });
  await target.dispatchEvent('dragover', { dataTransfer });
  await target.dispatchEvent('drop', { dataTransfer });
  await source.dispatchEvent('dragend', { dataTransfer });
}

test.describe('Report Builder — Mason drop-zone regressions', () => {
  const SLUG = 'e2e-drop-zone-cursor';

  test.beforeEach(() => {
    writeReportTemplateFixture(SLUG, {
      details: [spacerBrick(10), spacerBrick(20), spacerBrick(30)],
    });
  });

  test.afterEach(() => {
    deleteReportTemplateFixture(SLUG);
  });

  test('the drop zone nearest the cursor is highlighted, not always the last one in the band (#4)', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath(`/report-builder/company/invoice/${SLUG}`));
    const frame = bandFrame(page, 'details');
    await expect(frame.locator('.mason-block')).toHaveCount(3);

    const source = frame.locator('.mason-block[data-block-index="0"]');
    // Drop zone between block 1 (height 20) and block 2 (height 30) — the
    // middle of the canvas, nowhere near the bottom.
    const middleZone = frame.locator('.mason-drop-zone[data-drop-index="2"]');
    const lastZone = frame.locator('.mason-drop-zone[data-drop-index="3"]');
    const lastBlock = frame.locator('.mason-block[data-block-index="2"]');

    /* Act */
    const dataTransfer = await frame.evaluateHandle(() => new DataTransfer());
    await source.dispatchEvent('dragstart', { dataTransfer });
    await middleZone.dispatchEvent('dragover', { dataTransfer });

    /* Assert */
    await expect(middleZone).toHaveClass(/active/);
    await expect(lastZone).not.toHaveClass(/active/);

    // The regression: every drop zone used to carry `order: 9999`, an order
    // value shared by every zone equally — the relative order *among drop
    // zones* is unaffected (same-order flex items keep document order among
    // themselves), but it does move every one of them after every block
    // (order 0), collapsing them all below the last block regardless of
    // which is active. The middle zone must render above the last block
    // (block 2, height 30) — not pushed below it to the bottom of the canvas.
    const middleBox = await middleZone.boundingBox();
    const lastBlockBox = await lastBlock.boundingBox();
    expect(middleBox.y).toBeLessThan(lastBlockBox.y);

    await source.dispatchEvent('dragend', { dataTransfer });
  });

  test('dropping a brick one slot below itself is a no-op (#5)', async ({ page }) => {
    /* Arrange */
    const errors = captureConsoleErrors(page);
    await page.goto(tenantPath(`/report-builder/company/invoice/${SLUG}`));
    const frame = bandFrame(page, 'details');
    await expect(frame.locator('.mason-block')).toHaveCount(3);

    const before = await frame.locator('.mason-block-content').allTextContents();

    const source = frame.locator('.mason-block[data-block-index="0"]');
    // data-drop-index="1" === draggedBlockIndex + 1 — the slot immediately
    // after the dragged block's own current position.
    const noOpZone = frame.locator('.mason-drop-zone[data-drop-index="1"]');

    /* Act */
    await dragAndDrop(frame, source, noOpZone);
    // Give any (incorrect) moveBlockRequest round-trip a chance to land
    // before asserting nothing changed.
    await page.waitForTimeout(500);

    /* Assert */
    const after = await frame.locator('.mason-block-content').allTextContents();
    expect(after).toEqual(before);
    expect(errors, `unexpected error(s) during a self-drop:\n${errors.join('\n')}`).toHaveLength(0);
  });

  test('deleting the only brick in a band re-shows the empty placeholder and accepts a new brick (#6)', async ({ page }) => {
    /* Arrange */
    const emptySlug = 'e2e-empty-band-placeholder';
    writeReportTemplateFixture(emptySlug, { details: [spacerBrick(42)] });

    try {
      await page.goto(tenantPath(`/report-builder/company/invoice/${emptySlug}`));
      const frame = bandFrame(page, 'details');
      await expect(frame.locator('.mason-block')).toHaveCount(1);

      /* Act */
      // Block controls (including Delete) are only shown once a block is
      // selected — see vendor/awcodes/mason/resources/css/preview.css.
      await frame.locator('.mason-block').click();
      await frame.locator('.mason-block [data-action="delete"]').click();

      /* Assert */
      await expect(frame.locator('.mason-block')).toHaveCount(0);
      await expect(frame.locator('.mason-drop-zone--empty')).toBeVisible();

      // And the band can actually be refilled. The sidebar's own drag
      // origin (a real cross-frame native HTML5 drag) is generic browser
      // plumbing untouched by this fix — what the fix actually governs is
      // the canvas's `drop` handler, so drive that directly with a
      // DataTransfer carrying the same 'brick' payload the sidebar's
      // dragstart would set, built inside the iframe's own JS context
      // (dispatchEvent needs the handle to belong to the target frame).
      const dropZone = frame.locator('.mason-drop-zone--empty');
      const dataTransfer = await frame.evaluateHandle((brickId) => {
        const dt = new DataTransfer();
        dt.setData('brick', brickId);

        return dt;
      }, 'spacer');
      await dropZone.dispatchEvent('dragover', { dataTransfer });
      await dropZone.dispatchEvent('drop', { dataTransfer });

      // Inserting opens the brick's config modal (ReportBrickAction) —
      // confirm with its defaults to actually land the brick. Label comes
      // from vendor/awcodes/mason/resources/lang/en/mason.php ("Insert
      // Brick", not just "Insert").
      const insertButton = page.getByRole('button', { name: 'Insert Brick', exact: true });
      await expect(insertButton).toBeVisible({ timeout: 10000 });
      await insertButton.click();

      await expect(frame.locator('.mason-block')).toHaveCount(1, { timeout: 10000 });
    } finally {
      deleteReportTemplateFixture(emptySlug);
    }
  });
});

test.describe('Report Builder — band editor isolation (#7)', () => {
  const SLUG = 'e2e-band-cross-talk';

  test.beforeEach(() => {
    writeReportTemplateFixture(SLUG, {
      header: [spacerBrick(11)],
      group_header: [spacerBrick(22)],
      details: [spacerBrick(33)],
      group_footer: [spacerBrick(44)],
      footer: [spacerBrick(55)],
    });
  });

  test.afterEach(() => {
    deleteReportTemplateFixture(SLUG);
  });

  test('deleting a brick in one band leaves every other band untouched', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath(`/report-builder/company/invoice/${SLUG}`));

    const bands = ['header', 'group_header', 'details', 'group_footer', 'footer'];
    const frames = Object.fromEntries(bands.map((band) => [band, bandFrame(page, band)]));

    for (const band of bands) {
      await expect(frames[band].locator('.mason-block')).toHaveCount(1);
    }

    /* Act */
    await frames.details.locator('.mason-block').click();
    await frames.details.locator('.mason-block [data-action="delete"]').click();
    await expect(frames.details.locator('.mason-block')).toHaveCount(0);

    /* Assert */
    for (const band of ['header', 'group_header', 'group_footer', 'footer']) {
      await expect(frames[band].locator('.mason-block')).toHaveCount(1);
    }
    await expect(frames.header.locator('.mason-block-content')).toContainText('11px');
    await expect(frames.group_header.locator('.mason-block-content')).toContainText('22px');
    await expect(frames.group_footer.locator('.mason-block-content')).toContainText('44px');
    await expect(frames.footer.locator('.mason-block-content')).toContainText('55px');
  });
});

test.describe('Report Builder — move to band respects the open brick (#8)', () => {
  const SLUG = 'e2e-move-to-band-stale-selection';

  test.beforeEach(() => {
    // Two bricks with different allowedBands() sets in the same band: spacer
    // is valid everywhere, header_company only in header/group_header. This
    // makes a stale `to_band` value observable — `group_header` is a valid
    // (silent, no-error) target for the spacer too, so if `to_band` were not
    // cleared when `position` changes, the wrong brick would move there
    // without Filament ever rejecting the submission.
    writeReportTemplateFixture(SLUG, {
      header: [spacerBrick(10), { brick: 'header_company', width: 'full', config: {} }],
    });
  });

  test.afterEach(() => {
    deleteReportTemplateFixture(SLUG);
  });

  test('changing the selected brick clears a previously chosen target band', async ({ page }) => {
    /* Arrange */
    await page.goto(tenantPath(`/report-builder/company/invoice/${SLUG}`));
    const headerFrame = bandFrame(page, 'header');
    const footerFrame = bandFrame(page, 'footer');
    const groupHeaderFrame = bandFrame(page, 'group_header');
    await expect(headerFrame.locator('.mason-block')).toHaveCount(2);

    await page.getByRole('button', { name: 'Move to band…' }).click();
    const modal = page.getByRole('dialog', { name: 'Move to band…' });
    // The role="dialog" element itself renders with no box of its own — its
    // content is a `position: fixed` child — so Playwright's visibility
    // check on the wrapper always reports "hidden" even once it's genuinely
    // on screen. Assert on real, boxed content instead. Mounting a Filament
    // action also round-trips through Livewire before Alpine shows the
    // modal, and this dev environment pays real Xdebug step-debug overhead
    // per request (see global-setup.js), hence the generous timeout.
    await expect(modal.getByRole('heading', { name: 'Move to band…' })).toBeVisible({ timeout: 15000 });

    const fromBand = modal.getByLabel('From band');
    const position = modal.getByLabel('Brick');
    const toBand = modal.getByLabel('To band');

    /* Act */
    await fromBand.selectOption({ label: 'Header' });
    // "2. Company Header" — the second brick in the band.
    await position.selectOption({ label: '2. Company Header' });
    await expect(toBand).toBeVisible();
    // Company Header is only allowed in header/group_header — with `header`
    // as the source, `group_header` is the only offered target.
    await toBand.selectOption({ label: 'Group Header' });
    await expect(toBand).toHaveValue('group_header');

    // Change our mind about which brick to move.
    await position.selectOption({ label: '1. Spacer' });

    /* Assert */
    // The fix: `to_band` must reset, not silently keep the previous band —
    // spacer is valid in `group_header` too, so a stale value here would
    // submit successfully and move the wrong brick.
    await expect(toBand).toHaveValue('');

    await toBand.selectOption({ label: 'Footer' });
    await modal.getByRole('button', { name: 'Submit' }).click();
    await expect(page.getByText('Brick moved')).toBeVisible();

    // Identify bricks by data-brick-id/data-config rather than rendered
    // text — ip.company_name's shipped translation is literally "Customer
    // Name", so the Company Header brick's own preview never contains the
    // string "Company Header".
    await expect(headerFrame.locator('.mason-block')).toHaveCount(1);
    await expect(headerFrame.locator('.mason-block')).toHaveAttribute('data-brick-id', 'header_company');
    await expect(footerFrame.locator('.mason-block')).toHaveCount(1);
    await expect(footerFrame.locator('.mason-block')).toHaveAttribute('data-brick-id', 'spacer');
    await expect(footerFrame.locator('.mason-block-content')).toContainText('10px');
    await expect(groupHeaderFrame.locator('.mason-block')).toHaveCount(0);
  });
});
