FROM php:7.4-fpm

RUN docker-php-ext-install mysqli

RUN echo "date.timezone=UTC" > /usr/local/etc/php/conf.d/timezone.ini
