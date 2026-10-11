FROM serversideup/php:8.4-fpm-nginx

# extensões que o Filament precisa além das que já vêm na imagem
USER root
RUN install-php-extensions intl bcmath
USER www-data

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
RUN composer install --no-dev --optimize-autoloader --no-interaction