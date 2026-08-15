# Docker Setup for InvoicePlane V2

This guide explains how to run InvoicePlane V2 using Docker — both the web
stack and the standalone CLI image for running tests and artisan commands.

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

# Install dependencies and bootstrap the app through the CLI container —
# no PHP required on the host:
docker compose run --rm cli composer install
docker compose run --rm cli php artisan key:generate
docker compose up -d
docker compose run --rm cli php artisan migrate --seed
```

Visit: http://localhost:8080 (override the port with `APP_PORT` in `.env`).

---

## Services

| Service | Image | Purpose |
|---|---|---|
| `web` | `docker-resources/apache` (httpd 2.4 alpine) | Serves `public/`, proxies PHP to `app` |
| `app` | `docker-resources/php-fpm` (PHP 8.4 fpm alpine) | Laravel application (FPM) |
| `cli` | `docker-resources/php-cli` (PHP 8.4 cli alpine) | One-off runner for tests / artisan / composer — profile `tools`, never auto-started |
| `db` | `mariadb` | Database (port 3306) |
| `mailcatcher` | `sj26/mailcatcher` | Catches outgoing mail — UI on port 1080 |

Both PHP images ship the full extension set the app needs: `intl`, `gd`,
`pdo_mysql`, `bcmath`, `zip`, `exif`, `soap`, `redis`. The CLI image also has
Composer, a 1G memory limit for the test suite, and bundled `pdo_sqlite`
(the suite runs on an in-memory sqlite database — no db service needed for
tests).

---

## Running the test suite

```bash
docker compose run --rm cli vendor/bin/phpunit --exclude-group failing,troubleshooting
```

`APP_ENV=testing` is the `cli` service default, so `.env.testing`
(sqlite `:memory:`) is picked up automatically. See `RUNNING_TESTS.md` for
filters, groups, and suites.

### File ownership on Linux

The CLI image creates its user with uid/gid `1000`. If your host user
differs, rebuild with your ids so files written into the mounted repo
(vendor/, storage/, compiled views) stay owned by you:

```bash
docker compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g) cli
```

---

## Useful Commands

| Action | Command |
|---|---|
| Start services | `docker compose up -d` |
| Stop services | `docker compose down` |
| View logs | `docker compose logs -f` |
| Run artisan | `docker compose run --rm cli php artisan <command>` |
| Run composer | `docker compose run --rm cli composer <command>` |
| Rebuild containers | `docker compose build --no-cache` |

---

## Troubleshooting

- **Port already in use**: set `APP_PORT` in `.env` (web) or adjust ports in `docker-compose.yml`
- **Permission issues**: rebuild the `cli` image with your `UID`/`GID` (see above)
- **Missing .env config**: re-run `cp .env.example .env` and adjust
- **Tests fail with `could not find driver` or missing `intl`**: you are running on host PHP — use the `cli` container instead

---

## What's Next?

Visit CHECKLIST.md if contributing
