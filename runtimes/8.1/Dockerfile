FROM php:8.1.0-fpm-alpine

# install dependencies
RUN apk update && apk add \
        build-base \
        bash \
        openssl \
        git \
        tar \
        curl \
        openssl \
        openssl-dev \
		curl-dev \
		libxml2-dev \
		libpng-dev \
		libzip-dev \
		postgresql-dev \
		sqlite \
		sqlite-dev \
		autoconf \
		zip \
    && docker-php-ext-install \
	    bcmath \
		curl \
		dom \
		gd \
		iconv \
		intl \
		opcache \
		pcntl \
		pdo \
		pdo_mysql \
		pdo_sqlite \
		pdo_pgsql \
		pgsql \
		phar \
		posix \
		session \
		simplexml \
		sockets \
		xml \
		zip
RUN pecl install redis \
	&& docker-php-ext-enable redis

COPY ./docker/php/php.ini $PHP_INI_DIR/php.ini

# create the web user
RUN addgroup -S web && adduser -S web -G web

ENV APP_HOME=/srv/src
WORKDIR $APP_HOME
COPY . $APP_HOME

# chown all the files to the web user
RUN chown -R web:web $APP_HOME/storage

# change to the web user
USER web

EXPOSE 9000