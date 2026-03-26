# Makefile for Astrologer API Playground development.
#
# Targets:
#   make up        — Start WordPress + MariaDB containers
#   make down      — Stop and remove containers
#   make logs      — Tail container logs
#   make shell     — Open a shell inside the WordPress container
#   make build-fe  — Build the frontend for production
#   make dev-fe    — Start Vite dev server (run separately)
#   make pot       — Regenerate the .pot translation file
#   make zip       — Create a distributable ZIP archive
#   make clean     — Remove build artefacts

.PHONY: up down logs shell build-fe dev-fe pot zip clean

# ---------------------------------------------------------------------------
# Docker
# ---------------------------------------------------------------------------

up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f

shell:
	docker compose exec wordpress bash

# ---------------------------------------------------------------------------
# Frontend
# ---------------------------------------------------------------------------

build-fe:
	cd frontend && npm run build

dev-fe:
	cd frontend && npm run dev

# ---------------------------------------------------------------------------
# i18n
# ---------------------------------------------------------------------------

pot:
	mkdir -p languages
	xgettext \
		--language=PHP \
		--keyword=__ --keyword=_e --keyword=_n:1,2 \
		--keyword=_x:1,2c --keyword=_ex:1,2c --keyword=_nx:1,2,4c \
		--keyword=esc_html__ --keyword=esc_html_e \
		--keyword=esc_attr__ --keyword=esc_attr_e \
		--keyword=esc_html_x:1,2c --keyword=esc_attr_x:1,2c \
		--from-code=UTF-8 \
		--default-domain=astrologer-api \
		--package-name="Astrologer API" \
		--package-version="1.0.0" \
		--output=languages/astrologer-api.pot \
		astrologer-api-playground.php \
		$$(find includes -name '*.php') \
		uninstall.php

# ---------------------------------------------------------------------------
# Distribution
# ---------------------------------------------------------------------------

zip: build-fe
	bash compress_4_wp.sh

# ---------------------------------------------------------------------------
# Cleanup
# ---------------------------------------------------------------------------

clean:
	rm -rf frontend/dist
	rm -f astrologer-api-playground.zip
