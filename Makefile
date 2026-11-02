#!/bin/bash
# Name of the program
NAME = LPanel
DOCKER_PHP = lpanel-php-1
SSL_PATH = infrastructure/etc/nginx/ssl
SSL_DOMAIN = localhost
SSL_DAYS = 825

.DEFAULT_GOAL := help
.PHONY: help env install up build ssh cert

help: ## List the available commands
	@grep -E '^[a-zA-Z_-]+:.*## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*## "}; {printf "  %-10s %s\n", $$1, $$2}'

env: ## Create .env, asks before overwriting an existing one
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo ".env created from .env.example"; \
	else \
		printf ".env already exists. Overwrite it? A copy is saved in .env.backup [y/N] "; \
		read -r answer || true; \
		if [ "$$answer" = "y" ] || [ "$$answer" = "Y" ]; then \
			cp .env .env.backup; \
			cp .env.example .env; \
			echo ".env overwritten, previous version in .env.backup"; \
		else \
			echo "Keeping current .env"; \
		fi; \
	fi

install: env ## Install the application
	composer install
	grep -q '^APP_KEY=base64:' .env || php artisan key:generate
	docker compose exec php php artisan migrate
	docker compose exec php php artisan panel:install

up: ## Start and run the application
	docker compose up -d

build: ## Build and start
	docker compose build
	docker compose up -d

ssh: ## Connect into php container
	docker exec -it ${DOCKER_PHP} /bin/bash

cert: ## Generate a self-signed SSL certificate
	mkdir -p $(SSL_PATH)/certs $(SSL_PATH)/private
	openssl req -x509 -newkey rsa:2048 -nodes -sha256 -days $(SSL_DAYS) \
		-keyout $(SSL_PATH)/private/$(SSL_DOMAIN).key \
		-out $(SSL_PATH)/certs/$(SSL_DOMAIN).crt \
		-subj "/CN=$(SSL_DOMAIN)" \
		-addext "subjectAltName=DNS:$(SSL_DOMAIN),DNS:*.$(SSL_DOMAIN),IP:127.0.0.1"
	chmod 600 $(SSL_PATH)/private/$(SSL_DOMAIN).key
