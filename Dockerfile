FROM php:8.4-apache

# Install system deps + Node.js
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        build-essential \
        git \
        gphoto2 \
        libimage-exiftool-perl \
        rsync \
        udisks2 \
        python3 \
        ca-certificates \
        curl \
        gnupg \
        unzip \
        libzip-dev \
        libpng-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip

# Apache: enable rewrite, increase limits
RUN a2enmod rewrite \
    && echo "LimitRequestLine 12000" > /etc/apache2/conf-available/photobooth-limits.conf \
    && a2enconf photobooth-limits.conf

# Document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html
WORKDIR /var/www/html

# Copy project
COPY . .

# Install Composer (Photobooth expects bin/composer)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=bin --filename=composer

# Composer install + build (as root, then fix perms)
RUN git config --global --add safe.directory /var/www/html \
    && git submodule update --init \
    && php bin/composer install --ignore-platform-req=ext-ftp --ignore-platform-req=ext-sodium \
    && npm install \
    && npm run build:docker \
    && chown -R www-data:www-data /var/www/html
