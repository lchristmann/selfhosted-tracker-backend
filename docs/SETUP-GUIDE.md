# Setup Guide

## Requirements

- [Docker](https://docs.docker.com/get-started/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/) installed and operational on your server

## Installation Steps

1. Create a folder
    ```shell
    mkdir /opt/quokka-tracker-backend
    cd /opt/quokka-tracker-backend
    ```
2. Download the `docker-compose.yml`
    ```shell
    curl -L https://raw.githubusercontent.com/lchristmann/quokka-tracker-backend/main/docker-compose.yml -o docker-compose.yml
    ```
3. Download the `.env.example` to a `env` file
    ```shell
   curl -L https://raw.githubusercontent.com/lchristmann/quokka-tracker-backend/main/.env.example -o .env
    ```
4. Edit the `.env` file and set the `APP_URL` to your domain or public IP address
    ```shell
    nano .env
    ```
5. Start the services
    ```shell
    docker compose up -d
    ```
6. Generate a new `APP_KEY` in the `.env` file
    ```shell
    php artisan key:generate
    ```
   In case that doesn't work, you can generate and paste that key manually there with
   `docker compose exec php-fpm bash` and `php artisan key:generate --show`.

To test the success of your installation, you can follow the [admin guide](ADMIN-GUIDE.md) to create a user
and use his access token to make and API request like a [GET /me](API-DOCUMENTATION.md#1-get-me--get-usersuser).
That should return a JSON response body containing that user's information.
