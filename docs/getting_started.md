# Getting started

## Docker Compose
A minimal docker-compose environment is prepared where tests can be run in isolation in an environment that, 
for us, kind of represents a production environment.

To start the container simply run:

`docker-compose up -d`

Verified to work on a Linux host machine running Ubuntu 20.04. With these software versions:

```
$ docker --version
Docker version 20.10.7, build 20.10.7-0ubuntu5~20.04.2

$ docker-compose --version
docker-compose version 1.28.4, build cabd5cfb
```

## Setting the .env
Either use the provided .env.dist file (from the webroot: `cp .env.dist .env`) or create one of your own. What 
environment variables can be used in the application can be found in the `.env.dist` file.

## Installing dependencies
When the container is up, you should be able to install the composer dependencies. You can 'enter' the
 guest with the `docker-compose exec php-fpm sh` command. 
 
Installing the composer dependencies is as simple as running:

```bash
composer install
```

Running composer install will also prepare the autoloader, build bootstrap files and clear and warm up the development cache.

## Testing the API in a dev environment
Docker-compose does not configure a web server to run the API on. You can use the Symfony built in web server. 
From your host run: `bin/console server:start`. Or do the same from the guest, but ensure you configure your host files
accordingly and use the command `bin/console server:start 0.0.0.0:8000` on the guest.
