FROM bootjp/apachephp:latest

COPY web/apache2.conf /etc/apache2/apache2.conf
ADD ./ /app/
