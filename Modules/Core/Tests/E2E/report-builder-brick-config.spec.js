import { test, expect } from './test.js';
import { tenantPath } from './tenant-path.js';
import {
  writeReportTemplateFixture,
  deleteReportTemplateFixture,
  brickEntry,
  bandFrame,
} from './report-builder-fixture.js';
import { trans } from './lang-helper.js';

/**
 * Helper to test a single checkbox configuration change on a placed brick.
 * Sets up fixture, visits builder, verifies initial state in band iframe,
 * opens slideover, toggles checkbox, saves, and verifies updated preview state.
 */
async function testBrickCheckboxToggle({
  page,
  slug,
  band,
  brick,
  initialConfig,
  checkboxLabel,
  targetCheckedState,
  modalHeading,
  assertInitial,
  assertUpdated,
}) {
  writeReportTemplateFixture(slug, {
    [band]: [brickEntry(brick, initialConfig)],
  });

  try {
    await page.goto(tenantPath(`/report-builder/company/invoice/${slug}`));
    const frame = bandFrame(page, band);
    await expect(frame.locator('.mason-block')).toHaveCount(1);

    if (assertInitial) {
      await assertInitial(frame);
    }

    // Block controls (including Edit) appear when block is selected
    await frame.locator('.mason-block').click();
    await frame.locator('.mason-block [data-action="edit"]').click();

    // The slideover opens on the top-level page
    if (modalHeading) {
      await expect(page.getByRole('heading', { name: modalHeading })).toBeVisible({ timeout: 15000 });
    }

    const checkbox = page.getByLabel(checkboxLabel, { exact: true });
    await expect(checkbox).toBeVisible({ timeout: 15000 });
    await checkbox.setChecked(targetCheckedState);

    const saveButton = page.getByRole('button', { name: 'Save Changes', exact: true });
    await saveButton.click();

    if (modalHeading) {
      await expect(page.getByRole('heading', { name: modalHeading })).toBeHidden({ timeout: 15000 });
    }

    if (assertUpdated) {
      await assertUpdated(frame);
    }
  } finally {
    deleteReportTemplateFixture(slug);
  }
}

test.describe('HeaderCompanyBrick — checkbox configuration', () => {
  const BAND = 'header';
  const BRICK = 'header_company';
  const HEADING = trans('company_header_settings');

  test('show_vat_id: unchecking removes VAT ID from preview', async ({ page }) => {
    await testBrickCheckboxToggle({
      page,
      slug: 'e2e-company-vat-id',
      band: BAND,
      brick: BRICK,
      initialConfig: { show_vat_id: true, show_phone: true, show_email: true, show_address: true },
      checkboxLabel: trans('show_vat_id'),
      targetCheckedState: false,
      modalHeading: HEADING,
      assertInitial: async (frame) => {
        await expect(frame.locator('.mason-block-content')).toContainText(trans('vat_id'));
      },
      assertUpdated: async (frame) => {
        await expect(frame.locator('.mason-block-content')).not.toContainText(trans('vat_id'));
      },
    });
  });

  test('show_phone: unchecking removes Phone from preview', async ({ page }) => {
    await testBrickCheckboxToggle({
      page,
      slug: 'e2e-company-phone',
      band: BAND,
      brick: BRICK,
      initialConfig: { show_vat_id: true, show_phone: true, show_email: true, show_address: true },
      checkboxLabel: trans('show_phone'),
      targetCheckedState: false,
      modalHeading: HEADING,
      assertInitial: async (frame) => {
        await expect(frame.locator('.mason-block-content')).toContainText(trans('phone'));
      },
      assertUpdated: async (frame) => {
        await expect(frame.locator('.mason-block-content')).not.toContainText(trans('phone'));
      },
    });
  });

  test('show_email: unchecking removes Email from preview', async ({ page }) => {
    await testBrickCheckboxToggle({
      page,
      slug: 'e2e-company-email',
      band: BAND,
      brick: BRICK,
      initialConfig: { show_vat_id: true, show_phone: true, show_email: true, show_address: true },
      checkboxLabel: trans('show_email'),
      targetCheckedState: false,
      modalHeading: HEADING,
      assertInitial: async (frame) => {
        await expect(frame.locator('.mason-block-content')).toContainText(trans('email'));
      },
      assertUpdated: async (frame) => {
        await expect(frame.locator('.mason-block-content')).not.toContainText(trans('email'));
      },
    });
  });

  test('show_address: unchecking removes Address from preview', async ({ page }) => {
    await testBrickCheckboxToggle({
      page,
      slug: 'e2e-company-address',
      band: BAND,
      brick: BRICK,
      initialConfig: { show_vat_id: true, show_phone: true, show_email: true, show_address: true },
      checkboxLabel: trans('show_address'),
      targetCheckedState: false,
      modalHeading: HEADING,
      assertInitial: async (frame) => {
        await expect(frame.locator('.mason-block-content')).toContainText(trans('company_address'));
      },
      assertUpdated: async (frame) => {
        await expect(frame.locator('.mason-block-content')).not.toContainText(trans('company_address'));
      },
    });
  });
});
