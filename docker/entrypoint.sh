#!/usr/bin/env bash

# Modify /etc/hosts
DEFAULT_ROUTE=$(ip route show default | awk '/default/ {print $3}')
echo "$DEFAULT_ROUTE localhost.container.com" >> /etc/hosts

cd /var/www/

# Run composer install and replace the correct configuration
# rm -rf vendor
composer install --no-scripts

# Database setup
export PGHOST=database-server
export PGUSER=admin
export PGPASSWORD=admin
until psql -c "\q"; do sleep 3; done
echo "SELECT 'CREATE DATABASE idp' WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'idp')\gexec" \
 | psql -v ON_ERROR_STOP=1

#php bin/console -e dev doctrine:migrations:migrate
#php bin/console -e dev -n doctrine:fixtures:load

cd /var/www/

# PHPUnit setup
#rm -rf var/database.sqlite
#touch var/database.sqlite
#php bin/console -e test doctrine:database:create
#php bin/console -e test doctrine:schema:create
#php bin/console -e test -n doctrine:fixtures:load

# Set correct permissions on var/
rm -rf var/cache/*
chmod -R o+rw var/

# This will exec the CMD from Dockerfile
exec "$@"
