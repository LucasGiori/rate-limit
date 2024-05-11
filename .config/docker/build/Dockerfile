FROM php:8.1.9-alpine

RUN apk update && apk add --no-cache git

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Tornar o Composer executável
RUN chmod +x /usr/local/bin/composer

WORKDIR /app

CMD ["sh"]
ENTRYPOINT ["sh", "-c"]