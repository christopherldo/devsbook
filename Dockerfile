# Use uma imagem base para PHP-FPM
FROM php:7.4.0-fpm-alpine

# Instale dependências e extensões necessárias
RUN set -ex \
  && apk --no-cache add \
    postgresql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
  && docker-php-ext-install gd pdo pdo_pgsql

# Configure parâmetros do PHP
ENV PHP_UPLOAD_MAX_FILESIZE=20M
ENV PHP_POST_MAX_SIZE=25M
RUN echo "upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}" > /usr/local/etc/php/conf.d/uploads.ini \
  && echo "post_max_size = ${PHP_POST_MAX_SIZE}" >> /usr/local/etc/php/conf.d/uploads.ini \
  && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/errors.ini

# Instale o NGINX
RUN apk add --no-cache nginx

# Configure o NGINX
COPY services/nginx/nginx.conf /etc/nginx/nginx.conf
COPY services/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY app /app

# Configure o NGINX para rodar com o PHP-FPM
RUN mkdir -p /run/nginx

# Exponha a porta 8080
EXPOSE 8080

# Comando para iniciar NGINX e PHP-FPM
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
