FROM php:8.1.9-alpine

RUN apk update && apk add --no-cache git $PHPIZE_DEPS \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && apk add --no-cache libzip-dev zlib-dev libmemcached-dev \
    && pecl install xdebug redis memcached\
    && docker-php-ext-enable xdebug redis memcached \
    && pecl clear-cache \
    && apk del .build-deps

RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Tornar o Composer executável
RUN chmod +x /usr/local/bin/composer

WORKDIR /app

CMD ["sh"]
ENTRYPOINT ["sh", "-c"]