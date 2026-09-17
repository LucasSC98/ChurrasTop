FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

COPY public/ /var/www/html/
COPY config/ /var/www/config/
COPY includes/ /var/www/includes/
COPY functions/ /var/www/functions/
COPY pages/ /var/www/html/pages/
COPY assets/ /var/www/html/assets/

RUN printf 'ServerName churrastop.local\n<Directory /var/www/html>\n    Options -Indexes\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/churrastop-security.conf \
    && a2enconf churrastop-security \
    && chown -R www-data:www-data /var/www
