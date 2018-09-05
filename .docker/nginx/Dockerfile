FROM nginx

# Image Labels
LABEL maintainer="El Nino <victor@niculae.net>"

# Some default parameters
ENV LAST_UPDATED 05-06-2018

ENV SERVER_NAME _
ENV PHP_FPM_SOCK 127.0.0.1:9000
ENV PHP_POST_MAX_SIZE 100M
ENV TZ Europe/Bucharest

RUN unlink /etc/localtime
RUN ln -s /usr/share/zoneinfo/${TZ} /etc/localtime

# Nginx user
#RUN addgroup -g 1000 -S www-data \
#    && adduser -u 1000 -D -S -G www-data www-data

RUN usermod -u 1000 www-data && \
    groupmod -g 1000 www-data

# Injecting assets
ADD etc /etc

RUN chmod 400 /etc/nginx/certs/cert.key
RUN chmod 400 /etc/nginx/certs/cloudflare.crt
RUN chmod 400 /etc/nginx/certs/key.pem

# Environment parameters substitution & Nginx start
CMD envsubst '\$SERVER_NAME,\$PHP_FPM_SOCK,\$PHP_POST_MAX_SIZE' < /etc/nginx/conf.d/default.template > /etc/nginx/conf.d/default.conf \
    && nginx -g 'daemon off;'