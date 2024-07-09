FROM php:8.3.9-zts-alpine3.20

RUN apk update && apk upgrade && apk add curl-dev openssl-dev git openssh zip unzip $PHPIZE_DEPS --update linux-headers

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN pecl install xdebug-3.3.2 && docker-php-ext-enable xdebug
RUN pecl install mongodb && docker-php-ext-enable mongodb
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_autostart = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_log = /var/www/api/logs/xdeubg.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

ARG UID=1000
ARG GID=1000
RUN addgroup --system --gid $GID lolai
RUN adduser --system --uid $UID --ingroup lolai lolai
USER lolai

WORKDIR /var/www/api
EXPOSE 8080
ENTRYPOINT ["php", "build.local.php"]
