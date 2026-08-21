# Docker Setup for InvoicePlane V2

This guide explains how to run InvoicePlane V2 using Docker.

---

## Prerequisites

- Docker installed (https://www.docker.com/)
- Docker Compose v2+

---

## Quick Start

```bash
git clone https://github.com/InvoicePlane/InvoicePlane-v2.git
cd InvoicePlane-v2

cp .env.example .env

# Install dependencies and bootstrap the app — no PHP required on the host:
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose up -d
docker compose run --rm app php artisan migrate --seed
```

Visit: http://localhost:8080 (override the port with `APP_PORT` in `.env`).

---

## Services

| Service | Image | Purpose |
|---|---|---|
| `web` | `docker-resources/apache` (httpd 2.4 alpine) | Serves `public/`, proxies PHP to `app` |
| `app` | `docker-resources/php-fpm` (PHP 8.4 fpm alpine) | Laravel application (FPM) — also used to run artisan/composer/tests |
| `db` | `mariadb` | Database (port 3306) |
| `mailcatcher` | `sj26/mailcatcher` | Catches outgoing mail — UI on port 1080 |

The PHP image ships the full extension set the app needs: `intl`, `gd`,
`pdo_mysql`, `bcmath`, `zip`, `exif`, `soap`, `redis`, plus Composer.

---

## Running the test suite

```bash
docker compose run --rm -e APP_ENV=testing -e DB_DATABASE=invoiceplane_test app \
  php artisan test --exclude-group failing,troubleshooting
```

Use `php artisan test`, not `vendor/bin/phpunit` directly — the two have been observed to behave
differently for this app: a raw `vendor/bin/phpunit` run silently drops some submitted field
values in Livewire form tests. `artisan test` is the proven-reliable path and is what CI uses, so
standardize on it.

**Known issue (see [#689](https://github.com/InvoicePlane/InvoicePlane-v2/issues/689)):** a
freshly-`docker compose build`'t `app` image has, at least once, reproduced this same
field-dropping bug at scale (100+ false failures) even under `artisan test`, for reasons not yet
isolated — despite extension/ini parity with a known-good image. Before trusting a full local run
from a rebuilt image, sanity-check it against a small, known test first, e.g.:
```bash
docker compose run --rm -e APP_ENV=testing -e DB_DATABASE=invoiceplane_test app \
  php artisan test --filter=ContactsTest
```
All 11 assertions should pass. If any fail with "field is required" errors on data you know you
supplied, don't trust the rest of that run — see the linked issue.

The `-e` overrides above point the run at the dedicated `invoiceplane_test` database on the same
`db` service, so tests never touch your dev data. This intentionally does not fall back to
SQLite: SQLite's lenient identifier quoting has masked real bugs before that only surfaced against
MariaDB in CI.

### File ownership on Linux

The image creates its user with uid/gid `1000`. If your host user differs, rebuild with your ids
so files written into the mounted repo (vendor/, storage/, compiled views) stay owned by you:

```bash
docker compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g) app
```

---

## Useful Commands

| Action | Command |
|---|---|
| Start services | `docker compose up -d` |
| Stop services | `docker compose down` |
| View logs | `docker compose logs -f` |
| Run artisan | `docker compose exec app php artisan <command>` |
| Run composer | `docker compose exec app composer <command>` |
| Rebuild containers | `docker compose build --no-cache` |

---

## Troubleshooting

- **Port already in use**: set `APP_PORT` in `.env` (web) or adjust ports in `docker-compose.yml`
- **Permission issues**: rebuild the `app` image with your `UID`/`GID` (see above)
- **Missing .env config**: re-run `cp .env.example .env` and adjust
- **Tests fail with `could not find driver` or missing `intl`**: you are running on host PHP — use the `app` container instead

---

## What's Next?

Visit CHECKLIST.md if contributing
