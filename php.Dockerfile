FROM php:fpm

RUN docker-php-ext-install mysqli

RUN pecl install xdebug && docker-php-ext-enable xdebug