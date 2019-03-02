FROM bootjp/apachephp:latest

COPY web/apache2.conf /etc/apache2/apache2.conf
COPY ./ /app/

HEALTHCHECK CMD curl -sS http://localhost:80/__health__ | xargs -I@ test 'OK' = @