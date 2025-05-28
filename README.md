# Quokka Tracker Backend <!-- omit in toc -->

## Table of Contents <!-- omit in toc -->

- [Architecture](#architecture)
  - [Database Schema](#database-schema)
  - [API Schema](#api-schema)
- [Development](#development)
  - [Helpful commands](#helpful-commands)
  - [Database Seeding](#database-seeding)
- [Deployment](#deployment)
- [Administration](#administration)
- [Maintenance](#maintenance)

This Quokka Tracker Backend powers the [Quokka Tracker Android App](https://github.com/lchristmann/quokka-tracker-android-app). It is a lightweight, containerized Laravel API
for managing user profiles and associated location data.

## Architecture

The backend is composed of three Docker containers:

- [Nginx](https://nginx.org/): Web Server
- [PHP-FPM](https://www.php.net/manual/de/install.fpm.php): Laravel API runtime
- [PostgreSQL](https://www.postgresql.org/): Database

### Database Schema

![Database schema](docs/db-schema.drawio.svg)

In addition to the application-specific tables, Laravel adds its standard tables and there's the Laravel Sanctum table `personal_access_tokens`, which stores the authentication tokens for users.

### API Schema

All endpoints are protected using Laravel Sanctum's `auth:sanctum` middleware.
Clients must include their personal access token in every request.
This ensures secure access and enables `/me`-scoped endpoints for user-specific data.

| Method | Endpoint                | Description                      | Resource |
|--------|-------------------------|----------------------------------|----------|
| GET    | /me                     | fetch **my** profile             | User     |
| PUT    | /me                     | update **all my** profile        | User     |
| PATCH  | /me                     | update **some of my** profile    | User     |
| GET    | /me/image               | fetch **my** profile image       | User     |
| GET    | /me/locations           | fetch **my** locations           | Location |
| POST   | /me/locations           | upload locations **of mine**     | Location |
|        |                         |                                  |          |
| GET    | /users                  | fetch **all** profiles           | User     |
| GET    | /users/{user}           | fetch **a user's** profile       | User     |
| GET    | /users/{user}/image     | fetch **a user's** profile image | User     |
| GET    | /users/{user}/locations | fetch **a user's** locations     | Location |

**See the [API Documentation](docs/API-DOCUMENTATION.md) for detailed usage.**

## Development

This project adheres to standard [Laravel](https://laravel.com/docs/12.x) conventions.
The development environment uses a [Docker Compose setup](docs/DOCKER-COMPOSE.md) defined in `compose.dev.yaml`,
which includes an additional workspace container with helpful CLI tools.

```shell
docker compose -f compose.dev.yaml up -d # Start the setup
```

```shell
docker compose -f compose.dev.yaml down # Shut it down
```

The most complex part of the codebase is a large SQL query in `LocationController.php`. Refer to [SQL-QUERY-EXPLANATION.md](docs/SQL-QUERY-EXPLANATION.md) for an in-depth breakdown.

### Helpful commands

```shell
docker compose -f compose.dev.yaml exec workspace bash
  php artisan migrate # to set up the database structure
  php artisan migrate:fresh --seed
  php artisan tinker
  Location::factory()->count(2)->make()->toJson()
  
docker compose -f compose.dev.yaml exec postgres bash
  psql -d app -U laravel # password: secret
  \dt
  \d tablename
```

### Database Seeding

Seeding the database generates:

- `sanctum_tokens.txt`: Contains test users and their API tokens.
- `storage/app/*.[svg|png]`: User profile images (50% chance per user).

## Deployment

For production, use the minimal `compose.prod.yaml` file (no workspace container). It includes only the essential containers.

Follow the [Setup Guide](docs/SETUP-GUIDE.md) for full deployment instructions.

## Administration

Admin tasks (such as user management) are performed using custom Artisan commands.

See the [Administration Guide](docs/ADMIN-GUIDE.md) for details.

## Maintenance

This project actively maintained by [Leander Christmann](https://github.com/lchristmann).

For questions or support, feel free to [email me](mailto:hello@lchristmann.com).
