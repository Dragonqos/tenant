FROM php:7.4-fpm
LABEL maintainer="dragonqos@gmail.com"

RUN mkdir ~/.ssh

RUN buildDeps=" \
        default-libmysqlclient-dev \
        libbz2-dev \
        libmemcached-dev \
        libsasl2-dev \
    " \
    runtimeDeps=" \
        curl \
        git \
        zip \
        unzip \
        vim \
        wget \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libjpeg-dev \
        libmemcachedutil2 \
        libpng-dev \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        libevent-dev \
        librabbitmq-dev \
        libcurl4-openssl-dev \
        pkg-config \
        libssl-dev \
        openssh-client \
    " \
    && apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y $buildDeps $runtimeDeps \
    && docker-php-ext-install bcmath bz2 curl json calendar iconv intl mbstring opcache pdo_mysql zip \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install exif \
    && docker-php-ext-enable opcache \
    && pecl install memcached redis \
    && docker-php-ext-enable memcached.so redis.so \
    && apt-get purge -y --auto-remove $buildDeps \
    && rm -r /var/lib/apt/lists/*

RUN ln -sf /dev/stdout /var/log/access.log && ln -sf /dev/stderr /var/log/error.log

#RUN pecl install -o -f redis && rm -rf /tmp/pear && docker-php-ext-enable redis

RUN set -xe \
    && pecl install \
        redis \
        mongodb \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable \
        redis \
        mongodb

## Now we copy will copy very simple php.ini file and change the timezone by ENV variable
COPY php.ini /usr/local/etc/php/

## Install Composer
RUN echo "Install composer globally"
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && ln -s $(composer config --global home) /root/composer
RUN composer self-update --2
ENV PATH=$PATH:/root/composer/vendor/bin COMPOSER_ALLOW_SUPERUSER=1

## Setup TMP folder
RUN mkdir /tmp/multitenant
RUN rm -rf /tmp/multitenant/*
RUN chmod 777 /tmp/multitenant -R

USER www-data

CMD ["php-fpm"]