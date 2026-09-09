/**
 * mind-the-gap-again: shared logic for the real frontend counterpart to
 * every PHPUnit "it_fails_to_create_X_without_required_Y" test.
 *
 * The insight this is built on: a PHPUnit test like that exists *because*
 * the backend knows a column is required — and that exact fact (NOT NULL,
 * no default) is already the ground truth
 * Modules/Core/Commands/ExportFormDbSchemaCommand.php exports (the same
 * source `FormDbConstraintAuditTest.php` uses). Each generated test fills in
 * a fully valid create form except one required field, submits for real, and
 * asserts the browser genuinely rejects it — mirroring what the PHPUnit test
 * proves, through the real UI.
 *
 * Which fields get a test is an EXPLICIT choice per spec, not schema
 * iteration: `registerRequiredFieldOmissionTests(module, { 'panel/slug':
 * ['field', ...] })`. A required column that a user never types —
 * tenant-injected (company_id), auth-derived (user_id), service-computed
 * (invoice_total), relation-derived (customer_id), or a repeater/rich-text/
 * file-upload — is left off the list with a one-line comment in the spec.
 * The schema export is still loaded, purely as a stale-entry guard (a listed
 * field that's no longer NOT-NULL, or a resource key that no longer resolves,
 * fails loudly).
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
 * A required DB column with no user-fillable form field behind it (repeaters,
 * rich text, file uploads, plus relation-derived / service-computed /
 * tenant-injected columns like customer_id, invoice_total, company_id) is
 * `test.skip()`-ed with an annotation, not failed: whether such a column
 * needs a matching ->required() rule is the backend FormDbConstraintAuditTest's
 * call — it's form-field-driven and can answer it; this DB-column-driven
 * browser check cannot, and only claims the fields a user actually fills.
 */

import { execSync } from 'child_process';
import { test, expect } from './test.js';
import { tenantPath } from './tenant-path.js';

// Columns the framework fills, never a user-typed form input:
// - id / timestamps: Eloquent/DB managed.
// - company_id: injected by the BelongsToCompany trait from the Filament
//   tenant on create (see CLAUDE.md) — no resource renders it as a field.
//   FormDbConstraintAuditTest (the backend half) never flags it either
//   because that audit is form-field-driven and there's no field to check;
//   this generator is DB-column-driven, so it has to exclude it explicitly
//   or every company-panel resource produces an undeclared-skip failure for
//   a column no form was ever meant to expose.
const NON_FORM_COLUMNS = new Set(['id', 'created_at', 'updated_at', 'deleted_at', 'company_id']);

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
// The export command takes no module argument — it always dumps every
// panel's every resource, and callers filter afterwards. Eight spec files
// call this at collection time; without memoisation that's eight Laravel
// boots (or `docker exec`s) producing byte-identical JSON before the first
// assertion, multiplied again per Playwright worker. Parse once per process.
let _schemaCache;

function loadFullSchema() {
  if (_schemaCache) return _schemaCache;

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

  _schemaCache = JSON.parse(raw);

  return _schemaCache;
}

export function loadSchemaForModule(moduleName) {
  const schema = loadFullSchema();
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
      let readOnly = false;

      if (nativeCtl) {
        name = nativeRawKey.replace(/\[\]$/, '');
        const tag = nativeCtl.tagName;
        const type = (nativeCtl.getAttribute('type') || '').toLowerCase();
        required = nativeCtl.required || nativeCtl.getAttribute('aria-required') === 'true';
        readOnly = nativeCtl.readOnly || nativeCtl.getAttribute('aria-readonly') === 'true';

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
      out.push({ name, kind, required, readOnly, id: fiSelectBtn ? fiSelectBtn.id : null });
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
  // .first(): nativeControlLocator returns an id/name-suffix match that can
  // resolve to >1 node — .evaluate() and the .fill()/.check() actions below
  // are strict and would throw on a multi-match.
  const ctl = nativeControlLocator(scope, field.name).first();

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
      if (!controlsId) {
        throw new Error(`fi-select '${field.name}' opened no listbox (no aria-controls on the combobox)`);
      }
      // [id="..."], not `#${controlsId}` — Filament's generated ids carry
      // dots/colons that a bare CSS id selector misparses (same reason the
      // button above is matched with [id=...]).
      const listbox = page.locator(`[id="${controlsId}"]`);
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
  // Filament renders the create/save button differently by context: a real
  // type="submit" on a dedicated create PAGE, but a plain <button wire:click>
  // inside some header-action MODALS (e.g. "Add Team Member", Numbering) —
  // where the type="submit" selector matches nothing and .click() would hang
  // the whole 30s test timeout. Try both shapes with a short bounded wait,
  // and throw a classifiable error if neither is there so the caller can
  // record it as a harness gap rather than a failure.
  const candidates = [
    scope.getByRole('button', { name: /^(create|save)$/i }),
    scope.locator('button[type="submit"]').filter({ hasText: /create|save/i }),
  ];
  for (const c of candidates) {
    const btn = c.last();
    if (await btn.isVisible({ timeout: 3000 }).catch(() => false)) {
      await btn.click();
      return;
    }
  }
  throw new Error('SUBMIT_NOT_FOUND: no create/save button located in the form scope');
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

  // Negative signal first: checkValidity() === false is near-tautological for
  // an empty `required` input — true whether or not a submit was attempted or
  // blocked. If a Filament success notification appeared, the create went
  // through regardless of the constraint DOM, so the omission was NOT
  // rejected. (Covers a resource whose submit control isn't a real
  // type=submit, so the browser never blocks — see clickSubmit's comment.)
  const succeeded = await page
    .locator('.fi-no-notification')
    .filter({ has: page.locator('.fi-color-success, [class*="success"]') })
    .first()
    .isVisible({ timeout: 1000 })
    .catch(() => false);
  if (succeeded) {
    return { rejected: false, mechanism: 'native-constraint-validation', detail: 'create succeeded despite the omitted field' };
  }

  const ctl = nativeControlLocator(scope, field.name).first();
  // If the control can't be resolved after submit (form re-rendered under a
  // different id, replaced by a modal, etc.) a bare .evaluate() would hang
  // the whole 30s test timeout — bound it and let the caller record a
  // harness gap instead.
  if (!(await ctl.isVisible({ timeout: 3000 }).catch(() => false))) {
    throw new Error(`HARNESS_CANNOT_ASSERT: native control for '${field.name}' not resolvable after submit`);
  }
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
    return { skipped: 'no create form (button or link) found for this resource', reason: 'no-create-form' };
  }

  const allFields = await extractFieldMeta(scope);
  const requiredFields = allFields.filter((f) => f.required);
  const target = requiredFields.find((f) => f.name === targetFieldName);

  if (!target) {
    return {
      skipped: `'${targetFieldName}' is not rendered as a fillable required field — it's relation-derived, service-computed, tenant-injected, or a repeater/rich-text/file-upload. Whether a NOT-NULL column needs a matching ->required() form rule is FormDbConstraintAuditTest's job (form-field-driven, authoritative); this browser-level check only speaks to fields a user actually fills in.`,
      reason: 'field-not-rendered',
    };
  }

  if (target.readOnly) {
    return {
      skipped: `'${targetFieldName}' is a read-only field driven by another field's afterStateUpdated hook (e.g. slug derived from name) — a user can't type in it or leave it blank, so a browser-level "omit it" test doesn't apply. FormDbConstraintAuditTest already exempts disabled/non-user-editable fields the same way.`,
      reason: 'field-not-rendered',
    };
  }

  for (const field of requiredFields) {
    if (field.name === targetFieldName) continue;
    try {
      await fillValidValue(scope, page, field);
    } catch (error) {
      return {
        skipped: `could not fill sibling required field '${field.name}' with a valid value: ${error.message}`,
        reason: 'unfillable-sibling',
      };
    }
  }

  try {
    return await assertOmissionRejected(scope, page, target);
  } catch (error) {
    // assertOmissionRejected throws only when this generic driver can't
    // operate the form — the submit control isn't locatable, or the field's
    // control isn't resolvable after submit. That's a gap in the driver, not
    // an app defect: record it as a skip rather than redden the suite over
    // test tooling.
    if (String(error.message).startsWith('SUBMIT_NOT_FOUND') || String(error.message).startsWith('HARNESS_CANNOT_ASSERT')) {
      return {
        skipped: `couldn't locate this form's submit control to test the omission (${error.message})`,
        reason: 'harness-cannot-drive',
      };
    }
    throw error;
  }
}

/**
 * Registers `mind-the-gap-again` tests from an EXPLICIT per-resource field
 * list — one `test()` per field named, nothing auto-discovered:
 *
 *   registerRequiredFieldOmissionTests('Payments', {
 *     'company/payments': ['invoice_id'],
 *   });
 *
 * `fieldsByResource` maps `'<panel>/<slug>'` → the user-facing required
 * fields whose omission the browser must reject. Whoever writes the spec
 * decides what belongs — a column that's framework-filled (company_id,
 * user_id), service-computed (invoice_total), relation-derived (customer_id),
 * or otherwise not a thing a user types is simply left off the list, with a
 * one-line comment in the spec saying why. No skips, no KNOWN_GAPS lookup:
 * every entry is a real assertion, and every omission is deliberate and
 * visible in the spec file rather than inferred here.
 *
 * The schema export is still loaded — as a stale-entry guard: a listed field
 * that is no longer a NOT-NULL / no-default column (or a resource key that no
 * longer resolves) fails loudly so the list can't rot.
 */
export function registerRequiredFieldOmissionTests(moduleName, fieldsByResource) {
  let schema;
  try {
    schema = loadSchemaForModule(moduleName);
  } catch (error) {
    // loadSchemaForModule runs at collection time (execSync + JSON.parse).
    // A failure here — DB down, dev container missing, malformed output —
    // must not throw out of this call: these tests share a spec file with
    // the rest of the module's E2E tests, and a collection-time throw takes
    // the whole file's discovery down with it. Register one explicit failing
    // test instead, so the schema problem is loud but contained.
    test(`mind-the-gap-again: ${moduleName} — schema export unavailable`, () => {
      throw new Error(
        `Could not load the form/DB schema for ${moduleName} via `
        + `'php artisan mind-the-gap:export-schema' (see loadSchemaForModule): `
        + error.message
      );
    });

    return;
  }

  for (const [resourceKey, fieldNames] of Object.entries(fieldsByResource)) {
    const resource = schema.resources.find((r) => `${r.panel}/${r.slug}` === resourceKey);

    test.describe(`mind-the-gap-again: ${resourceKey}`, () => {
      if (!resource) {
        test(`resource '${resourceKey}' is still registered`, () => {
          throw new Error(
            `No Filament resource in module ${moduleName} matches '${resourceKey}' — `
            + 'it was renamed, unregistered, or moved panels. Update this spec\'s field map.'
          );
        });

        return;
      }

      const requiredCols = new Set(requiredColumns(resource).map((c) => c.name));

      for (const fieldName of fieldNames) {
        test(`omitting required '${fieldName}' is rejected by the browser`, async ({ page }) => {
          expect(
            requiredCols.has(fieldName),
            `'${fieldName}' is listed for ${resourceKey} but is not a NOT-NULL/no-default column on `
              + `'${resource.table}' — stale list entry: drop it, or fix the form/DB.`
          ).toBe(true);

          const result = await testRequiredFieldOmission(page, resource, fieldName);

          if (result.skipped) {
            throw new Error(
              `Couldn't run the omission test for '${fieldName}' on ${resourceKey}: ${result.skipped}\n`
              + `It's in ${moduleName}'s explicit list — either teach the driver to handle this `
              + 'field, or drop it from the list with a comment on why.'
            );
          }

          expect(result.rejected, `mechanism=${result.mechanism} detail=${result.detail}`).toBe(true);
        });
      }
    });
  }
}
