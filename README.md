# php-identity-link-db-users

## Tests


```bash

```

You can execute all tests using the command bellow.

```bash
docker exec -it db-users bash -c "cd /var/www; php bin/phpunit"
```

## Docker image

The application is fully functional and available as a Docker image. To start using it you will have to install [Docker](https://www.docker.com/) and
[Docker compose](https://docs.docker.com/compose/).

The Docker setup relies on [few environment variables](.env.docker.default) used for configuration. Please review
and define these as needed. Refer to [docker compose documentation](https://docs.docker.com/compose/environment-variables/set-environment-variables/)
for more information about environment variables.


