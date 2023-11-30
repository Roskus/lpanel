# Name of the program
NAME = LPanel

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
