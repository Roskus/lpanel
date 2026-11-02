FROM php:8.5-fpm
RUN apt-get update && apt-get install -y \
        libicu-dev vim redis-tools git libxml2-dev wget zip unzip mc htop nano \
        nginx supervisor sudo
RUN apt-get update -y && apt-get install -y libwebp-dev libjpeg-dev libjpeg62-turbo-dev libpng-dev libxpm-dev \
    libfreetype6-dev zlib1g-dev libzip-dev \
    && docker-php-ext-install \
        mysqli pdo pdo_mysql \
    && docker-php-ext-install \
        soap zip
RUN pecl install redis && docker-php-ext-enable redis
RUN docker-php-ext-configure gd --with-jpeg --with-webp
RUN docker-php-ext-install gd
ADD ./conf.d/  /usr/local/etc/php/conf.d/
# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Download wp-cli
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x wp-cli.phar
RUN mv wp-cli.phar /usr/local/bin/wp
# Same uid as the host user, php-fpm writes the virtual hosts in the mounted folders
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
RUN chown -R www-data:www-data /var/www
# Nginx and php-fpm run together like in a real server
RUN rm -f /etc/nginx/sites-enabled/default
RUN mkdir -p /var/run/php
COPY ./zz-lpanel.conf /usr/local/etc/php-fpm.d/zz-lpanel.conf
COPY ./supervisord.conf /etc/supervisor/supervisord.conf
COPY --chmod=440 ./sudoers /etc/sudoers.d/lpanel
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
#WORKDIR .
#RUN composer install
