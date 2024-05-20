FROM php:8.3.6-fpm-alpine

# Installation des dépendances système
RUN apk add -U --no-cache pcre-dev $PHPIZE_DEPS \
    curl bash openssl ncurses coreutils python3 libgcc linux-headers grep util-linux binutils findutils \
    vim git icu-dev libmcrypt-dev libpq libzip-dev libzip icu-libs libmcrypt libmcrypt libintl \
    rabbitmq-c rabbitmq-c-dev \
  && apk del pcre-dev

RUN pecl install -f amqp
RUN docker-php-ext-enable amqp


# Installation de composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer