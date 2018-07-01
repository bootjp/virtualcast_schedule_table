FROM bootjp/apachephp:latest

COPY ./apache2.conf /etc/apache2/apache2.conf
ADD ./ /webapp/
