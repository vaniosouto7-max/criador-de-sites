FROM php:8.3-apache
RUN docker-php-ext-install mysqli \
    && a2dismod mpm_event mpm_worker mpm_worker 2>/dev/null || true
RUN a2enmod mpm_prefork rewrite
WORKDIR /var/www/html
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R u=rwX,g=rX,o=rX /var/www/html
ENV APACHE_DOCUMENT_ROOT=/var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
