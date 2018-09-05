FROM debian:latest

# Image Labels
LABEL maintainer="El Nino <victor@niculae.net>"

ENV LAST_UPDATED 21-08-2018

# Some default parameters
ENV TZ Europe/Bucharest

# PHP related variables
ENV PHP_ERROR_REPORTING=E_ALL \
    PHP_DISPLAY_ERRORS=0 \
    PHP_OPCACHE_ENABLED=1 \
    PHP_MEM_LIMIT=128M \
    PHP_UPLOAD_MAX_FILESIZE=8M \
    PHP_POST_MAX_SIZE=8M

ENV APP_DIR=/var/www/html \
    COMPOSER_CACHE_DIR=/var/cache/composer \
    COMPOSER_ALLOW_SUPERUSER=1

ENV PATH=${APP_DIR}/bin:${APP_DIR}/vendor/bin:${PATH}

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get -y update && apt-get -y upgrade
RUN apt-get -y --no-install-recommends install locales curl wget ca-certificates apt-transport-https apt-utils vim sudo
RUN apt-get -y install apt-transport-https lsb-release ca-certificates

RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list

RUN apt-get -y update && apt-get -y upgrade
#packages
RUN apt-get install -y dialog curl mcrypt imagemagick memcached git redis-server git telnet zip aptitude locate

RUN apt-get -y install php7.2
RUN apt-get -y install php-cli php-common php-curl php-gd php-json php-mbstring php-mysql php7.2-opcache php-readline php7.2-xml php-zip
RUN apt-get -y install php-sqlite3 php-pgsql php-memcached
RUN apt-get -y install php7.2-fpm php-imagick php-gd php-tidy php-xmlrpc php-intl php-memcache php-redis php-apcu php-apcu-bc php-bcmath


# Install some packages and setup the locales
RUN echo en_US.UTF-8 UTF-8 >> /etc/locale.gen && locale-gen

# Install composer
RUN curl -LsS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer --version \
    && composer global require hirak/prestissimo

# Clean up the mess
RUN apt-get autoclean \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Ensure www-data has access to file from volume if file are mapped as uid/gid 1000/1000
RUN usermod -u 1000 www-data && \
    groupmod -g 1000 www-data

# Injecting assets
ADD etc /etc
ADD usr /usr

# Bootstrap file should be executable
RUN chmod +x /usr/local/bin/bootstrap \
    && ln -s /usr/local/bin/bootstrap /bootstrap # backwards compat

# Working dir
WORKDIR ${APP_DIR}

# Exposed port
EXPOSE 9000

RUN service php7.2-fpm start

# ENTRYPOINT resets CMD
ENTRYPOINT ["bootstrap"]
CMD ["php-fpm7.2", "-F"]