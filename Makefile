# ============================================================
# AstrologerWP - Development Commands
# ============================================================

.PHONY: help build up setup down clean logs shell rebuild \
        container-up container-down container-clean container-logs container-shell \
        zip

# Default target
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ── Build ────────────────────────────────────────────────────

build: ## Build JS and CSS assets for production
	npm run build

dev: ## Start JS and CSS watchers for development
	npm run dev

install: ## Install Node.js dependencies
	npm install

# ── Docker Compose ───────────────────────────────────────────

up: ## Start WordPress + MariaDB (Docker Compose)
	docker compose up -d

setup: ## Auto-configure WordPress (install, activate plugin, create test pages)
	docker compose exec wordpress setup-wordpress.sh

down: ## Stop containers (keeps data)
	docker compose down

clean: ## Stop containers and delete all volumes
	docker compose down -v

logs: ## Tail WordPress container logs
	docker compose logs -f wordpress

shell: ## Open bash shell in WordPress container
	docker compose exec wordpress bash

rebuild: ## Rebuild WordPress image and restart
	docker compose build --no-cache
	docker compose up -d

restart: ## Restart containers
	docker compose restart

# ── Apple Container (macOS) ──────────────────────────────────

container-up: ## Start test environment (Apple Container)
	./container-run.sh

container-down: ## Stop containers (keeps data)
	./container-stop.sh

container-clean: ## Stop containers and purge all data
	./container-stop.sh --purge

container-logs: ## Tail WordPress container logs
	container logs -f astrologerwp-wp

container-shell: ## Open bash shell in WordPress container
	container exec -it astrologerwp-wp bash

# ── Distribution ─────────────────────────────────────────────

zip: ## Create AstrologerWP.zip for WordPress upload
	./compress_4_wp.sh
