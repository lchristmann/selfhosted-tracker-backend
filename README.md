# Quokka Tracker Backend <!-- omit in toc -->

## Table of Contents <!-- omit in toc -->

- [Architecture](#architecture)
  - [Database Schema](#database-schema)
  - [API Schema](#api-schema)
- [Development](#development)
  - [Helpful commands](#helpful-commands)
  - [Notes on the database seeding process](#notes-on-the-database-seeding-process)
- [Deployment / Setup](#deployment--setup)
- [Administration](#administration)
- [Maintenance](#maintenance)

This is the backend to the [Quokka Tracker Android App](https://github.com/lchristmann/quokka-tracker-android-app) client.

## Architecture

The architecture of the Quokka Tracker Backend is very simple. There's three containers in the Docker network:

- [Nginx](https://nginx.org/) Server
- [PHP-FPM](https://www.php.net/manual/de/install.fpm.php) running a Laravel API
- [PostgreSQL](https://www.postgresql.org/) database

### Database Schema

![Database schema](docs/db-schema.drawio.svg)

This basic schema is extended by the Laravel framework's tables and the Laravel Sanctum table `personal_access_tokens`, which stores the authentication tokens for the users.

### API Schema

All endpoints are guarded with the `auth:sanctum` authentication middleware, i.e. clients
must always use their personal access token, which identifies them as a specific user.
This enables the `/me` endpoints.

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

**For usage see the [API Documentation](docs/API-DOCUMENTATION.md).**

## Development

The code follows the standard Laravel conventions.

The hardest part is probably the big SQL query at the bottom of the `LocationController.php` class. The [SQL-QUERY-EXPLANATION.md](docs/SQL-QUERY-EXPLANATION.md) document helps you understand it.

For development, we use the `compose.dev.yaml`, which provides a `workspace` container with extra tools.
Consider reading the [dedicated documentation](docs/DOCKER-COMPOSE.md) of this project's Docker Compose setup for further understanding.

```shell
docker compose -f compose.dev.yaml up -d # Start the setup
```

```shell
docker compose -f compose.dev.yaml down # Shut it down
```

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

### Notes on the database seeding process

There are two types of artifacts created:

- `sanctum_tokens.txt`: a file with user information + API tokens of those users (use those for testing)
- `storage/app/*.[svg|png]`: images for the users (with 50% coin-flip chance a user gets an image)

## Deployment / Setup

In production, we use the `compose.prod.yaml`, which only contains the three core containers (no workspace container).

For detailed instructions on how to set up the Quokka Tracker Backend, visit the [Setup Guide](docs/SETUP-GUIDE.md).

## Administration

See the [Administration Guide](docs/ADMIN-GUIDE.md).

## Maintenance

This project is developed and maintained by [Leander Christmann](https://stackoverflow.com/users/20594090/lchristmann). In case of questions, feel free to [write me an email](mailto:hello@lchristmann.com).
