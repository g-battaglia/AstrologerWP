# Makefile for Astrologer API Playground development.
#
# Docker targets:
#   make up          — Start WordPress + MariaDB (Docker), auto-install WP + activate plugin
#   make down        — Stop and remove Docker containers
#   make logs        — Tail Docker container logs
#   make shell       — Shell into the WordPress container (Docker)
#
# Apple Container targets:
#   make ac-up       — Start WordPress + MariaDB (Apple Containers)
#   make ac-down     — Stop Apple Container instances
#   make ac-logs     — Tail Apple Container WordPress logs
#   make ac-shell    — Shell into WordPress (Apple Containers)
#   make ac-clean    — Stop + remove volumes and network (Apple Containers)
#   make ac-status   — Show Apple Container status
#
# Frontend targets:
#   make build-fe    — Build the frontend for production
#   make dev-fe      — Start Vite dev server
#
# Other targets:
#   make pot         — Regenerate the .pot translation file
#   make zip         — Create a distributable ZIP archive
#   make clean       — Remove build artefacts

.PHONY: up down logs shell build-fe dev-fe pot zip clean \
        ac-up ac-down ac-logs ac-shell ac-clean ac-status

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
# Apple Containers
# ---------------------------------------------------------------------------

ac-up:
	./apple-container.sh up

ac-down:
	./apple-container.sh down

ac-logs:
	./apple-container.sh logs

ac-shell:
	./apple-container.sh shell

ac-clean:
	./apple-container.sh clean

ac-status:
	./apple-container.sh status

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
