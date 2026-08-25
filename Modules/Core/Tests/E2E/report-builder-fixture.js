import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * Report templates are pure-file storage (see
 * Modules/Core/Services/ReportTemplateStorage.php) — a company template is
 * just a folder of manifest.json + bands.json under
 * storage/app/report_templates/{company_id}/{slug}/. Writing those two files
 * directly is far more reliable than building fixture content by drag-and-
 * dropping bricks from the sidebar through the UI (the only way to add a
 * brick to an *empty* band, since Mason's sidebar bricks are drag-only), and
 * keeps the actual drag/drop interactions under test the sole responsibility
 * of the browser rather than the fixture setup.
 */

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Matches database/seeders/DatabaseSeeder.php: the `ivplv2` company is always
// seeded at id=22.
const E2E_COMPANY_ID = process.env.E2E_COMPANY_ID || '22';

const REPORT_TEMPLATES_ROOT = path.resolve(__dirname, '../../../../storage/app/report_templates');

export function reportTemplatePath(slug) {
  return path.join(REPORT_TEMPLATES_ROOT, E2E_COMPANY_ID, slug);
}

/**
 * @param {string} slug
 * @param {Record<string, Array<{brick: string, width?: string, config?: object}>>} bandEntries
 */
export function writeReportTemplateFixture(slug, bandEntries = {}) {
  const dir = reportTemplatePath(slug);
  fs.mkdirSync(dir, { recursive: true });

  const manifest = {
    name: `E2E ${slug}`,
    slug,
    type: 'invoice',
    version: 1,
    cloned_from: null,
  };

  const bands = {
    header: [],
    group_header: [],
    details: [],
    group_footer: [],
    footer: [],
    ...bandEntries,
  };

  fs.writeFileSync(path.join(dir, 'manifest.json'), JSON.stringify(manifest, null, 4));
  fs.writeFileSync(path.join(dir, 'bands.json'), JSON.stringify(bands, null, 4));
}

export function deleteReportTemplateFixture(slug) {
  fs.rmSync(reportTemplatePath(slug), { recursive: true, force: true });
}

/** A `spacer` brick entry — allowed in every band, minimal config. */
export function spacerBrick(height, width = 'full') {
  return { brick: 'spacer', width, config: { height } };
}
