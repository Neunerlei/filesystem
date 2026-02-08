FROM neunerlei/php-nginx:8.5

COPY --from=index.docker.io/library/composer:latest /usr/bin/composer /usr/bin/composer

RUN chown -R www-data:www-data "/var/www"
USER www-data
RUN composer global config --no-interaction allow-plugins.neunerlei/dbg-global true \
    && composer global require neunerlei/dbg-global
USER root
