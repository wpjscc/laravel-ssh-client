ARG PHP_VERSION="wpjscc/php:7.4.7-fpm-alpine"
FROM ${PHP_VERSION}
COPY  . /www

WORKDIR /www

RUN composer install --ignore-platform-reqs --no-dev --no-interaction -o
RUN mkdir -p  /etc/supervisord.d
RUN mv /www/docker/supervisor.conf /etc/supervisord.d/supervisor.conf && rm -rf /var/www/html/docker

ENTRYPOINT ["/bin/sh","-c","chmod -R 777 /www/storage;chmod -R 777 /www/bootstrap;php-fpm -R -D &&supervisord -n"]
