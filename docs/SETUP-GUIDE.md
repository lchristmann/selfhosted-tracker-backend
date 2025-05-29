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
    curl -L https://raw.githubusercontent.com/lchristmann/quokka-tracker-backend/main/docker-compose.yaml -o docker-compose.yaml
    ```
3. Download the `.env.example` and save it as `env` file
    ```shell
    curl -L https://raw.githubusercontent.com/lchristmann/quokka-tracker-backend/main/.env.example -o .env
    ```
4. Edit the `.env` file and set the `APP_URL` to your domain or public IP address
    ```shell
    nano .env
    ```
5. Create the Docker network and start the services
    ```shell
    docker network create quokka-tracker-backend-network
    docker compose up -d
    ```
6. Generate a new `APP_KEY` and replace it in the `.env` file (format `base64:xyz...`)
    ```shell
    docker compose exec php-fpm bash
    php artisan key:generate --show
    exit
    nano .env 
    ```
7. Verify your installation
   1. Visit: http://yourServersIP/health
   2. .Follow the [Admin Guide](ADMIN-GUIDE.md) to create a user, then use their access token
      to make an API request like [GET /me](API-DOCUMENTATION.md#1-get-me--get-usersuser).
      It should return that user's data in JSON format.

## Firewall (Recommended)

| Source          | Protocol | Port | Purpose |
|-----------------|----------|------|---------|
| Any IPv4 / IPv6 | TCP      | 22   | SSH     |
| Any IPv4 / IPv6 | ICMP     | —    | Ping    |
| Any IPv4 / IPv6 | TCP      | 80   | HTTP    |
| Any IPv4 / IPv6 | TCP      | 443  | HTTPS   |

## Setup HTTPS (Optional but Recommended)

>  This guide uses [Nginx Proxy Manager](https://nginxproxymanager.com/) to enable HTTPS via Let's Encrypt for simplicity and ease of use. You can also use other solutions like [Caddy](https://caddyserver.com/), [Traefik](https://traefik.io/traefik/), or manual [Nginx](https://nginx.org/) setup.

Since Let's Encrypt doesn’t issue certificates for IP addresses and browsers don’t trust them, you need a (sub)domain to enable HTTPS.

1. Point your (sub)domain to your server's public IP, e.g.
   ```text
   A quokka-tracker.example.com 192.168.1.1
   ```
2. Edit the `.env` file and set the `APP_URL` to your (sub)domain prefixed with `https://`.
   ```shell
   cd /opt/quokka-tracker-backend
   nano .env
   ```
3. Edit the `docker-compose.yaml` file and comment out the entire ports section. (This ensures the `web` service is only reachable internally in the `quokka-tracker-backend-network` Docker network)
   ```shell
   nano .env
   ```
   ```yaml
   # ports:
     # - "${NGINX_PORT:-80}:80"
   ```
4. Restart the setup with `down` and `up` for the `.env` file change to take effect
   ```shell
   docker compose down
   docker compose up -d
   ```
5. Set up the [Nginx Proxy Manager](https://nginxproxymanager.com/)
   1. Create a folder
      ```yaml
      mkdir /opt/nginx-proxy
      cd /opt/nginx-proxy
      ```
   2. Create a `docker-compose.yaml` file like this (from the [official Quick Setup guide](https://nginxproxymanager.com/guide/#quick-setup))
      ```yaml
      touch docker-compose.yaml
      nano docker-compose.yaml
      ```
      ```yaml
      services:
        app:
          image: 'jc21/nginx-proxy-manager:latest'
          restart: unless-stopped
          ports:
            - '80:80'
            - '81:81'
            - '443:443'
          volumes:
            - ./data:/data
            - ./letsencrypt:/etc/letsencrypt
      ```
      Also paste this, to put the Nginx Proxy Manager into the `quokka-tracker-backend-network` Docker network.
      This is the single point of access to the Quokka Tracker Backend now.
      ```yaml
          # This one level nested under the 'app' service
          networks:
            - quokka-tracker-backend-network

      # This on the root level, not nested at all
      networks:
        quokka-tracker-backend-network:
          external: true
      ```
   3. Bring it up by running
      ```yaml
      docker compose up -d
      ```
   4. Temporarily open port 81 in your firewall (if you configured the firewall as recommended above)
   
      |                    | Protocol | Port | Note                       |
      |--------------------|----------|------|----------------------------|
      | Any IPv4, Any IPv6 | TCP      | 81   | for Nginx Proxy Manager    |
   5. Access the Admin UI at http://yourServersIP:81

      |          | Default credentials |
      |----------|---------------------|
      | Email    | admin@example.com   |
      | Password | changeme            |
       You'll be prompted to change those credentials immediately after logging in.
   6. Find the container name of your Quokka Tracker Backend's `web` service
      ```yaml
      docker ps --format "{{.Image}} {{.Names}}" | grep '^leanderchristmann/quokka-tracker-backend-nginx' | awk '{print $2}'
      # quokka-tracker-backend-web-1 <- usual output
      ```
      > You can also check the name manually by looking at `docker ps` output: it's the rightmost column of the container with the `IMAGE` `leanderchristmann/quokka-tracker-backend-nginx:{VERSION}`.
   7. In the Nginx Proxy Manager visit Dashboard > Proxy Hosts > Add Proxy Hosts:
       - "Details" Tab

         | Setting               | Value                      | Example                        | Explanation                                                                        |
         |-----------------------|----------------------------|--------------------------------|------------------------------------------------------------------------------------|
         | Domain Names          | your domain name           | `quokka-tracker.example.com`   |                                                                                    |
         | Scheme                | http                       |                                | This is only Docker network internal, we'll force HTTPS for internet traffic later |
         | Forward Hostname / IP | the `web` container's name | `quokka-tracker-backend-web-1` | See previous step                                                                  |
         | Forward Port          | 80                         |                                |                                                                                    |
         | Block Common Exploits | yes                        |                                |                                                                                    |

      - "SSL" Tab

        | Setting                                       | Value                         | Explanation                                                            |
        |-----------------------------------------------|-------------------------------|------------------------------------------------------------------------|
        | SSL Certificate                               | Request a new SSL Certificate |                                                                        |
        | Force SSL                                     | yes                           |                                                                        |
        | HTTP/2 Support                                | yes                           | Enables the newer, faster HTTP/2 protocol over TLS                     |
        | HSTS Enabled                                  | yes                           | Adds `Strict-Transport-Security` header to force browsers to use HTTPS |
        | I agree to the Let's Encrypt Terms of Service | yes                           |                                                                        |

      - Click "Save"
   8. To test that the API works, see [Installation Steps](#installation-steps) > 7. (at the start of this guide) 
   9. You can now remove the temporary firewall rule of allowing port 81 again (in case you followed the firewall recommendations).
