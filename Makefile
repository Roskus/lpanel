#!/bin/bash
# Name of the program
NAME = LPanel
DOCKER_PHP = lpanel_php_1

install:
	composer install
	cp .env.example .env
	php artisan lpanel:install
	php artisan migrate

start:
	docker-compose up -d

# Build and start
build:
	docker-compose build
	docker-compose up -d

ssh: ## Connect into php container
	docker exec -it ${DOCKER_PHP} /bin/bash
