FROM php:7.1-apache

EXPOSE 80

RUN docker-php-ext-install mbstring pdo pdo_mysql  \
  && a2enmod rewrite expires headers

COPY ./apache2.conf /etc/apache2/apache2.conf
ADD ./ /webapp/

CMD ["apache2-foreground"]
