# DEMO ONLY: This Dockerfile builds an intentionally vulnerable PHP app for SQLi demonstration.
FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
COPY . /var/www/app/
RUN cp -r /var/www/app/public/* /var/www/html/ && cp -r /var/www/app/src /var/www/
RUN mkdir -p /var/log/app && chown www-data:www-data /var/log/app

