# Administrator's Guide

1. Connect to the server via SSH.
2. Enter the php-fpm container:
   ```shell
   docker compose exec php-fpm bash
   ```

## Create a user (incl. an access token)

3. Run the artisan command `user:create`, e.g.:
   ```shell
   php artisan user:create "Josh Miller
   ```
4. Hand that token to the user or store it somewhere safe - this will be the only time you see it in plain text.

## List and search for users

3. Run the artisan command `user:list`, optionally with `--name` option, which filters the name column to contain that string (case-insensitive), e.g.:
   ```shell
   php artisan user:list --name "ramon m"
   ```

## Delete a user (incl. his locations and image)

3. Run the artisan command `user:delete`, e.g.:
   ```shell
   php artisan user:delete 2 "Octavia Cassin"
   ```
