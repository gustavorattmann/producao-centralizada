FROM php:7.4.13-apache-buster

LABEL maintainer="Gustavo Rattmann <gustavo_rattmann@hotmail.com.br>"

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=America/Sao_Paulo

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone && \
    apt update && \
    apt -y install \
        gnupg2 && \
    apt update && \
    apt dist-upgrade -y && \
    apt -y install \
            apt-utils \
            bash-completion \
            curl \
            default-mysql-client \
            g++ \
            git \
            htop \
            libcurl3-dev \
            libcurl4-openssl-dev \
            libonig-dev \
            libpcre3-dev \
            libpq-dev \
            libssl-dev \
            libxml2-dev \
            libzip-dev \
            mariadb-client \
            openssh-client \
            openssl \
            nano \
            unzip \
            vim \
            wget \
            zlib1g-dev \
        --no-install-recommends && \
        apt autoremove -y && \
        apt clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN a2enmod rewrite

RUN docker-php-ext-configure bcmath && \
    docker-php-ext-install \
        bcmath \
        ctype \
        curl \
        fileinfo \
        gettext \
        json \
        mbstring \
        mysqli \
        pdo \
        pdo_mysql \
        session \
        sockets \
        tokenizer \
        xml \
        zip

RUN pecl install phalcon psr swoole redis \
    && docker-php-ext-enable phalcon psr swoole redis

RUN if command -v a2enmod >/dev/null 2>&1; then \
        a2enmod rewrite headers \
    ;fi

RUN echo "date.timezone = America/Sao_Paulo" > /usr/local/etc/php/conf.d/timezone.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer && \
    composer clear-cache

WORKDIR /var/www/html

COPY . .

USER www-data