.PHONY: setup up down rebuild remove ps stop artisan composer-install migrate seed fresh key storage-link fix-permissions

setup:
	@if [ ! -f .env ]; then cp .env.example .env; fi
	docker compose build
	docker compose up -d
	docker compose exec php composer install
	docker compose exec php php artisan key:generate
	docker compose exec php php artisan migrate --seed
	docker compose exec php php artisan storage:link
	docker compose exec php chmod -R 777 storage bootstrap/cache
	echo "Laravel project setup complete."

remove:
	docker compose down --rmi all --volumes --remove-orphans

rebuild:
	docker compose down
	docker compose build --no-cache
	docker compose up -d

up:
	docker compose up -d

down:
	docker compose down

ps:
	docker compose ps

stop:
	docker compose stop

artisan:
	docker compose exec php php artisan

composer-install:
	docker compose exec php composer install

migrate:
	docker compose exec php php artisan migrate

seed:
	docker compose exec php php artisan db:seed

fresh:
	docker compose exec php php artisan migrate:fresh --seed

key:
	docker compose exec php php artisan key:generate

storage-link:
	docker compose exec php php artisan storage:link

fix-permissions:
	docker compose exec php chmod -R 777 storage bootstrap/cache