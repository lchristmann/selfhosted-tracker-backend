# Administrator's Guide

## Create a user (incl. an access token)

1. Connect to the server
2. Enter the php-fpm container: `docker exec compose.prod.yaml exec php-fpm bash`.
3. Open Laravel Tinker: `php artisan tinker`.
4. Run the following:
    ```
    $user = User::create(['name' => 'John Doe']);
    $basicToken = $user->createToken('basic-token');
    $basicToken->plainTextToken
    ``` 
5. Hand that token to the user - this will be the only time you see it in plain text.

## Delete a user (incl. his locations and access tokens)

1. Connect to the server.
2. Enter the php-fpm container: `docker exec compose.prod.yaml exec php-fpm bash`.
3. Open Laravel Tinker: `php artisan tinker`.
4. Show the list of users: `User::all()` and note the id of the one to delete.
5. Delete your user of choice: `User::find(<id>)->delete()`. This will also delete his locations and access tokens.
