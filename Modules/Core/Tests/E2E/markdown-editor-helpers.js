/**
 * Filament v5's MarkdownEditor renders EasyMDE/CodeMirror, not a native
 * textarea or a ProseMirror/TipTap contenteditable — confirmed by reading
 * the live rendered DOM, not assumed. The field itself:
 *   - lives inside a collapsible `<section>` that must be expanded before
 *     the field's lazy `wire:partial="schema-component::form.{key}"`
 *     content is attached at all;
 *   - exposes its real input as a `role="textbox"` accessible via its
 *     Filament label (Playwright's getByRole('textbox', { name }) — not a
 *     native <textarea name=...>, so `.fill()` on a plain locator doesn't
 *     work; real keystrokes via `page.keyboard.type()` do, and land in
 *     Livewire's entangled state immediately (confirmed via
 *     `Livewire.all()[...].$wire.data` inspection, not assumed);
 *   - re-renders its visible text into `.CodeMirror-code` asynchronously
 *     after a fresh page load, so reading it back needs polling, not a
 *     fixed timeout — the value is correct as soon as it's non-empty, and
 *     was repeatedly observed to still be empty at a naive fixed 1s wait
 *     after the field attaches.
 */

async function expandSection(page, sectionHeading) {
  const section = page.locator('section', { hasText: sectionHeading }).first();
  await section.locator('header').click();
  return section;
}

/**
 * Expand the section containing a MarkdownEditor field (by its Livewire
 * schema key, e.g. 'notes', 'invoice_terms') and return its wrapper locator,
 * ready to read or fill.
 */
export async function openMarkdownEditorField(page, sectionHeading, fieldKey) {
  await expandSection(page, sectionHeading);
  const field = page.locator(`[wire\\:partial="schema-component::form.${fieldKey}"]`);
  await field.waitFor({ state: 'attached' });
  // The lazy x-load-src script (markdown-editor.js) needs a moment to fetch
  // and initialize the EasyMDE instance before the textbox role exists.
  await field.getByRole('textbox').waitFor({ state: 'attached', timeout: 5000 });

  return field;
}

/**
 * Fill a MarkdownEditor field with real keystrokes (not `.fill()` — this is
 * a CodeMirror-backed field, not a native textarea) and blur it.
 */
export async function fillMarkdownEditor(page, field, text) {
  const textbox = field.getByRole('textbox');
  await textbox.scrollIntoViewIfNeeded();
  // Filament's sticky top action bar can intercept a plain click at this
  // scroll position even though the field is genuinely visible underneath;
  // force is safe here since we're about to type into it directly, not
  // relying on the click itself to prove interactivity.
  await textbox.click({ force: true });
  await page.keyboard.type(text, { delay: 15 });
  await page.keyboard.press('Tab');
}

/**
 * Read back a MarkdownEditor field's currently rendered text, polling
 * because CodeMirror repopulates its visible content asynchronously after
 * a fresh page load — a fixed wait flakes intermittently empty.
 */
export async function readMarkdownEditorText(page, field, timeoutMs = 8000, intervalMs = 300) {
  const code = field.locator('.CodeMirror-code');
  const start = Date.now();

  while (Date.now() - start < timeoutMs) {
    const text = (await code.innerText()).trim();
    // A genuinely empty CodeMirror line renders a single zero-width space,
    // not an empty string — treat that as "still empty."
    if (text.length > 0 && text !== '​') return text;
    await page.waitForTimeout(intervalMs);
  }

  return '';
}
