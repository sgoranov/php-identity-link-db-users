# php-identity-link-db-users

## Generate certificates

The certification authority (usually called CA) is a digital certificate that certifies the owner of a public key.
You can use the command bellow to generate self-signed passwordless certificate.

```bash
openssl req -new -newkey rsa:4096 -days 365 -nodes -x509 \
  -subj "/C=BG/ST=Sofia/L=Sofia/O=PHPIdentityLink/CN=www.phpidentitylink.com" \
  -keyout ca.key -out ca.crt
cat ca.crt ca.key > ca.pem
```

We create a certificate signing request (called a CSR) from the client key. Applying the CA digital certificate to the 
CSR will create a unique certificate for the client key signed by the CA.

```bash
openssl req -new -newkey rsa:4096 -nodes \
  -subj "/C=BG/ST=Sofia/L=Sofia/O=PHPIdentityLink/CN=www.example.com" \
  -subj "/emailAddress=user@phpidentitylink.com/CN=phpidentitylink.com/O=PHPIdentityLink/OU=IT/C=BG/ST=Sofia/L=Sofia" \
  -out client.csr -keyout client.key
```

Signe the key client by the CA

```bash
openssl x509 -req -days 365 -in client.csr -CA ca.crt -CAkey ca.key -set_serial 01 -out client.crt
```

Export the client key to a PKCS12 certificate, which can be imported into the browser for test purposes.

```bash
openssl pkcs12 -export -inkey client.key -in client.crt -out client.p12 -passout pass:
```

Generate server certificate (HTTPS support)
```bash
openssl req -x509 -nodes -days 365 -newkey rsa:4096 \
  -subj "/C=BG/ST=Sofia/L=Sofia/O=PHPIdentityLink/CN=www.phpidentitylink.com" \
  -keyout server.key -out server.crt
```

## Docker image

The application is fully functional and available as a Docker image. To start using it you will have to install [Docker](https://www.docker.com/) and
[Docker compose](https://docs.docker.com/compose/).

The Docker setup relies on [few environment variables](.env.docker.default) used for configuration. Please review
and define these as needed. Refer to [docker compose documentation](https://docs.docker.com/compose/environment-variables/set-environment-variables/)
for more information about environment variables.


