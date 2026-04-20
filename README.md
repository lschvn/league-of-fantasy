# Fantasy Esport League

This repository currently contains the Laravel application scaffold and the project
design documentation for the planned fantasy esport product.

## Documentation

- Functional requirements: [docs/requirements.md](docs/requirements.md)
- Use cases: [docs/use_cases.md](docs/use_cases.md)
- Domain class diagram: [docs/class_diagram.md](docs/class_diagram.md)
- Conceptual data model: [docs/mcd.md](docs/mcd.md)
- Physical data model: [docs/mpd.md](docs/mpd.md)

The documentation describes the target product model. It is not yet implemented in the
current codebase, which still contains the default Laravel starter structure.

## Development

The local development setup is based on Docker.

Start the project:

```bash
make dev
```

Run the migrations if needed:

```bash
docker compose exec app php artisan migrate
```

## Demo workflow

For the assignment review, use:

```bash
make demo
```

This command:

- installs Composer and Node dependencies through Docker
- starts the application stack
- waits for PostgreSQL
- resets and seeds the database with a realistic fantasy-esport scenario
- exports the OpenAPI document for Swagger UI

After the command completes, the main review URLs are:

- Application: `http://localhost:8080`
- OpenAPI UI: `http://localhost:8080/docs/api`
- OpenAPI JSON: `http://localhost:8080/docs/api.json`
- Swagger UI: `http://localhost:8080/swagger`

Seeded demo credentials:

- `owner@fantasy.test` / `password`
- `bruno@fantasy.test` / `password`
- `chloe@fantasy.test` / `password`
- `diego@fantasy.test` / `password`
- `eve@fantasy.test` / `password`
- `farah@fantasy.test` / `password`

Seeded private invite code:

- `PRIVATE2026`
