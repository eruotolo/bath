FROM composer:2 AS composer

FROM php:8.5-fpm-alpine3.21

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Update the system and install necessary dependencies
RUN apk update && apk add --no-cache \
$PHPIZE_DEPS \
mariadb-dev \
libpng-dev \
libjpeg-turbo-dev \
freetype-dev \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install mysqli gd

# Set the working directory
WORKDIR /var/www/html

# Expose the port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]