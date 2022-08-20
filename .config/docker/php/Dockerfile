

FROM php:8.1.9-fpm

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev \
        unzip \
		zip \
        telnet;

RUN apt-get update && apt-get install -y libpq-dev procps \
    && pecl install xdebug-3.1.2 \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install sockets bcmath pdo pdo_pgsql pgsql opcache;

RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis;

RUN apt-get install -y libz-dev libmemcached-dev \
    && pecl install memcached \
    && docker-php-ext-enable memcached;

RUN apt-get install -y git

USER nobody