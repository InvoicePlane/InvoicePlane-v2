DOCKER.md

# Docker Setup for InvoicePlane V2

This guide explains how to run InvoicePlane V2 using Docker.

---

## Prerequisites

- Docker installed (https://www.docker.com/)
- Docker Compose v2+

---

## Quick Start

```bash
git clone https://github.com/InvoicePlane/InvoicePlane.git
cd InvoicePlane

cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed

docker compose up -d

Visit: http://localhost/ivpl

---

Useful Commands

Action	Command

Start services	docker compose up -d
Stop services	docker compose down
View logs	docker compose logs -f
Run artisan	docker compose exec app php artisan
Rebuild containers	docker compose build --no-cache

---

Services

App container: Laravel application

Database: MariaDB (latest)

Mail: MailCatcher (port 1080)

Queue: Redis (optional)

---

Customize Docker

Change database port in docker-compose.yml

Override PHP version via Dockerfile

Add volumes for local persistence if needed

---

Troubleshooting

Port already in use: Adjust ports in docker-compose.yml

Permission issues: Ensure Docker has access to your project folder

Missing .env config: Re-run cp .env.example .env and adjust

---

---

What's Next?

Visit CHECKLIST.md if contributing
