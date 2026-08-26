---
name: e2e-behavioral-testing
description: Defines what makes a Playwright E2E test real (not structural theater) and maps PHPUnit UI-behavior coverage to E2E coverage
license: MIT
metadata:
  author: project
---

# Purpose

Prevents E2E tests that pass regardless of whether the feature works — the specific failure
mode found across `Modules/*/Tests/E2E/*.spec.js` on 2026-08-21: 4 of 10 spec files were either
asserting something that isn't a real assertion, or asserting the wrong element entirely, and
none of it was caught because nobody re-ran the exact locator chain against the live app.

This is a browser-testing counterpart to `filament-resource-testing` (which governs
Livewire/PHPUnit-level resource tests). Where that skill owns "does the Livewire component do
the right thing," this skill owns "does the real browser, hitting the real rendered HTML, prove
a human could actually do this."

---

# 1. The Core Rule

**An E2E test must prove the feature works, not that a tag exists.**

`await expect(page.locator('table')).toBeVisible()` on a list page is not an assertion about the
feature — a `<table>` renders whether or not the list has any rows, any seeded data, correct
columns, or correct filtering. It would pass identically whether the resource query is broken,
empty, or wired to the wrong tenant. It only proves Blade rendered *a* table tag.

Same for `await expect(page.locator('form')).toBeVisible()` on a create page — it proves a form
tag exists, not that the form has the right fields, that filling it out and submitting it works,
or even that you're looking at the intended page (see Rule 3).

If an assertion would pass on an empty, broken, or wrong-tenant version of the feature, it is not
a test of that feature.

---

# 2. List Pages Must Assert Seeded Content, Not Table Existence

A list-page test must prove the list actually shows real rows, not an empty shell:

```js
// Wrong — passes even if the table is empty or wired to the wrong query
await expect(page.locator('table')).toBeVisible();

// Right — proves seeded data is actually rendered
await expect(page.getByRole('cell', { name: /INV-2026-00001/ })).toBeVisible();
// or, if the exact seeded value isn't known ahead of time:
await expect(page.locator('table tbody tr')).not.toHaveCount(0);
```

Prefer asserting a specific, known value the seeder produces (an invoice number, a relation
name, a quote reference) over a bare row-count check — a row count only proves *something*
rendered, not that it's the *right* something.

---

# 3. Create/Edit Tests Must Perform the Real Flow and Assert the Real Outcome

A create-page test is not "the form is visible." It is: navigate → fill every required field with
a real value → submit → assert the record actually exists.

```js
test('creating an invoice persists it and shows it in the list', async ({ page }) => {
  await page.goto(tenantPath('/invoices/create'));

  await page.getByLabel(/customer/i).click();
  await page.getByRole('option', { name: KNOWN_RELATION_NAME }).click();
  await page.getByLabel(/invoice date/i).fill('2026-01-01');
  await page.getByLabel(/due date/i).fill('2026-01-31');
  // ...every other required field...

  await page.getByRole('button', { name: /^create$/i }).click();

  // Assert the real outcome — pick at least one:
  await expect(page).toHaveURL(/\/invoices\/\d+\/edit/);         // redirected to the new record
  await expect(page.getByText(/created/i)).toBeVisible();         // success notification
  // and/or navigate back to the list and assert the new row is there
});
```

This mirrors the PHPUnit convention already established in `filament-resource-testing` (Rule 5:
"Tests MUST assert business outcome: database state change, UI state change... NOT framework
internals") — the browser-level equivalent of a database assertion is proving the created record
is now visible/reachable through the UI, not just that the click didn't throw.

---

# 4. Locators Must Be Verified Live, Not Assumed From Reading Blade/Code

Reading the Blade template or the Filament schema definition is not sufficient to know a locator
is correct — two real, confirmed-live bugs this session were invisible from reading code alone:

- `inline-customer-creation.spec.js` used `page.getByLabel(/client/i)`, intending to match the
  "Customer" select. It actually matched an unrelated "Client Reference" text field — a live
  DOM check was the only way to catch this; the code read as reasonable.
- `quotes.spec.js`, `expenses.spec.js`, `invoices.spec.js` used bare `page.locator('form')` on
  create pages. Every one of those pages has **two** `<form>` elements (a hidden topbar logout
  form plus the real one) — Playwright's strict mode throws on `.toBeVisible()` resolving to more
  than one match. This is invisible unless you actually count the elements on the live page.

**Before trusting any locator in a new or edited E2E test, verify it live**: log into the running
app, navigate to the exact route, and confirm the locator resolves to exactly one element, and
that it's the *right* element — not just that the count is 1. A throwaway Playwright/Node script
against the dev app (`chromium.launch()` → login → `page.locator(...).count()` /
`.evaluate(el => el.outerHTML)`) is the standard way to do this; a passing test written without
this check is not trustworthy.

---

# 5. Auth-Loss Guard

Every test in this suite runs pre-authenticated via `global-setup.js`'s saved storage state. A
test that only checks "does *a* table/form exist" can silently false-pass if that session expires
mid-run, because `/login` also has exactly one `<form>` (zero `<table>`, so table-only assertions
happen to catch this by accident — form-only assertions do not). Assert something specific to the
intended page — a heading, a URL match, or content unique to that page — so a redirect to `/login`
fails loudly instead of passing quietly.

---

# 6. PHPUnit → E2E Parity Mapping

Every PHPUnit `#[Test]` method that exercises a **user-facing CRUD or workflow action** through a
Filament resource/page (create, update, delete, list-with-filtering, a named business action like
"send invoice" or "mark as paid") should have a matching Playwright test that performs the
equivalent action through the real browser and rendered HTML — not the Livewire test harness.

This is **not** a literal 1:1 requirement for all ~575 PHPUnit tests. Out of scope for E2E parity:

- Pure model/unit tests (relationships, casts, computed accessors)
- Service/DTO-layer tests with no UI surface
- Validation-rule edge cases already covered by one representative E2E happy-path + the existing
  PHPUnit validation coverage (don't re-litigate every validation message in the browser)
- Multi-tenancy/authorization deny-path tests (owned by `test-gaps`, not this skill — those are
  about proving a guard exists, not about UI behavior)

In scope: for each Filament Resource's Pages (`List*`, `Create*`, `Edit*`, and named Actions), at
minimum one E2E test proving the real create flow (Rule 3) and one proving the list shows real
data (Rule 2). Building this out is large — audit incrementally per-module, matching the
`Modules/<Name>/Tests/E2E/` layout already established, rather than attempting full parity in one
pass.

---

# 7. What This Skill Does NOT Do

- Does not replace `filament-resource-testing` — that skill owns the PHPUnit/Livewire layer.
- Does not mandate deleting or duplicating PHPUnit coverage; E2E tests prove the browser/HTML
  layer works, PHPUnit proves the backend logic works. Both are needed.
- Does not require asserting on framework internals (Livewire wire:model attributes, CSS class
  names) — assert on what a user would see: visible text, accessible names, URLs.
