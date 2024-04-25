FROM alpine:3.19.0

WORKDIR /var/www/html/

# install base system
RUN apk update && \
    apk upgrade

RUN apk --no-cache add curl

# install php and extensions
RUN apk add php81 \
    php81-fpm \
    php81-ctype \
    php81-iconv \
    php81-session \
    php81-simplexml \
    php81-tokenizer

RUN apk add php81-xml \
    php81-dom \
    php81-mbstring \
    php81-posix \
    php81-intl \
    php81-pdo \
    php81-opcache \
    php81-phar \
    php81-openssl \
    php81-curl \
    php81-pcntl \
    php81-sockets

# fix for getting php globaly
RUN ln -s /usr/bin/php81 /usr/bin/php

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.6.2

# override PHP-FPM configuration
COPY config/php81 /etc/php81

EXPOSE 9000

CMD ["/usr/sbin/php-fpm81"]
