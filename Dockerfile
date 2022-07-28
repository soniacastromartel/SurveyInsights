FROM php:7.3.28-apache

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN apt-get update && apt-get install -y libpq-dev

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable mysqli 
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y zlib1g-dev libzip-dev libpng-dev
RUN docker-php-ext-install zip

ENV COMPOSER_MEMORY_LIMIT=-1

RUN apt-get install -y vim  

RUN apt-get install -y wkhtmltopdf  

VOLUME /app
WORKDIR /app

RUN apt-get install -y git