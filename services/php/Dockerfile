FROM php:7.4.0-fpm-alpine

RUN set -ex \
  && apk --no-cache add \
    postgresql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev  # Adiciona suporte ao WebP

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install gd

RUN docker-php-ext-install pdo pdo_pgsql

ENV PHP_UPLOAD_MAX_FILESIZE=20M
ENV PHP_POST_MAX_SIZE=25M

RUN echo "upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = ${PHP_POST_MAX_SIZE}" >> /usr/local/etc/php/conf.d/uploads.ini
