FROM php:8.1.0-fpm-alpine

# install dependencies
RUN apk update && apk add \
        autoconf \
        build-base \
        bash \
        tar \
        curl \
        curl-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV APP_HOME=/srv/src
WORKDIR $APP_HOME