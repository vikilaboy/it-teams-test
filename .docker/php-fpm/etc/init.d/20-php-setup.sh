#!/bin/bash

# tweak php ini file
PHP_INI_FILE="/etc/php/7.2/fpm/php.ini"
PHP_FPM_FILE="/etc/php/7.2/fpm/php-fpm.conf"
PHP_INI_DIR="/etc/php/7.2/fpm"

# Timezone set
if [ ! -z "${TZ-}" ]; then
    echo "date.timezone = \"${TZ}\"" > ${PHP_INI_DIR}/conf.d/date_timezone.ini
fi

# Which PHP errors are reported.
echo "error_reporting = ${PHP_ERROR_REPORTING}" > ${PHP_INI_DIR}/conf.d/error_reporting.ini

# Display PHP error's or not
if [[ "${PHP_DISPLAY_ERRORS-}" != "1" ]] ; then
    echo "display_errors = Off" > ${PHP_INI_DIR}/conf.d/display_errors.ini
    echo "display_startup_errors = Off" > ${PHP_INI_DIR}/conf.d/display_startup_errors.ini
    echo "track_errors = Off" > ${PHP_INI_DIR}/conf.d/track_errors.ini
    echo "xmlrpc_errors = 0" > ${PHP_INI_DIR}/conf.d/xmlrpc_errors.ini

    sed -i -e "s/;log_level\s*=\s*debug/;log_level = notice/g" ${PHP_FPM_FILE}
else
    echo "display_errors = On" > ${PHP_INI_DIR}/conf.d/display_errors.ini
    echo "display_startup_errors = On" > ${PHP_INI_DIR}/conf.d/display_startup_errors.ini
    echo "track_errors = On" > ${PHP_INI_DIR}/conf.d/track_errors.ini
    echo "xmlrpc_errors = 1" > ${PHP_INI_DIR}/conf.d/xmlrpc_errors.ini

    sed -i -e "s/;log_level\s*=\s*notice/log_level = warning/g" ${PHP_FPM_FILE}
fi

# Increase the memory_limit
if [ ! -z "${PHP_MEM_LIMIT-}" ]; then
    echo "memory_limit = ${PHP_MEM_LIMIT}" > ${PHP_INI_DIR}/conf.d/memory_limit.ini
fi

# Increase the post_max_size
if [ ! -z "${PHP_POST_MAX_SIZE-}" ]; then
    echo "post_max_size = ${PHP_POST_MAX_SIZE}" > ${PHP_INI_DIR}/conf.d/post_max_size.ini
fi

# Increase the upload_max_filesize
if [ ! -z "${PHP_UPLOAD_MAX_FILESIZE-}" ]; then
    echo "upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}" > ${PHP_INI_DIR}/conf.d/upload_max_filesize.ini
fi

# Provides real PATH_INFO/ PATH_TRANSLATED support for CGI.
echo "cgi.fix_pathinfo = 0" > ${PHP_INI_DIR}/conf.d/cgi.fix_pathinfo.ini

# A bit of performance tuning.
echo "realpath_cache_size = 128k" > ${PHP_INI_DIR}/conf.d/realpath_cache_size.ini

# OpCache tuning
echo "opcache.enable = ${PHP_OPCACHE_ENABLED}" > ${PHP_INI_DIR}/conf.d/opcache.enable.ini
echo "opcache.validate_timestamps = 0" > ${PHP_INI_DIR}/conf.d/opcache.validate_timestamps.ini
echo "opcache.interned_strings_buffer = 16" > ${PHP_INI_DIR}/conf.d/opcache.interned_strings_buffer.ini
echo "opcache.revalidate_freq = 2" > ${PHP_INI_DIR}/conf.d/opcache.revalidate_freq.ini
echo "opcache.enable_cli = 1" > ${PHP_INI_DIR}/conf.d/opcache.enable_cli.ini
echo "opcache.max_accelerated_files = 7963" > ${PHP_INI_DIR}/conf.d/opcache.max_accelerated_files.ini
echo "opcache.memory_consumption = 192" > ${PHP_INI_DIR}/conf.d/opcache.memory_consumption.ini
echo "opcache.fast_shutdown = 1" > ${PHP_INI_DIR}/conf.d/opcache.fast_shutdown.ini

# PHP-FPM
sed -i -e "s/;daemonize = yes/daemonize = no/g" ${PHP_FPM_FILE}
sed -i -e "s/error_log\s=\s.*$/error_log = \/proc\/self\/fd\/2/g" ${PHP_FPM_FILE}

# Own my files
chown -R www-data:www-data /var/www/html

# Response
echo -en "\033[1;38;5;203m[PHP] Tweak PHP configuration.\033[m\n"