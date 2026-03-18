include .env


.PHONY: dev prod clean

prod:
	docker compose --profile prod build && \
	docker compose --profile prod up -d

dev:
	cp .env.example .env
	docker compose -f docker-compose.yaml up -d --build
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan config:clear
	echo "app available at http://localhost"

clean:
	docker compose down --remove-orphans --volumes && \
    docker system prune -a
