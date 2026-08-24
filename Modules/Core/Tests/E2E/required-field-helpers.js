/**
 * mind-the-gap-again: shared logic for the real frontend counterpart to
 * every PHPUnit "it_fails_to_create_X_without_required_Y" test.
 *
 * The insight this is built on: a PHPUnit test like that exists *because*
 * the backend knows a column is required — and that exact fact (NOT NULL,
 * no default) is already the ground truth
 * Modules/Core/Commands/ExportFormDbSchemaCommand.php exports (the same
 * source `FormDbConstraintAuditTest.php` uses). Instead of matching PHPUnit
 * test names to E2E test titles (fuzzy, and "a title that matches still
 * isn't a test that works"), each generated test here fills in a fully
 * valid form except one required field, submits for real, and asserts the
 * browser genuinely rejects it — mirroring exactly what the PHPUnit test
 * proves, through the real UI, driven by the same schema fact on both
 * sides.
 *
 * Two distinct rejection mechanisms exist in this app, confirmed by
 * inspecting real live DOM/network traffic (not assumed):
 *
 * 1. Native-HTML-backed fields (plain text/textarea/number/date/native
 *    <select>, Filament's ->required() rendered as a real `required`
 *    attribute): the BROWSER blocks the submission itself via native
 *    constraint validation — no request ever reaches the server. Same
 *    mechanism/assertion as admin-tax-rates.spec.js (commit fc25764):
 *    `el.checkValidity() === false` and a non-empty `el.validationMessage`.
 * 2. Filament's custom JS-driven Select (relationship fields — no native
 *    <select>, just a `<button role="combobox">`, no native `required`
 *    semantics to hook into): the request DOES reach the server, Livewire
 *    returns real validation errors, and Filament renders them into the DOM
 *    as `<p data-validation-error class="fi-fo-field-wrp-error-message">`
 *    inside that field's `.fi-fo-field` wrapper. Assert that element is
 *    visible with non-empty text.
 *
 * Fields this can't handle (repeaters like invoice/quote line items, rich
 * text, file uploads) are skipped with a reason — they're not scalar DB
 * columns the schema export describes anyway, so this never claims to
 * cover them. That's a real, separate gap left for hand-written E2E tests,
 * not silently pretended away.
 */

import { execSync } from 'child_process';
import { test, expect } from './test.js';
import { tenantPath } from './tenant-path.js';

const NON_FORM_COLUMNS = new Set(['id', 'created_at', 'updated_at', 'deleted_at']);

/**
 * Runs `php artisan mind-the-gap:export-schema` and returns only the
 * resources belonging to one module (matched by the `Modules\<Name>\`
 * segment of resourceClass), plus the shared knownGaps allowlist.
 *
 * On a dev box, this always runs inside the project's real dev container
 * (see CLAUDE.md's Docker section), never bare host PHP: bare `php artisan`
 * only reaches the DB when DB_HOST is 127.0.0.1, which it isn't on this dev
 * box (DB_HOST=mariadb, a container-internal hostname) — going straight to
 * Docker is the one path that reliably works with no per-environment
 * debugging. Override the container/path via env vars if they differ from
 * this repo's documented dev stack.
 *
 * In CI (.github/workflows/e2e-tests.yml), PHP runs directly on the
 * ubuntu-latest runner with DB_HOST=127.0.0.1 — there is no
 * ivpldock-workspace-1 container to exec into, so `php artisan` is invoked
 * directly there instead.
 */
export function loadSchemaForModule(moduleName) {
  const raw = process.env.CI
    ? execSync('php artisan mind-the-gap:export-schema', { encoding: 'utf8' })
    : (() => {
        const container = process.env.MIND_THE_GAP_DOCKER_CONTAINER || 'ivpldock-workspace-1';
        const appPath = process.env.MIND_THE_GAP_DOCKER_APP_PATH || '/var/www/projects/invoiceplane-2/ivplv2';

        return execSync(
          `docker exec -e XDEBUG_MODE=off ${container} sh -c "cd ${appPath} && php artisan mind-the-gap:export-schema"`,
          { encoding: 'utf8' }
        );
      })();

  const schema = JSON.parse(raw);
  const prefix = `Modules\\${moduleName}\\`;

  return {
    resources: schema.resources.filter((r) => r.resourceClass.startsWith(prefix)),
    knownGaps: schema.knownGaps || {},
  };
}

/** Same "is this column really form-required" test as the backend audit's checkRequired(). */
export function requiredColumns(resource) {
  return resource.columns.filter(
    (c) => !c.nullable && c.default === null && !c.auto_increment && !NON_FORM_COLUMNS.has(c.name)
  );
}

function resourcePath(resource) {
  if (resource.panel === 'admin') return `/admin/${resource.slug}`;
  return tenantPath(`/${resource.slug}`);
}

/**
 * Opens a resource's create form, whichever shape it takes — most
 * resources are a header "New X"/"Add X" action opening a modal; a few
 * (Invoices, Quotes) register a real `/create` page reached via a link
 * instead. Returns a Playwright locator `scope` both shapes can be
 * queried/filled through identically.
 */
async function openCreateForm(page, resource) {
  await page.goto(resourcePath(resource), { waitUntil: 'domcontentloaded' });

  const button = page.getByRole('button', { name: /^(New|Add)\s/i }).first();
  if (await button.isVisible({ timeout: 5000 }).catch(() => false)) {
    await button.click();
    const dialog = page.getByRole('dialog');
    // Not dialog.waitFor({state:'visible'}): this app's modal wrapper can
    // report a zero-height bounding box (confirmed via getBoundingClientRect
    // — display:block, opacity:1, but height:0) while fully rendered and
    // interactive underneath, which fails Playwright's stricter built-in
    // visibility check indefinitely. Alpine's own open/closed signal is the
    // 'fi-modal-open' class binding (x-bind:class="{'fi-modal-open': isOpen}")
    // — poll that directly instead.
    await page.waitForFunction(
      (el) => el && el.classList.contains('fi-modal-open'),
      await dialog.elementHandle(),
      { timeout: 15000 }
    );
    return dialog;
  }

  const link = page.getByRole('link', { name: /^New\s/i }).first();
  if (await link.isVisible({ timeout: 3000 }).catch(() => false)) {
    await link.click();
    await page.waitForLoadState('domcontentloaded');
    return page.locator('body');
  }

  return null;
}

/**
 * Reads every recognizable form field inside `scope` and classifies it.
 * Two field shapes exist in this app (confirmed via live DOM inspection):
 * a native input/select/textarea bound via wire:model="data.X" (name="data.X"
 * is present for some, absent for others — e.g. native date inputs), or
 * Filament's custom Select — a `<button role="combobox" id="form.X">` with
 * no native form semantics at all. Anything else (repeaters, rich text,
 * file uploads, nested/relation sub-paths) is silently excluded — not this
 * generator's concern, see file header.
 */
async function extractFieldMeta(scope) {
  return scope.evaluate((scopeEl) => {
    const wrappers = Array.from(scopeEl.querySelectorAll('.fi-fo-field'));
    const out = [];

    // Same "page form" vs "modal action" split as the fi-select id below:
    // wire:model's value is "data.<field>" on a dedicated create PAGE, but
    // "mountedActions.0.data.<field>" inside a header-action MODAL — and in
    // the modal case there's no `name` attribute at all. A CSS prefix
    // selector can't handle a variable-index "mountedActions.N." prefix, so
    // match broadly and extract the key with a regex instead. The attribute
    // NAME itself also varies: a field with ->live() or similar renders as
    // wire:model.live.debounce.500ms="..." (Livewire modifiers appended to
    // the attribute name) rather than plain wire:model — getAttribute()
    // with a fixed name misses those entirely, so scan all attributes for
    // one starting with "wire:model".
    const DATA_PATH_RE = /^(?:data\.|mountedActions\.\d+\.data\.)(.+)$/;

    for (const wrp of wrappers) {
      let nativeCtl = null;
      let nativeRawKey = null;
      for (const el of wrp.querySelectorAll('input, select, textarea')) {
        const wireModelAttr = Array.from(el.attributes).find((a) => a.name.startsWith('wire:model'));
        const match = DATA_PATH_RE.exec((wireModelAttr && wireModelAttr.value) || el.getAttribute('name') || '');
        if (match) {
          nativeCtl = el;
          nativeRawKey = match[1];
          break;
        }
      }
      // Two confirmed-live id shapes for a Filament custom-select combobox:
      // "form.<field>" on a dedicated create PAGE, but "mountedActionSchema0.
      // <field>" when the create form is opened as a header-action MODAL
      // (mountAction) — which is how most resources in this app create
      // records (Relations, Payments, Products, ...). Missing the second
      // shape silently dropped every such field from this audit entirely.
      const fiSelectBtn = wrp.querySelector(
        'button[role="combobox"][id^="form."], button[role="combobox"][id^="mountedActionSchema"]'
      );

      let name = null;
      let kind = null;
      let required = false;

      if (nativeCtl) {
        name = nativeRawKey.replace(/\[\]$/, '');
        const tag = nativeCtl.tagName;
        const type = (nativeCtl.getAttribute('type') || '').toLowerCase();
        required = nativeCtl.required || nativeCtl.getAttribute('aria-required') === 'true';

        if (tag === 'SELECT') kind = 'native-select';
        else if (tag === 'TEXTAREA') kind = 'textarea';
        else if (type === 'date' || type === 'datetime-local') kind = 'native-date';
        else if (type === 'checkbox') kind = 'checkbox';
        else if (type === 'number') kind = 'number';
        else kind = 'text';
      } else if (fiSelectBtn) {
        name = fiSelectBtn.id.replace(/^(?:form|mountedActionSchema\d+)\./, '');
        kind = 'fi-select';
        // No native `required` to read here — Filament signals it only via
        // the label's required-mark <sup>, the same convention every
        // hand-written E2E test in this suite already depends on
        // (getByLabel('Customer*'), etc).
        required = !!wrp.querySelector('.fi-fo-field-label-required-mark');
      } else {
        continue;
      }

      if (!name || name.includes('.')) continue; // nested/repeater path — out of scope

      // fi-select's real DOM id (e.g. "mountedActionSchema0.relation_type")
      // is kept verbatim so later lookups target the actual element instead
      // of re-deriving a "form.<name>" id that's wrong for modal actions.
      out.push({ name, kind, required, id: fiSelectBtn ? fiSelectBtn.id : null });
    }

    return out;
  });
}

function nativeControlLocator(scope, name) {
  // Match by id suffix ("form.<name>" or "mountedActionSchema0.<name>"),
  // not by wire:model value: a field with ->live() or similar renders its
  // binding as wire:model.live.debounce.500ms="..." — a different
  // attribute NAME, which a value-based CSS selector like [wire\:model$=…]
  // can never match (CSS has no attribute-name wildcard). id is stable
  // regardless of Livewire modifiers, so key off that instead. The leading
  // "." in the suffix guards against a false match on a different field
  // whose name happens to end the same way (e.g. "name" inside
  // "company_name") since field names never contain ".".
  return scope.locator(`[id$=".${name}"], [name$=".${name}"]`);
}

/**
 * Fills one field with a representative valid value, dispatched by the
 * kind extractFieldMeta assigned it. Best-effort: a field this can't
 * confidently fill (e.g. a native-select with no non-empty options in this
 * environment) throws, and the caller treats that as a reason to skip the
 * whole test rather than fill it wrong and produce a false result.
 */
async function fillValidValue(scope, page, field) {
  const ctl = nativeControlLocator(scope, field.name);

  // A required, readOnly field (e.g. RelationForm's unique_name) is driven
  // by another field's ->afterStateUpdated()/->afterStateHydrated() hook,
  // not direct user input — Playwright correctly refuses to .fill() it
  // ("element is not editable"). Treat it as already-satisfied rather than
  // un-fillable: it's real, dehydrated, submitted input, just not typed by
  // hand, the same "not this generic filler's concern" boundary the
  // backend audit draws around disabled/non-dehydrated fields.
  if (await ctl.evaluate((el) => el.readOnly).catch(() => false)) {
    return;
  }

  switch (field.kind) {
    case 'text':
      await ctl.fill('Test Value');
      return;
    case 'textarea':
      await ctl.fill('Test value content.');
      return;
    case 'number':
      await ctl.fill('10');
      return;
    case 'native-date':
      await ctl.fill('2026-09-01');
      return;
    case 'checkbox':
      await ctl.check();
      return;
    case 'native-select': {
      const options = await ctl.locator('option').all();
      for (const opt of options) {
        const val = await opt.getAttribute('value');
        if (val) {
          await ctl.selectOption(val);
          return;
        }
      }
      throw new Error(`native-select '${field.name}' has no non-empty option to pick`);
    }
    case 'fi-select': {
      const btn = scope.locator(`[id="${field.id}"]`);
      await btn.click();
      const controlsId = await btn.getAttribute('aria-controls');
      const listbox = page.locator(`#${controlsId}`);
      const firstOption = listbox.getByRole('option').first();
      await firstOption.waitFor({ state: 'visible', timeout: 5000 });
      await firstOption.click();
      return;
    }
    default:
      throw new Error(`no fill strategy for field kind '${field.kind}'`);
  }
}

async function clickSubmit(scope) {
  await scope.locator('button[type="submit"]').filter({ hasText: /Create|Save/i }).first().click();
}

/**
 * The two rejection-assertion mechanisms described in the file header,
 * dispatched by field kind. Returns { rejected: boolean, mechanism, detail }.
 */
async function assertOmissionRejected(scope, page, field) {
  if (field.kind === 'fi-select') {
    await clickSubmit(scope);
    // Real Livewire round-trip, not a native browser block — give it time.
    await page.waitForTimeout(1500);
    // Walk up from the field's own control to its .fi-fo-field wrapper in
    // one evaluate() call — more reliable here than chaining Playwright's
    // locator .filter({has}) across a scope that can be either a dialog or
    // the full page body.
    const text = await scope.evaluate((scopeEl, id) => {
      const btn = scopeEl.querySelector(`[id="${id}"]`) || document.querySelector(`[id="${id}"]`);
      const wrp = btn ? btn.closest('.fi-fo-field') : null;
      const err = wrp ? wrp.querySelector('.fi-fo-field-wrp-error-message') : null;
      return err ? err.textContent.trim() : '';
    }, field.id);
    return { rejected: text !== '', mechanism: 'livewire-error-message', detail: text };
  }

  // Native-HTML-backed kinds: the browser blocks submission before any
  // request fires — assert checkValidity()/validationMessage directly,
  // the same real mechanism admin-tax-rates.spec.js already established
  // for this exact class of field (commit fc25764).
  await clickSubmit(scope);
  await page.waitForTimeout(500);
  const ctl = nativeControlLocator(scope, field.name);
  const isValid = await ctl.evaluate((el) => el.checkValidity());
  const validationMessage = await ctl.evaluate((el) => el.validationMessage);
  return { rejected: isValid === false && validationMessage !== '', mechanism: 'native-constraint-validation', detail: validationMessage };
}

/**
 * Full flow for one (resource, targetFieldName) pair: open the create
 * form, fill every OTHER required field validly, leave targetFieldName
 * blank, submit, and report whether the browser genuinely rejected it.
 */
export async function testRequiredFieldOmission(page, resource, targetFieldName) {
  const scope = await openCreateForm(page, resource);
  if (!scope) {
    return { skipped: 'no create form (button or link) found for this resource' };
  }

  const allFields = await extractFieldMeta(scope);
  const requiredFields = allFields.filter((f) => f.required);
  const target = requiredFields.find((f) => f.name === targetFieldName);

  if (!target) {
    return { skipped: `'${targetFieldName}' was not found as a required rendered field (repeater/rich-text/file-upload, or not required in the DOM — a real mismatch mind-the-gap's own audit should already have caught)` };
  }

  for (const field of requiredFields) {
    if (field.name === targetFieldName) continue;
    try {
      await fillValidValue(scope, page, field);
    } catch (error) {
      return { skipped: `could not fill sibling required field '${field.name}' with a valid value: ${error.message}` };
    }
  }

  return assertOmissionRejected(scope, page, target);
}

/**
 * Registers one `mind-the-gap-again` test per required column of every
 * resource in `moduleName`, via `testRequiredFieldOmission` above.
 *
 * A `result.skipped` outcome (no create form, target field not rendered as
 * required, or an unfillable sibling field) used to `return` straight out
 * of the test — which Playwright reports as a PASS, with only an
 * annotation attached. That let a real form/DB mismatch (exactly the bug
 * class this suite exists to catch) hide behind a green checkmark.
 *
 * Now only a skip whose gap is explicitly declared in
 * FormDbGapKnownExceptions::KNOWN_GAPS (the same registry
 * FormDbConstraintAuditTest.php uses) is allowed to skip; every other skip
 * reason fails the test, the same "record it or it's a bug" discipline
 * FormDbConstraintAuditTest.php already applies on the backend.
 */
export function registerRequiredFieldOmissionTests(moduleName) {
  const schema = loadSchemaForModule(moduleName);

  for (const resource of schema.resources) {
    const fields = requiredColumns(resource);
    if (fields.length === 0) continue;

    test.describe(`mind-the-gap-again: ${resource.panel}/${resource.slug}`, () => {
      for (const column of fields) {
        test(`omitting required '${column.name}' is rejected by the browser`, async ({ page }) => {
          const result = await testRequiredFieldOmission(page, resource, column.name);

          if (result.skipped) {
            test.info().annotations.push({ type: 'skipped-reason', description: result.skipped });

            const gapKey = `${resource.resourceClass}:${column.name}`;
            if (Object.prototype.hasOwnProperty.call(schema.knownGaps, gapKey)) {
              test.skip(true, result.skipped);
              return;
            }

            throw new Error(
              `Undeclared skip for ${gapKey}: ${result.skipped}\n`
              + 'If this is a deliberate, reviewed gap, register it in '
              + "FormDbGapKnownExceptions::KNOWN_GAPS — don't leave it silently skipped."
            );
          }

          expect(result.rejected, `mechanism=${result.mechanism} detail=${result.detail}`).toBe(true);
        });
      }
    });
  }
}
