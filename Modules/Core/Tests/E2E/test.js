import { test as base, expect } from '@playwright/test';

/**
 * Drop-in replacement for `import { test, expect } from '@playwright/test'`
 * — every test that imports from here automatically captures console
 * errors and uncaught page exceptions, and records them as annotations on
 * the test result. This is what makes them show up in
 * error-summary-reporter.js's end-of-run report.
 *
 * This does NOT fail a test just because a browser-side error occurred —
 * most tests aren't specifically about error-freedom, and auto-failing on
 * any console noise would produce false positives unrelated to what the
 * test is actually checking. Tests that specifically need to assert
 * "no errors occurred" (the +/add-row-button regression guards) still wire
 * up their own local listeners and assert on them directly — this fixture
 * is for *visibility* across the whole suite, including tests that never
 * thought to check, which is exactly where a silent error would otherwise
 * hide.
 */
export const test = base.extend({
  page: async ({ page }, use, testInfo) => {
    const errors = [];

    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(`[console] ${msg.text()}`);
    });
    page.on('pageerror', (err) => {
      errors.push(`[pageerror] ${err.message}`);
    });

    await use(page);

    for (const description of errors) {
      testInfo.annotations.push({ type: 'browser-error', description });
    }
  },
});

export { expect };
