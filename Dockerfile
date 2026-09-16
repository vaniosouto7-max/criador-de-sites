FROM php:8.3-apache
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf \
    && docker-php-ext-install mysqli \
    && a2enmod mpm_prefork rewrite
WORKDIR /var/www/html
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R u=rwX,g=rX,o=rX /var/www/html
ENV APACHE_DOCUMENT_ROOT=/var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
