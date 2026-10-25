# German localization — implementation plan

Goal: ship a usable German InvoicePlane v2 alongside the existing v1 without changing business records, calculations, credentials or encryption keys.

Architecture: reuse Laravel translation catalogues and Filament runtime labels; keep locale selection scoped to each request/document. Preserve English fallback, placeholders, storage formats and stable identifiers. German terminology comes from the installed v1 catalogue, with reviewed translations for new v2 concepts. User-entered text is never automatically translated.

Tech stack (composer.lock): Laravel 13, Filament 5, PHP 8.4, MariaDB. Repository AGENTS.md version summary is older than composer.json.

Specification: the user approved the five-step fork/localization/test/deploy/upstream approach and requested sequential execution plus Trilium documentation at note 8QbPeXIljKXy.

## Global constraints
- Work in the explicitly requested clean checkout on l10n/german; keep develop unchanged.
- Upstream translation contributions use Crowdin. Keep German catalogues in a separate fork commit, and upstream code fixes as focused independent PRs.
- No real outgoing test emails. No production DB in tests. No secrets or business records in git, documentation or PRs.
- Existing v1 stays running. Deploy only the v2 application after backup and verification; retain previous image for rollback.

## Tasks and completion checks
1. Verify fork/baseline and create Trilium pages. Expected: origin TheFreeman, clean baseline 563200e, six linked pages.
2. Inventory catalogues and visible literals; extract v1 terminology; prepare German catalogues. Expected: matching leaf keys, nonempty German values and matching placeholders/markup. Record reused vs new terms.
3. Internationalize navigation/settings, then remaining UI/messages. Add behavior tests where changes have runtime consequences; run failing test before implementation. Expected: locale-aware rendered labels, same identifiers/actions and English fallback.
4. Apply user locale and document formatting where needed. Test German and English, unsupported/missing locale, locale restoration, decimal quantities, negative amounts, null dates and email/PDF output. Expected: test pass and PDF/render inspection with isolated sample data.
5. Build versioned Docker image; back up v2 configuration/database, deploy app only, verify health, record counts, login/session behavior and PDF; record rollback. Prepare small topic PRs to upstream develop with tests and dependencies; attach every created PR.

## Review focus
Stable keys vs translated UI labels; late evaluation in Filament; locale leakage between requests or queued mail; customer vs operator language; exact placeholder/HTML parity; no translation of stored data; date-only fields must not shift by timezone; monetary calculations must remain unchanged; old and new English behavior; no production secrets in build context; source/PR commit provenance and rollback.

## PR sequence
A. Runtime locale preference (small standalone behavior fix).
B. Translatable navigation and settings (source keys + regression tests).
C. Remaining visible text, grouped by domain if diff is large.
D. Document presentation formats and mail/PDF locale (tests + before/after).
German catalogue and deployment/docs are separate fork commits; Crowdin handoff documented. Avoid a giant all-in-one upstream PR. Reference the matching .github/CHECKLIST.md translation rows in PR bodies.
