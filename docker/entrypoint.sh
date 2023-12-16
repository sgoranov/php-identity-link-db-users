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

# Generate client certificate used for testing
mkdir -p var/certificates
cd var/certificates
rm -rf client.* localhost.*

if [[ ! -f /certificates/server.crt && ! -f /certificates/server.key ]]
then
  cd /certificates
  openssl req -x509 -nodes -days 365 -newkey rsa:4096 \
    -subj "/C=BG/ST=Sofia/L=Sofia/O=PHPIdentityLink/CN=www.phpidentitylink.com" \
    -keyout server.key -out server.crt
fi

if [ ! -f /certificates/ca.pem  ]
then
  cd /certificates
  openssl req -new -newkey rsa:4096 -days 365 -nodes -x509 \
    -subj "/C=BG/ST=Sofia/L=Sofia/O=PHPIdentityLink/CN=www.phpidentitylink.com" \
    -keyout ca.key -out ca.crt
  cat ca.crt ca.key > ca.pem

  openssl req -new -newkey rsa:4096 -nodes \
    -subj "/C=BG/ST=Sofia/L=Sofia/O=PHPIdentityLink/CN=www.example.com" \
    -subj "/emailAddress=user@phpidentitylink.com/CN=phpidentitylink.com/O=PHPIdentityLink/OU=IT/C=BG/ST=Sofia/L=Sofia" \
    -out client.csr -keyout client.key

  openssl x509 -req -days 365 -in client.csr -CA ca.crt -CAkey ca.key -set_serial 01 -out client.crt
  openssl pkcs12 -export -inkey client.key -in client.crt -out client.p12 -passout pass:
fi

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
