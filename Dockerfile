FROM php:7.2-apache

RUN /bin/cp /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && \
echo "Asia/Tokyo" > /etc/timezone

COPY ./.dockerconfig/conf/000-default.conf /etc/apache2/sites-available/
COPY . /var/www/html/
RUN ln -s ../mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load

WORKDIR /var/www/html/

RUN apt-get update
RUN apt-get install -y \
      git \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      libpng-dev \
      zip \
      unzip
RUN docker-php-ext-install gd pdo zip pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer config -g repositories.packagist composer https://packagist.jp && composer global require hirak/prestissimo

RUN composer install --no-dev

ENV LESSCHARSET=utf-8 

COPY ./.dockerconfig/php/php.ini /usr/local/etc/php/

RUN curl -sL https://deb.nodesource.com/setup_12.x | bash -
RUN apt-get install -y nodejs
RUN npm install
RUN npm run prod

RUN chown -R www-data:www-data ./
RUN chmod -R 755 storage