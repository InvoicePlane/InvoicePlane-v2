# Docker Setup for InvoicePlane V2

This guide explains how to run InvoicePlane V2 against the **`ivpldock`** stack — the actual
Docker environment used for local development on this box. It's a shared stack (not specific to
this repo) that several projects are mounted into; check `docker ps` if in doubt, container names
always start with `ivpldock-`.

> **Note:** this repo also ships its own `docker-compose.yml` (services named `app`/`cli`). That
> stack is **not** what's used for local dev right now — it's unfinished, conflicts with
> `ivpldock` on shared ports (3306, etc.), and will be sorted out at release time. Until then,
> ignore it and use `ivpldock` as documented below.

---

## Prerequisites

- The `ivpldock` stack already running (`docker ps` should show `ivpldock-workspace-1`,
  `ivpldock-mariadb-1`, `ivpldock-nginx-1`, etc.)
- This repo checked out at `/var/www/projects/invoiceplane-2/ivplv2` inside the stack (same path
  on the host, under `/data/Projects/...`)

---

## Services

| Container | Purpose |
|---|---|
| `ivpldock-workspace-1` | Where you run `php`, `composer`, `artisan`, `npm` — shell in here for everything dev-related |
| `ivpldock-nginx-1` | Serves the app — vhost `ip2.test` (port 80) points at this repo's `public/` |
| `ivpldock-php-fpm-1` / `ivpldock-php-worker-1` | PHP-FPM + queue worker |
| `ivpldock-mariadb-1` | Database, reachable inside the network as host `mariadb`, also exposed on host port 3306 |
| `ivpldock-redis-1` | Cache/sessions, host `redis` |
| `ivpldock-beanstalkd-1` / `ivpldock-beanstalkd-console-1` | Queue backend |
| `ivpldock-phpmyadmin-1` | DB admin UI — http://localhost:8081 |

The app is reachable in a browser at **http://ip2.test** (already in `/etc/hosts` → 127.0.0.1).

---

## Running commands

Everything runs via `docker exec` into `ivpldock-workspace-1`:

```bash
docker exec ivpldock-workspace-1 sh -c "cd /var/www/projects/invoiceplane-2/ivplv2 && php artisan migrate"
docker exec ivpldock-workspace-1 sh -c "cd /var/www/projects/invoiceplane-2/ivplv2 && composer install"
docker exec ivpldock-workspace-1 sh -c "cd /var/www/projects/invoiceplane-2/ivplv2 && vendor/bin/pint"
docker exec ivpldock-workspace-1 sh -c "cd /var/www/projects/invoiceplane-2/ivplv2 && vendor/bin/phpstan analyse"
```

---

## Running the test suite

Tests need real MariaDB, not the SQLite default in `.env.testing` — SQLite's lenient identifier
quoting has masked real bugs before that only surfaced against MariaDB in CI. Override the DB
connection with `-e` flags on `docker exec` (env vars passed this way take precedence over
`.env.testing`, so nothing else needs to change).

`ivpldock-workspace-1`'s php.ini has `xdebug.mode=debug` on by default (for IDE step-debugging).
That's dead weight for a plain test run — every request tries and fails to reach a debug client —
so pass `-e XDEBUG_MODE=off` for normal runs; it's a confirmed ~2-3x speedup (roughly 1.2-1.7s/test
instead of 2.5-4s/test). Use `-e XDEBUG_MODE=coverage` instead when you actually need
`--coverage`.

```bash
docker exec -e XDEBUG_MODE=off -e APP_ENV=testing -e DB_CONNECTION=mariadb -e DB_HOST=mariadb -e DB_DATABASE=invoiceplane_test \
  ivpldock-workspace-1 sh -c "cd /var/www/projects/invoiceplane-2/ivplv2 && php artisan test --exclude-group failing,troubleshooting"
```

Use `php artisan test`, not `vendor/bin/phpunit` directly — the two have been observed to behave
differently for this app: a raw `vendor/bin/phpunit` run silently drops some submitted field
values in Livewire form tests. `artisan test` is the proven-reliable path and is what CI uses, so
standardize on it.

**Known issue (see [#689](https://github.com/InvoicePlane/InvoicePlane-v2/issues/689)):** rebuilt
images/environments have, at least once, reproduced a field-dropping bug at scale (100+ false
failures) even under `artisan test`, for reasons not yet isolated. Before trusting a full run after
any environment change, sanity-check it against a small, known test first:

```bash
docker exec -e XDEBUG_MODE=off -e APP_ENV=testing -e DB_CONNECTION=mariadb -e DB_HOST=mariadb -e DB_DATABASE=invoiceplane_test \
  ivpldock-workspace-1 sh -c "cd /var/www/projects/invoiceplane-2/ivplv2 && php artisan test --filter=ContactsTest"
```

All 11 tests / 48 assertions should pass. If any fail with "field is required" errors on data you
know you supplied, don't trust the rest of that run — see the linked issue.

---

## Running E2E (Playwright) tests

```bash
cd /data/Projects/invoiceplane-2/ivplv2
CI=true APP_URL=http://ip2.test npx playwright test
```

`ip2.test` is the vhost that actually points at this repo's `public/` — don't use `ivplv2.test`,
that vhost on this box points at an unrelated checkout.

---

## Troubleshooting

- **Wrong app loads in the browser**: double check you're hitting `ip2.test`, not `ivplv2.test`.
- **Tests fail with `could not find driver` or missing `intl`**: you're running on host PHP —
  always run through `ivpldock-workspace-1`.
- **Container not found**: run `docker ps` and confirm the `ivpldock` stack is actually up.

---

## What's Next?

Visit CHECKLIST.md if contributing
