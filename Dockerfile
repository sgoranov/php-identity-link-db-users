# syntax=docker/dockerfile:1
FROM ubuntu:22.04

RUN apt-get update && apt-get -y upgrade && DEBIAN_FRONTEND=noninteractive apt-get -y install \
    sudo \
    ssh \
    git \
    nano \
    less \
    net-tools \
    inetutils-ping \
    iproute2 \
    telnet \
    apache2 \
    curl \
    ca-certificates \
    gnupg \
    postgresql-client \
    php \
    php-fpm \
    php-pgsql \
    php-sqlite3 \
    php-xml \
    php-xdebug \
    composer

# Install symfony cli
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash
RUN apt install symfony-cli

# Apache configuration
RUN a2enmod rewrite
RUN a2enmod actions

COPY ./docker/apache.conf /etc/apache2/sites-enabled/000-default.conf

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD apachectl -D FOREGROUND

# Used for debugging purposes only to keep the container up and running
#CMD tail -f /dev/null