FROM php:8.3-cli
RUN docker-php-ext-install mysqli
WORKDIR /var/www/html
COPY . /var/www/html/
RUN chmod -R u=rwX,g=rX,o=rX /var/www/html
EXPOSE 80
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html"]
