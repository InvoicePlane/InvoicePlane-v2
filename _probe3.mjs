import { chromium } from '@playwright/test';

const BASE = 'http://localhost:8001';

const browser = await chromium.launch();
const page = await browser.newPage();

await page.goto(`${BASE}/login`);
await page.fill('input[type="email"]', 'admin@invoiceplane.com');
await page.fill('input[type="password"]', 'password');
await page.click('[type="submit"]');
await page.waitForURL(/\/ivplv2\/dashboard/, { timeout: 60000 });

// quote 276 already has summary = PROBE-MARKER-1789284514818 from the earlier probe
await page.goto(`${BASE}/ivplv2/quotes/276/edit`);
await page.waitForSelector('form.fi-sc-form');

const notesSection = page.locator('section', { hasText: 'Quote notes' }).first();
await notesSection.locator('header').click();

const notesField = page.locator('[wire\\:partial="schema-component::form.notes"]');
await notesField.waitFor({ state: 'attached' });

// Wait for the CodeMirror line content to actually contain something
// (real condition, not a fixed timeout guess).
await expect_eventually(async () => {
  const text = await notesField.locator('.CodeMirror-code').innerText();
  return text.trim().length > 0 ? text : null;
});

async function expect_eventually(fn, timeoutMs = 8000, intervalMs = 250) {
  const start = Date.now();
  while (Date.now() - start < timeoutMs) {
    const result = await fn();
    if (result) {
      console.log('Rendered notes text:', JSON.stringify(result));
      return result;
    }
    await page.waitForTimeout(intervalMs);
  }
  console.log('TIMED OUT waiting for non-empty notes content');
  return null;
}

const livewireState = await page.evaluate(() => {
  const found = [];
  for (const c of Livewire.all()) {
    if (c.$wire && c.$wire.data && 'notes' in c.$wire.data) {
      found.push(c.$wire.data.notes);
    }
  }
  return found;
});
console.log('Livewire .data.notes after fresh reload:', JSON.stringify(livewireState));

await browser.close();
