FROM php:8.1-fpm

WORKDIR /var/www/html

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

ARG WWWGROUP

RUN apt-get update \
    && apt-get install -y curl libcurl4-openssl-dev git zip libzip-dev unzip htop nano openssl libssl-dev supervisor

RUN docker-php-ext-configure pdo_mysql \
    && docker-php-ext-install pdo_mysql

RUN docker-php-ext-install fileinfo \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN pecl install redis-5.3.7 && docker-php-ext-enable redis

RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

RUN cd /tmp && git clone https://github.com/openswoole/swoole-src.git
RUN cd /tmp/swoole-src \
    && git checkout v4.11.1 \
    && phpize \
    && ./configure --enable-swoole-curl --enable-http2 --enable-mysqlnd \
    && make \
    && make install

RUN touch /usr/local/etc/php/conf.d/openswoole.ini \
    && echo 'extension=openswoole.so' > /usr/local/etc/php/conf.d/openswoole.ini

RUN groupadd --force -g $WWWGROUP leaf
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 leaf

COPY php.ini /usr/local/etc/php/conf.d/leaf.ini
ADD supervisor /etc/supervisor/conf.d

RUN apt-get autoremove -y && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && apt-get clean
