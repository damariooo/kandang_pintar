FROM richarvey/nginx-php-fpm:3.1.6

COPY . /var/www/html

ENV WEBROOT /var/www/html/public
ENV APP_ENV production

RUN composer install --no-dev --optimize-autoloader

RUN chown -R application:application /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80