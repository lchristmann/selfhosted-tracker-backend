# Tracker Backend <!-- omit in toc -->

> The self-hosted setup is incredibly easy - just follow the instructions in the [setup guide](docs/SETUP-GUIDE.md)
> and the tiny [upgrade guide](docs/UPGRADE-GUIDE.md).

> As a Tracker Administrator, consider the [admin guide](docs/ADMIN-GUIDE.md).

I built this API intending to use it in an upgraded version of my Geolocation Tracking Android application called [Tracker](https://github.com/lchristmann/Tracker).

So far I did not do the upgrade though. The plan is to give it a beautiful UI using React Native + Tailwind
and turn the location tracking core code (written in Kotlin) into a [Native Module](https://reactnative.dev/docs/turbo-native-modules-introduction)
because the native Android APIs for guaranteed background work and location tracking
are a lot more reliable than what React Native itself offers.

Feel free to build your own mobile apps though and just take this API Backend.

It's pretty powerful, even supporting timezones (when a day starts and ends, depends on the timezone you're in, so that's important).

## Table of Contents <!-- omit in toc -->

- [Architecture](#architecture)
  - [Database Schema](#database-schema)
  - [API Schema](#api-schema)
- [Development](#development)
  - [Helpful commands](#helpful-commands)
  - [Database Seeding](#database-seeding)
- [Release](#release)
  - [Test the new release](#test-the-new-release)
  - [Make the new release available to the public](#make-the-new-release-available-to-the-public)
  - [Release the Nginx Image (rarely needed)](#release-the-nginx-image-rarely-needed)
- [Maintenance](#maintenance)

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

If you want to test out the production environment locally, use the minimal `compose.prod.yaml` file (no workspace container).
It mirrors the official `compose.yaml` file for easy self-hosting, but uses the local code instead of the release on Docker Hub.

The most complex part of the codebase is a large SQL query in `LocationController.php`. Refer to [SQL-QUERY-EXPLANATION.md](docs/SQL-QUERY-EXPLANATION.md) for an in-depth breakdown.

### Helpful commands

```shell
docker compose -f compose.dev.yaml exec workspace bash
  composer install # run this on first checkout
  php artisan key:generate --show # paste this on first checkout to .env and restart the setup
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

## Release

The application code must be containerized as shown within the `compose.prod.yaml` `web` service
and published to the Docker Hub repository [leanderchristmann/tracker-backend](https://hub.docker.com/repository/docker/leanderchristmann/tracker-backend/general).

```shell
docker login
```

Set a version that you want to release:

```shell
VERSION=1.0.0
```

Then build, tag and push the docker image:

```shell
docker build \
  -f ./docker/common/php-fpm/Dockerfile \
  --target production \
  -t leanderchristmann/tracker-backend:${VERSION} \
  -t leanderchristmann/tracker-backend:latest \
  .
```

```shell
docker push leanderchristmann/tracker-backend:${VERSION}
docker push leanderchristmann/tracker-backend:latest
```

Now commit your changed code.

Also tag the Git release: 

```shell
git tag -a "${VERSION}" -m "Release ${VERSION}"
git push origin "${VERSION}"
```

Finally, [create a GitHub release](https://github.com/lchristmann/selfhosted-tracker-backend/releases) via the GitHub UI -
it's takes the Git tag and lets you add some meta-information to it.
Give a title like `2.1.0`, a heading like `## What's Changed` and put a bullet point list of changes.

### Test the new release

In the docker-compose.yml **on your server**, bump up the version to what you've just released.

```yaml
php-fpm:
    image: leanderchristmann/tracker-backend:2.0.0 # increase this
```

### Make the new release available to the public

Make this same adaptation (see previous section [Test the new release](#test-the-new-release)) to the `docker-compose.yaml` **in this repository** and push it (simple git commit).
Now everybody following the setup guide, gets the new version of the Tracker Backend.

```yaml
php-fpm:
    image: leanderchristmann/tracker-backend:2.0.0 # increase this
```

### Release the Nginx Image (rarely needed)

```shell
VERSION=1.0.0 # Set a version that you want to release
```

```shell
docker build \
  -f ./docker/production/nginx/Dockerfile \
  -t leanderchristmann/tracker-backend-nginx:${VERSION} \
  -t leanderchristmann/tracker-backend-nginx:latest \
  .
```

```shell
docker push leanderchristmann/tracker-backend-nginx:${VERSION}
docker push leanderchristmann/tracker-backend-nginx:latest
```

Then put that version (`leanderchristmann/tracker-backend-nginx:X.X.X`) in the `docker-compose.yaml` file.

## Maintenance

This project actively maintained by [Leander Christmann](https://github.com/lchristmann).

For questions or support, feel free to [email me](mailto:hello@lchristmann.com).
