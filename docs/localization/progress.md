# Localization execution ledger
Plan: docs/localization/implementation-plan.md

2026-10-25: Task 1 complete. Fork clean at 563200e, branch l10n/german created, upstream remote configured. Trilium index 8QbPeXIljKXy and pages rNrE8CopMFEa, YAu3jEESJC9B, VASIGj4izicK, 3MBy4MZIhwaM, uMzNj7hicwaA, eyy9HLzyJ6LO created.
Decision: use the user-specified checkout and a dedicated branch; no extra checkout. Existing authorization covers implementing and deploying the agreed scope.
Decision: fork German catalogues separately, upstream code fixes via focused PRs, translation handoff through Crowdin as required by .github/TRANSLATIONS.md.

Task 2: German base catalogues prepared: 1,273 ip keys + 451 auxiliary leaves. 586 overlaps with v1: 471 retained verbatim, 115 adapted to changed meaning/placeholders/formal address. No empty German texts. Ten source English labels are empty and will be addressed separately.
Task 3: Profile locale correction committed as 5ead809. RED: 9 tests failed for missing middleware. GREEN: UserLocaleTest + UserProfileTest = 17 tests, 63 assertions. Persistent Livewire middleware intentionally keeps locale until rendering (a finally reset would undo it before Livewire hydration).

Task 3: Source commits: 9637e35 inferred labels (208 components, 17 resources, 12 relation managers); bbc895d enum/role/permission labels; 2c5feb0 import wizard/designer; 520f7e2 account notification locale; 72c386a lazy user-menu labels. German catalogues remain separate.
Task 4: Integrated localization check initially 63 tests / 198 assertions passed. Review corrected TableWidget heading hooks, textual month names, three raw enum status columns, lazy user-menu labels, public validation attributes, and per-cell settings queries. Request-scoped cache reduced 240 display-setting SELECTs to four in the regression scenario; 29 cache/date/UI tests / 212 assertions passed.
Broad suite with `--exclude-group failing,troubleshooting`: 732 passed, 7 failed, 9 incomplete, 28 skipped. Three failures were missing frontend assets, subsequently built successfully with frozen yarn.lock; one was a randomized customer-language fixture in an English reminder assertion, now explicitly en. Remaining two team-removal failures and one company-switch redirect failure reproduced unchanged on upstream 563200e (3 failed, 16 passed, 76 assertions); they are not localization regressions. Final follow-up verification pending below.
Task 5: Private v2 DB/config/storage/key backup taken before app-only deployment. No migration, access reset, key rotation or email send is part of this localization deployment.
