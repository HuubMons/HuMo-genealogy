FROM php:fpm

RUN apt-get update -y && apt-get install -y libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev \
    libfreetype6-dev
RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev
RUN docker-php-ext-install pdo pdo_mysql

RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-install -j$(nproc) gd

#RUN apk add jpeg-dev libpng-dev \
#    && docker-php-ext-configure gd --with-jpeg \
#    && docker-php-ext-install -j$(nproc) gd

# Set www-data to have UID 1000 (host user)
# RUN usermod -u 1000 www-data;

# content
ADD --chown=www-data:www-data ./ /var/www/html
