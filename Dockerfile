FROM php:5.6-fpm

RUN docker-php-ext-install mysqli

RUN echo "date.timezone=UTC" > /usr/local/etc/php/conf.d/timezone.ini
