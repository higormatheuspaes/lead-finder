FROM serversideup/php:8.4-fpm-nginx

# extensões que o Filament precisa além das que já vêm na imagem
USER root
RUN install-php-extensions intl bcmath

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .

# garante pastas graváveis mesmo quando o código vem do Windows (lá as permissões se perdem no COPY)
RUN mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

USER www-data
RUN composer install --no-dev --optimize-autoloader --no-interaction
