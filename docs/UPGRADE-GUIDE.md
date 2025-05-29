# Upgrade Guide

## Steps

1. Connect to your server via SSH.
2. Update the `leanderchristmann/quokka-tracker-backend` docker image version in the `docker.yaml` file to the current (or some other desired) release:
   ```shell
   cd /opt/quokka-tracker-backend
   nano docker-compose.yaml
   ```
   Example:
   ```yaml
   php-fpm:
     # For the php-fpm service, we will create a custom image to install the necessary PHP extensions and setup proper permissions.
     image: leanderchristmann/quokka-tracker-backend:1.1.0 # increase this version
   ```
3. Restart the setup with `down` and `up`. All data will be kept due to Docker volumes' persistence.
   ```shell
   docker compose down
   docker compose up -d
   ```
