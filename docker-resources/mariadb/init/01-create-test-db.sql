-- Runs once, on first boot of a fresh `database` volume (mariadb's
-- entrypoint executes everything under /docker-entrypoint-initdb.d/).
-- Provisions a dedicated test database alongside the dev one (MARIADB_DATABASE)
-- so `docker compose run --rm cli vendor/bin/phpunit` works out of the box
-- against real MariaDB, matching CI, with no per-developer .env.testing edits.
CREATE DATABASE IF NOT EXISTS invoiceplane_test;
