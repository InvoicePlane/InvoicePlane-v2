# German fork: deployment

Production build: `docker build -f docker/production/Dockerfile --build-arg SOURCE_REVISION=$(git rev-parse HEAD) -t local/invoiceplane-v2:<commit> .`

`compose.production.yml` is a self-contained two-service example for a fresh environment. Put database settings DB_USER, DB_PASSWORD, DB_ROOT_PASSWORD, INVOICEPLANE_IMAGE, SOURCE_REVISION, BIND_ADDRESS and APP_PORT in a private Compose environment file; put application configuration in private runtime.env. Use APP_LOCALE=de and APP_FALLBACK_LOCALE=en. These files must not be committed. The two-service example explicitly uses QUEUE_CONNECTION=sync; a separate queue worker is required before switching to database or redis. The default bind address is loopback; choose the intended LAN/proxy address explicitly.

For the existing v2 installation, build from `git archive` into a new build directory, back up its database and configuration, change only its existing Compose app image, and recreate only the app service with `docker compose up -d --no-deps app`. Keep its environment, APP_KEY, database, uploaded files and keys unchanged. Do not initialize or import again. Clear compiled views, then verify health, record counts, login, PDF output and stored mail configuration without sending a message.

Rollback: restore the previous app image in the existing Compose file and recreate only app. This localization change contains no database migrations. Keep the backup private. The Docker recipe and operational documentation are fork-specific, separate from upstream localization PRs.
