# Localization verification

2026-10-25, PHP 8.4 / MariaDB 11.4 isolated test database / locked Composer packages.

- Focused final integration: 89 passed / 447 assertions (catalogues, labels, locale middleware, menus, widgets, status columns, options, tenant-aware date/number formatting, PDF generation, public quote pages/validation and mail content).
- Frontend: frozen yarn.lock installation and production Vite build passed on Node 22.
- Catalogues: 17 PHP groups, 2,050 source/German leaf strings plus 22 German JSON messages (2,072 total); exact key/placeholder/markup/plural parity, no empty translation values. See catalogue-verification.json.
- Independent read-only review: all seven important and one minor findings corrected. Display setting lookups were reduced from 240 to at most four for repeated cells, without cross-tenant or cross-request caching.
- Broad run: 732 passed / 7 failed / 9 incomplete / 28 skipped. Three missing-asset failures were resolved by building assets. Reminder fixture now explicitly selects English instead of a random customer language. Three remaining failures reproduce unchanged at upstream 563200e: two CompanyUsersTest removal cases and UserProfileTest company switching, all tagged flaky upstream. Baseline check: 16 passed / same 3 failed.
- New default templates localize only at creation; existing/custom subjects and bodies stay unchanged. Render checks do not send emails.
- PHP Pint and git diff whitespace validation passed. Final project-default suite and live deployment checks are recorded in progress.md once complete.
