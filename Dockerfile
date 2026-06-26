FROM php:8.2-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite headers

WORKDIR /var/www/html

COPY . .

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

RUN mkdir -p public/uploads/propiedades \
    && chown -R www-data:www-data public/uploads \
    && chmod -R 775 public/uploads

COPY docker/render-start.sh /usr/local/bin/render-start.sh
RUN chmod +x /usr/local/bin/render-start.sh

CMD ["/usr/local/bin/render-start.sh"]
