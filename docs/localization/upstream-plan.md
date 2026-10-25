# Maintainer review sequence

The upstream translation guide requires Crowdin. German catalogues and Docker operations therefore stay in this fork. The source changes are split by observable behavior. No dependency updates, schema migrations, financial calculations, credential changes or custom user content belong in the source PRs.

| Order | Package | Review state | Dependencies |
| --- | --- | --- | --- |
| 1 | Profile locale across panels, persistent Livewire middleware, account notifications and lazy user menus | Submit first, ready for review | None; translation tests inject sentinel strings |
| 2 | Translatable enum, role and permission labels with stable backing values | Submit as independent draft | None; English source catalogues only |
| 3 | Navigation/settings, language/country choices, widget headings and inferred form/resource labels | Keep in fork until first feedback; split navigation from mechanical labels | Locale package for live profile behavior |
| 4 | Import wizard and report-designer strings | Keep in fork; designer is existing incomplete upstream functionality | English source catalogue additions only |
| 5 | Date/number/currency display, request-scoped settings cache | Keep in fork for focused performance/tenant review | UI component label changes can be separated mechanically |
| 6 | Recipient-language PDFs, public quotes, queued mail and localized creation defaults | Keep in fork for document/mail review | Display settings cache; preserves existing custom templates |
| Fork | German catalogues, v1 glossary, Docker build and deployment documentation | Integration branch l10n/german | All reviewed source changes |

Only two initial PRs are published, one ready and one draft. Subsequent packages remain available as individual commits on the fork instead of creating a queue of simultaneous large reviews. Each PR references relevant .github/CHECKLIST.md rows, states tested behavior and excludes German catalogue files. Do not auto-merge. Keep later packages queued until upstream review or integration establishes the preferred approach.

## Translation handoff

The German catalogue contains complete application keys and Laravel account/error JSON messages, with v1 terminology where meaning and placeholders match. It is usable in this fork immediately. Crowdin submission has not been performed: source-key changes should be agreed upstream before submitting the corresponding translations through the project's translation workflow.

## Existing limitations retained

The report-designer Blade view refers to absent upstream service/page classes; translating it does not make the unfinished feature runnable. Existing PDF layout/logo filesystem restrictions are separate from localization. Existing team-removal/company-switch tests fail on the unchanged baseline as well; test evidence is recorded in progress.md. No baseline failures are silently marked as passing.
