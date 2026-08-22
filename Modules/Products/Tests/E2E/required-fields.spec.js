import { test, expect } from '../../../Core/Tests/E2E/test.js';
import { loadSchemaForModule, requiredColumns, testRequiredFieldOmission } from '../../../Core/Tests/E2E/required-field-helpers.js';

/**
 * mind-the-gap-again: real frontend counterpart to this module's PHPUnit
 * "it_fails_to_create_X_without_required_Y" tests. For every required DB
 * column of every Products resource (Products, Product Units, Product Categories), fills a fully valid
 * create form except that one field and asserts the browser genuinely
 * rejects the omission. See Core/Tests/E2E/required-field-helpers.js for
 * the full mechanism and the two real rejection paths it asserts.
 */
const schema = loadSchemaForModule('Products');

for (const resource of schema.resources) {
  const fields = requiredColumns(resource);
  if (fields.length === 0) continue;

  test.describe(`mind-the-gap-again: ${resource.panel}/${resource.slug}`, () => {
    for (const column of fields) {
      test(`omitting required '${column.name}' is rejected by the browser`, async ({ page }) => {
        const result = await testRequiredFieldOmission(page, resource, column.name);

        if (result.skipped) {
          test.info().annotations.push({ type: 'skipped-reason', description: result.skipped });
          return;
        }

        expect(result.rejected, `mechanism=${result.mechanism} detail=${result.detail}`).toBe(true);
      });
    }
  });
}
