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


openssl req -new -newkey rsa:4096 -nodes \
  -subj "/C=BG/ST=Sofia/L=Sofia/O=PHPIdentityLink/CN=www.example.com" \
  -subj "/emailAddress=user@phpidentitylink.com/CN=phpidentitylink.com/O=PHPIdentityLink/OU=IT/C=BG/ST=Sofia/L=Sofia" \
  -out localhost.csr -keyout localhost.key
openssl x509 -req -days 365 -in localhost.csr -CA /certificates/ca.crt -CAkey /certificates/ca.key -set_serial 01 -out localhost.crt

openssl req -new -newkey rsa:4096 -nodes \
  -subj "/C=BG/ST=Sofia/L=Sofia/O=PHPIdentityLink/CN=www.example.com" \
  -subj "/emailAddress=user@phpidentitylink.com/CN=phpidentitylink.com/O=PHPIdentityLink/OU=IT/C=BG/ST=Sofia/L=Sofia" \
  -out client.csr -keyout client.key
openssl x509 -req -days 365 -in client.csr -CA /certificates/ca.crt -CAkey /certificates/ca.key -set_serial 01 -out client.crt
cat client.crt client.key > client.pem
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
