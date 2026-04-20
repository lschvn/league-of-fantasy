# Conventions

## Goal

This file defines the coding and API conventions used in this project.
It focuses on the Laravel backend structure, JSON responses, validation, and naming.

## General Rules

- Keep code simple and explicit.
- Use English for code, class names, method names, and API payloads.
- Write comments only when they add real context.
- Write code comments in lower case.
- Prefer clear service and controller logic over clever shortcuts.
- Keep controllers thin and move business rules into services or models when possible.

## Laravel Structure

- Controllers live in `app/Http/Controllers`.
- Form requests live in `app/Http/Requests`.
- API resources live in `app/Http/Resources`.
- Shared response helpers live in `app/Support`.
- Domain logic should live in `app/Services` when it does not belong directly in a model.

## Controllers

- Controllers should primarily coordinate the request, service calls, and response.
- Do not return raw Eloquent models directly from controllers.
- Use resources for serialized model output.
- Use the base controller response helpers for success and error responses.
- Return consistent HTTP status codes:
  - `200` for successful reads and updates
  - `201` for successful creation
  - `401` for unauthenticated access
  - `403` for forbidden access
  - `404` when a resource does not exist
  - `422` for validation or business-rule errors

## API Response Format

All API responses should follow this structure:

```json
{
  "success": true,
  "message": "resource fetched successfully.",
  "data": {}
}
```

Error responses should follow the same structure:

```json
{
  "success": false,
  "message": "validation failed.",
  "data": {
    "field": [
      "error message"
    ]
  }
}
```

## Requests

- Use form requests for validation instead of inline validators in controllers.
- Keep `authorize()` explicit, even when it returns `true`.
- Define validation rules in arrays.
- Add custom messages when they improve clarity.
- Normalize request input in `prepareForValidation()` when needed.

## Resources

- Use one resource per exposed model when that model is part of the API.
- Keep resource output stable and predictable.
- Include relations only when they are loaded.
- Use ISO strings for dates with `toISOString()` when relevant.
- Do not wrap resources with the default Laravel `data` key outside the shared API response object.

## Models

- Define `$fillable` for mass assignment.
- Define casts through `casts()` when possible.
- Keep relationship methods small and explicit.
- Small domain helpers are acceptable in models when they clearly belong to the entity.

## Routes

- API routes live in `routes/api.php`.
- Use clear plural resource names when possible.
- Protect authenticated routes with `auth:sanctum`.
- Keep route definitions readable and grouped by domain.

## Testing

- Feature tests should validate the real API contract.
- Tests must assert the shared response format when hitting API endpoints.
- When the API contract changes, update tests in the same change set.

## Current Project Conventions

- Use `ApiResponse` for standardized JSON responses.
- Use `JsonResource::withoutWrapping()` to avoid nested Laravel resource wrapping.
- Prefer API messages written in lower case with a trailing period.
- Prefer neutral, descriptive conventional commits such as:
  - `feat(api): add invitation endpoints`
  - `fix(auth): handle invalid credentials`
  - `refactor(api): normalize response handling`
