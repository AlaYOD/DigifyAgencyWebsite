.PHONY: up down sh art composer npm test pint stan fresh

up:
	docker compose up -d

down:
	docker compose down

sh:
	docker compose exec app bash

art:
	docker compose exec app php artisan $(c)

composer:
	docker compose exec app composer $(c)

npm:
	docker compose exec app npm $(c)

test:
	docker compose exec app php artisan test

pint:
	docker compose exec app vendor/bin/pint

stan:
	docker compose exec app vendor/bin/phpstan analyse

fresh:
	docker compose exec app php artisan migrate:fresh --seed
