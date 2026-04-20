-include .env
APP_PORT ?= 8080
PUBLIC_APP_URL ?= http://localhost:$(APP_PORT)
OPENAPI_UI_URL ?= $(PUBLIC_APP_URL)/docs/api
OPENAPI_JSON_URL ?= $(PUBLIC_APP_URL)/docs/api.json
SWAGGER_URL ?= $(PUBLIC_APP_URL)/swagger
DEMO_USER_PASSWORD ?= password
DEMO_PRIVATE_INVITATION_CODE ?= PRIVATE2026
DOCKER_COMPOSE ?= docker compose -f docker-compose.yaml
DOCKER_UID ?= $(shell id -u)
DOCKER_GID ?= $(shell id -g)
DOCKER_USER_ENV = HOST_UID=$(DOCKER_UID) HOST_GID=$(DOCKER_GID)
RUNTIME_SERVICES = app web db

.PHONY: ensure-env dev demo prod docker-up wait-db prepare-filesystem install-deps app-setup seed export-openapi clean nuke

ensure-env:
	@test -f .env || cp .env.example .env

docker-up:
	$(DOCKER_COMPOSE) up -d --build $(RUNTIME_SERVICES)

wait-db:
	@until $(DOCKER_COMPOSE) exec -T db pg_isready -U $(DB_USERNAME) -d $(DB_DATABASE) >/dev/null 2>&1; do \
		printf "Waiting for database...\n"; \
		sleep 1; \
	done

prepare-filesystem:
	$(DOCKER_COMPOSE) exec -T app sh -lc 'mkdir -p /var/www/storage/logs /var/www/bootstrap/cache /var/www/resources/swagger && touch /var/www/storage/logs/laravel.log && chmod -R ug+rwX /var/www/storage /var/www/bootstrap/cache /var/www/resources/swagger'

install-deps:
	$(DOCKER_USER_ENV) $(DOCKER_COMPOSE) run --rm composer install --no-scripts --no-interaction
	$(DOCKER_USER_ENV) $(DOCKER_COMPOSE) run --rm node npm install
	$(DOCKER_USER_ENV) $(DOCKER_COMPOSE) run --rm node npm run build

app-setup:
	$(DOCKER_COMPOSE) exec -T app php artisan package:discover --ansi
	$(DOCKER_COMPOSE) exec -T app php artisan key:generate --force
	$(DOCKER_COMPOSE) exec -T app php artisan config:clear

seed:
	$(DOCKER_COMPOSE) exec -T app php artisan migrate:fresh --seed

export-openapi:
	$(DOCKER_COMPOSE) exec -T app php artisan scramble:export --path=resources/swagger/openapi.json

prod:
	$(DOCKER_COMPOSE) --profile prod build
	$(DOCKER_COMPOSE) --profile prod up -d

dev: ensure-env docker-up wait-db app-setup
	@printf "Application available at %s\n" "$(PUBLIC_APP_URL)"

demo: ensure-env docker-up wait-db prepare-filesystem install-deps app-setup seed export-openapi
	@printf "Application available at %s\n" "$(PUBLIC_APP_URL)"
	@printf "OpenAPI UI available at %s\n" "$(OPENAPI_UI_URL)"
	@printf "OpenAPI JSON available at %s\n" "$(OPENAPI_JSON_URL)"
	@printf "Swagger UI available at %s\n" "$(SWAGGER_URL)"
	@printf "Demo login: owner@fantasy.test / %s\n" "$(DEMO_USER_PASSWORD)"
	@printf "Private league invite code: %s\n" "$(DEMO_PRIVATE_INVITATION_CODE)"

clean:
	$(DOCKER_COMPOSE) down --remove-orphans
	docker system prune -a

nuke:
	$(DOCKER_COMPOSE) down -v --remove-orphans
	docker system prune -a --volumes --force
	rm -rf node_modules/.cache 2>/dev/null || true
