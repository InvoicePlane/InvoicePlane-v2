# Report Builder Quality Initiative — Progress Record

## Phase 1 — Baseline (VERIFIED, complete)

### Environment verified
- PHP 8.4.27 (cli), Composer, Playwright 1.62.1, Chromium/Firefox/WebKit browsers installed
- MariaDB reachable on 127.0.0.1:3306 (ivplflmnt_db container, healthy)
- App server: `php artisan serve --port=8000` works with `DB_HOST=127.0.0.1 DB_DATABASE=invoiceplane`

### Framework versions (from composer.lock + CLAUDE.md — AGENTS.md is STALE)
- Laravel 13.15.0 (AGENTS.md wrongly says 11)
- Filament 5.6.7 (AGENTS.md wrongly says v4)
- Livewire 4.3.1 (AGENTS.md wrongly says v3)
- nwidart/laravel-modules 13.0.0, PHPUnit 12.5+

### Report Builder implementation (fully mapped)
- 5 bands: header, group_header, details, group_footer, footer (`Modules/Core/Enums/ReportBand.php`)
- 20 bricks across header/detail/footer/utility (`ReportBricksCollection`)
- `ReportTemplateType`: ONLY `invoice` + `quote` — **GAP vs Phase 2 scope** which lists Clients, Invoices, Expenses, Projects, Payments, Tasks, Quotes (7 resources)
- Pure-file storage: manifest.json + bands.json under `storage/app/report_templates/{scope}/{type|company_id}/{slug}/`
- `ReportRenderer::render()` (print) + `renderPreview()` (preview) — share row grid; preview omits document wrapper
- `ReportDataMapper::forInvoice()` / `forQuote()` — lazy-loads only when brick present (query caps)
- Admin panel: super_admin/admin manage system scope (ASSIST excluded). Company panel: elevated + client_admin manage company scope
- `BaseReportBuilderPage`: Mason canvas per band, `moveBrick()` cross-band action, preview via `renderPreviewHtml()`

### Test baseline (ALL GREEN)
| Suite | Command | Result | Time |
|-------|---------|--------|------|
| PHPUnit Report Builder | `php artisan test --filter='Report...Test'` | 156 passed, 634 assertions, 0 failed | 257.73s |
| Playwright Report Builder | `npx playwright test report-builder-*.spec.js` | 22 passed, 0 failed | 52.7s |

### Environment issues encountered & fixed (NOT code changes)
- `storage/framework/testing/disks/report_templates/system` was root-owned (700) from Docker runs → chowned via Docker alpine container
- `test-results/`, `playwright-report/` root-owned → chowned via Docker
- `global-setup.js`: `getByRole('textbox')` matched email+password (strict mode violation) → fixed to `getByRole('textbox', { name: 'Email*', exact: true })` / `Password*` (1-line fix in test infra)

### testrunner (NOT authoritative)
- `./testrunner` and `../testrunner` are gitignored, untracked third-party "Playwright Route Testrunner" (route scanner / test generator)
- NOT part of this repo, NOT the Report Builder test suite. Do NOT use as a second test suite.
- Authoritative Playwright E2E tests live in `Modules/Core/Tests/E2E/`

### Stale instruction files (contradict reality — claim Laravel 11 / Filament 4 / Livewire 3)
- `AGENTS.md` (lines 1-5, 157+)
- `.junie/guidelines.md` (Verified Tech Stack)
- `.junie/architecture.md` (auth/tenant flow — mostly accurate but framework version wrong)
- `.github/copilot-instructions.md` (lines 9-12)
- `CLAUDE.md` is ACCURATE (Laravel 13 / Filament 5 / Livewire 4)

### Key gap to preserve (do NOT silently broaden scope)
Phase 2 lists 7 report resources; implementation supports 2 (invoice, quote). Document as planned/incomplete, separate from implemented behavior.

---

## Prioritized Plan

### P0 — Fix stale instructions (contradicts verified reality; high confusion risk)
1. `AGENTS.md` — correct framework versions (13/5/4), add Report Builder section
2. `.junie/guidelines.md` — correct Verified Tech Stack
3. `.junie/architecture.md` — version note
4. `.github/copilot-instructions.md` — correct versions
5. `CLAUDE.md` — already accurate; add Report Builder guardrails summary

### P1 — Documentation
6. Professionalize `README.md` based on real branch (setup, architecture, testing, Report Builder scope)

### P2 — PHPUnit scrutiny (156 passing; review assertion quality, not count)
7. Audit Report Builder unit/feature tests for weak assertions, redundant coverage, missing edge cases
8. Add regression tests for genuine gaps (preview/export consistency, data context per resource)

### P3 — Mutation testing (PHPUnit)
9. Install/configure Infection (check compatibility with PHP 8.4 + Laravel 13 + PHPUnit 12)
10. Run mutation testing, investigate survivors, report score/exclusions/equivalent mutants

### P4 — Playwright scrutiny + mutation testing
11. Audit selectors/helpers/fixtures; verify against real rendered UI
12. Introduce controlled faults in app, confirm E2E tests detect them (real mutation testing — not just green runs)

### P5 — Shared knowledge & skills (Phase 3/6/7)
13. Save verified findings to shared knowledge location (separate project-specific area)
14. Audit `~/.claude/skills` for duplication/discovery; improve relevant skills

### P3 — Mutation testing finding (VERIFIED constraint)
- Infection `^0.31.2` requires `sebastian/diff ^4||^5||^6||^7`, but PHPUnit 13.3.1 locks `sebastian/diff ^9.0` → **directly incompatible**. Installing Infection would break PHPUnit.
- Decision: perform controlled-fault mutation testing manually (introduce fault in source → run suite → confirm killed/surviving mutants → revert). Valid, honest, dependency-safe.

### P1 — Documentation (pending)
- Professionalize `README.md` based on real branch (setup, architecture, testing, Report Builder scope)

### P3 — PHPUnit Mutation Testing (VERIFIED, complete)
Infection incompatible: `infection/infection ^0.31.2` needs `sebastian/diff ^4||^5||^6||^7`; PHPUnit 13.3.1 locks `sebastian/diff ^9.0` → direct conflict. Installing Infection would break PHPUnit. Approach: controlled-fault manual mutation testing (introduce fault → run suite → confirm killed/surviving → revert).

| # | Mutant (source) | Guard disabled | Test(s) that fail | Status |
|---|-----------------|----------------|-------------------|--------|
| 1 | `ReportBrick::coerceConfigValues` — keep invalid `text_align` | XSS config sanitization | `ReportBrickTest::it_coerces_presentational_config_values`, `ReportBuilderSecurityTest::l1_...text_align is dropped` | KILLED (2 tests) |
| 2 | `ReportTemplateStorage::sanitizeBands` — persist unknown bricks | unknown-brick skip | `ReportTemplateStorageTest::it_skips_unknown_bricks_when_sanitizing_bands` | KILLED (1 test) |
| 3 | `BaseReportBuilderPage::moveBrick` — `false` instead of `!in_array(allowedBands)` | cross-band authorization | `AdminReportBuilderTest::it_refuses_to_move_a_brick_into_a_disallowed_band` | KILLED (1 test) |

All mutants reverted; source tree clean. All 3 guards have real test coverage.

PHPUnit Report Builder baseline: 201 tests (156 Report* + 45 Mason), 1094 assertions, ALL GREEN.

### P4/P6 — Playwright Scrutiny + Mutation Testing (pending)
- Baseline: 22 Report Builder E2E tests pass (52.7s)
- global-setup.js strict-mode bug fixed (getByRole('textbox') matched email+password)
- Still to do: Playwright scrutiny (selectors/helpers/fixtures vs real UI), controlled-fault mutation on app source
