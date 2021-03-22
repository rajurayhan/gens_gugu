FROM php:7.2-apache

RUN /bin/cp /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && \
echo "Asia/Tokyo" > /etc/timezone

COPY ./conf/000-default.conf /etc/apache2/sites-available/
RUN ln -s ../mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load

RUN apt-get update
RUN apt-get install -y \
      git \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      libpng-dev \
      zip \
      unzip
RUN docker-php-ext-install gd pdo zip pdo_mysql
RUN pecl install xdebug \
  && docker-php-ext-enable xdebug
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer config -g repositories.packagist composer https://packagist.jp && composer global require hirak/prestissimo

ENV LESSCHARSET=utf-8 

COPY ./php/php-local.ini /usr/local/etc/php/php.ini

RUN curl -sL https://deb.nodesource.com/setup_12.x | bash -
RUN apt-get install -y nodejs