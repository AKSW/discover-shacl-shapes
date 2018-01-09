FROM php:7.0-apache

RUN apt-get update
RUN apt-get install -y \
    curl git libfreetype6-dev libjpeg62-turbo-dev libpng12-dev net-tools nano make zlib1g-dev

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install zip \
    && docker-php-ext-enable zip

# install composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# fix terminal error
RUN echo "export TERM=xterm" > /etc/bash.bashrc

# configure apache2
COPY ./Docker/apache.conf /etc/apache2/sites-enabled/000-default.conf

COPY ./Docker/run2.sh /run2.sh
RUN chmod +x /run2.sh

RUN mkdir /shsrv
RUN mkdir /shsrv/docker-data

RUN a2enmod rewrite

EXPOSE 80

CMD ["/run2.sh"]
