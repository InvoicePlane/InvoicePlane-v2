import { chromium } from '@playwright/test';

const BASE = 'http://localhost:8001';

async function pollForNonEmptyText(locator, page, timeoutMs = 8000, intervalMs = 300) {
  const start = Date.now();
  while (Date.now() - start < timeoutMs) {
    const text = (await locator.innerText()).trim();
    if (text.length > 0 && text !== '​') return text;
    await page.waitForTimeout(intervalMs);
  }
  return '';
}

async function expandAndGetEditor(page, sectionHeading, wireKey) {
  const section = page.locator('section', { hasText: sectionHeading }).first();
  await section.locator('header').click();
  const field = page.locator(`[wire\\:partial="schema-component::form.${wireKey}"]`);
  await field.waitFor({ state: 'attached' });
  await page.waitForTimeout(800);
  return field;
}

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

const marker = 'PROBE4-' + Date.now();
const notesField = await expandAndGetEditor(page, 'Quote notes', 'notes');
const notesTextbox = notesField.getByRole('textbox', { name: 'Notes' });
await notesTextbox.scrollIntoViewIfNeeded();
await notesTextbox.click({ force: true });
await page.keyboard.type(marker, { delay: 20 });
await page.waitForTimeout(500);

await page.locator('button[type="submit"]', { hasText: 'Create' }).click();
await page.waitForURL(/\/quotes\/\d+\/edit$/, { timeout: 15000 });
const editUrl = page.url();
console.log('Created at:', editUrl);

// Real fresh reload — new navigation, not in-memory Livewire state.
await page.goto(editUrl);
await page.waitForSelector('form.fi-sc-form');

const notesField2 = await expandAndGetEditor(page, 'Quote notes', 'notes');
const text = await pollForNonEmptyText(notesField2.locator('.CodeMirror-code'), page);
console.log('Text after fresh reload:', JSON.stringify(text));
console.log('MATCH:', text.includes(marker));

await browser.close();
process.exit(text.includes(marker) ? 0 : 1);
