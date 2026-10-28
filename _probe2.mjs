import { chromium } from '@playwright/test';

const BASE = 'http://localhost:8001';

const browser = await chromium.launch();
const page = await browser.newPage();

await page.goto(`${BASE}/login`);
await page.fill('input[type="email"]', 'admin@invoiceplane.com');
await page.fill('input[type="password"]', 'password');
await page.click('[type="submit"]');
await page.waitForURL(/\/ivplv2\/dashboard/, { timeout: 60000 });

await page.goto(`${BASE}/ivplv2/quotes/create`);
await page.waitForSelector('form.fi-sc-form');

const notesSection = page.locator('section', { hasText: 'Quote notes' }).first();
await notesSection.locator('header').click();
await page.waitForTimeout(500);
const notesField = page.locator('[wire\\:partial="schema-component::form.notes"]');
await notesField.waitFor({ state: 'attached' });
await page.waitForTimeout(1000);

const marker = 'PROBE-MARKER-' + Date.now();
const notesTextbox = notesField.getByRole('textbox', { name: 'Notes' });
await notesTextbox.scrollIntoViewIfNeeded();
await notesTextbox.click({ force: true });
await page.keyboard.type(marker, { delay: 20 });
await page.waitForTimeout(1000);

// Reach into Livewire's own component data directly.
const livewireState = await page.evaluate(() => {
  const found = [];
  for (const c of Livewire.all()) {
    if (c.$wire && c.$wire.data && 'notes' in c.$wire.data) {
      found.push(c.$wire.data.notes);
    }
  }
  return found;
});
console.log('Livewire component .data.notes values found:', JSON.stringify(livewireState));

await browser.close();
