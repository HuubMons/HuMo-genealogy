FROM php:fpm
RUN docker-php-ext-install pdo pdo_mysql
# content
ADD --chown=www-data:www-data ./ /var/www/html
