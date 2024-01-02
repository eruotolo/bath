FROM php:8.1-fpm-alpine3.18

# Update the system and install necessary dependencies
RUN apk update && apk add --no-cache \
$PHPIZE_DEPS \
mariadb-dev \
&& docker-php-ext-install mysqli

# Set the working directory
WORKDIR /var/www/html

# Expose the port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]