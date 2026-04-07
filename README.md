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
