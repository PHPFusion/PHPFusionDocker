FROM php:8.1-apache-buster

ARG DEBIAN_FRONTEND=noninteractive

# Update
RUN apt-get -y update --fix-missing && \
    apt-get upgrade -y && \
    apt-get --no-install-recommends install -y apt-utils && \
    rm -rf /var/lib/apt/lists/*

# Install useful tools and install important libaries
RUN apt-get -y update && \
    apt-get -y --no-install-recommends install nano wget \
libfreetype6-dev \
libjpeg62-turbo-dev \
libpng-dev \
dialog \
libsqlite3-dev \
libsqlite3-0 && \
    apt-get -y --no-install-recommends install default-mysql-client \
zlib1g-dev \
libzip-dev \
libicu-dev && \
    apt-get -y --no-install-recommends install --fix-missing apt-utils \
build-essential \
git \
curl \
libonig-dev && \
    apt-get -y --no-install-recommends install --fix-missing libcurl4 \
libcurl4-openssl-dev \
zip \
openssl && \
    rm -rf /var/lib/apt/lists/* && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

## Install xdebug - (Need a PHP version >= 7.0.0 and < 8.0.0)
#RUN pecl install xdebug-2.8.0 && \
#    docker-php-ext-enable xdebug

## Install redis
#RUN pecl install redis-5.1.1 && \
#    docker-php-ext-enable redis

# Load other PHP Extensions
RUN docker-php-ext-install pdo_mysql \
&& docker-php-ext-install pdo_sqlite \
&& docker-php-ext-install mysqli \
&& docker-php-ext-install curl \
&& docker-php-ext-install exif \
&& docker-php-ext-install zip \
&& docker-php-ext-install mbstring \
&& docker-php-ext-install opcache \
&& docker-php-ext-install -j$(nproc) gd

# These don't work or we do not use them
#RUN docker-php-ext-install tokenizer
#RUN docker-php-ext-install json
#RUN docker-php-ext-install gettext \

# Install imagick
#RUN apt-get update && \
#    apt-get -y --no-install-recommends install --fix-missing libmagickwand-dev && \
#    rm -rf /var/lib/apt/lists/* && \
#    pecl install imagick && \
#    docker-php-ext-enable imagick

# Enable apache modules
RUN a2enmod rewrite headers

# Cleanup
RUN rm -rf /usr/src/*



