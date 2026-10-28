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

await page.getByRole('combobox', { name: /^customer name/i }).click();
await page.getByRole('option').first().click();

await page.getByRole('combobox', { name: /^status/i }).click();
await page.getByRole('option', { name: 'Draft', exact: true }).click();

await page.getByRole('combobox', { name: /^numbering/i }).click();
await page.getByRole('option', { name: 'Quote', exact: true }).click();

const quoteNumber = await page.locator('#form\\.quote_number').inputValue();

// Expand and fill Notes
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

// blur the field
await page.keyboard.press('Tab');
await page.waitForTimeout(300);

await page.locator('button[type="submit"]', { hasText: 'Create' }).click();
await page.waitForURL(/\/quotes\/\d+\/edit$/, { timeout: 15000 });

console.log('Created, now at:', page.url());

// Fresh reload to prove a real round trip, not just in-memory state.
await page.reload();
await page.waitForSelector('form.fi-sc-form');

const notesSection2 = page.locator('section', { hasText: 'Quote notes' }).first();
await notesSection2.locator('header').click();
await page.waitForTimeout(500);
const notesField2 = page.locator('[wire\\:partial="schema-component::form.notes"]');
await notesField2.waitFor({ state: 'attached' });
await page.waitForTimeout(1000);

const rendered = await notesField2.locator('.CodeMirror-code').innerText();
console.log('quote number:', quoteNumber);
console.log('marker used:', marker);
console.log('notes content after reload:', JSON.stringify(rendered));
console.log('MATCH:', rendered.includes(marker));

await browser.close();
