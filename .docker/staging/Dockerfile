FROM php:7.4-apache

RUN apt update && apt install -y \
        build-essential \
        vim \
        nano \
        nodejs \
        mlocate \
        tree \
        npm \
        libssl-dev \
        libpng-dev \
        libxpm-dev \
        libjpeg-dev \
        libfreetype6-dev \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        libonig-dev \
        zip \
        curl \
        unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install zip mysqli soap pdo_mysql mbstring exif pcntl bcmath gd && \
    docker-php-ext-enable mysqli && docker-php-ext-configure intl && \
    docker-php-ext-install intl && \
    docker-php-ext-enable intl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-configure calendar && docker-php-ext-install calendar

COPY .docker/.conf/default.conf /etc/apache2/sites-enabled/000-default.conf

COPY .docker/.conf/php.ini /usr/local/etc/php/php.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html && a2enmod rewrite

RUN composer install --ignore-platform-reqs

# RUN cp .env.example .env

RUN mkdir -p /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views \
   && chown -R www-data:www-data /var/www/html/storage/ \
   && chmod -R ug+rwX /var/www/html/storage/

RUN chmod -R 755 /var/www/html
RUN chmod -R o+w storage

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]

EXPOSE 80
