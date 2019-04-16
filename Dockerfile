FROM php:7.3.4-apache-stretch

ENV STA_APACHE_CONF_FILE_NAME=apache.conf \
    STA_DEST_DIR=/var/www/html
ENV STA_DEST_DIR_SITE=$STA_DEST_DIR
ENV STA_DEST_DIR_SITE_PUBLIC=$STA_DEST_DIR_SITE/public

ARG STA_TEMPORARY_COMPOSER_FILE=$STA_DEST_DIR_SITE/composer.phar

RUN DEBIAN_FRONTEND=noninteractive apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        nano \
        unzip \
        && \
    rm -rfv "$STA_DEST_DIR_SITE" && \
    mkdir -p "$STA_DEST_DIR_SITE" && \
    cd "$STA_DEST_DIR_SITE" && \
    curl -sS -o "$STA_TEMPORARY_COMPOSER_FILE" https://getcomposer.org/download/1.6.4/composer.phar

COPY docker/composer.for-cache-propouses.json "$STA_DEST_DIR_SITE/composer.json"
COPY docker/composer.for-cache-propouses.lock "$STA_DEST_DIR_SITE/composer.lock"
COPY src/MoveInterface.php "$STA_DEST_DIR_SITE/src/"
RUN cd "$STA_DEST_DIR_SITE" && \
    php composer.phar install --no-dev --no-progress --optimize-autoloader

COPY composer.json composer.lock "$STA_DEST_DIR_SITE/"
RUN cd "$STA_DEST_DIR_SITE" && \
    php composer.phar install --no-dev --no-progress --optimize-autoloader && \
    DEBIAN_FRONTEND=noninteractive apt-get purge -y unzip && \
    DEBIAN_FRONTEND=noninteractive apt-get autoremove -y && \
    DEBIAN_FRONTEND=noninteractive apt-get autoclean -y && \
    DEBIAN_FRONTEND=noninteractive apt-get clean -y

COPY ./ "$STA_DEST_DIR_SITE"

RUN echo "\nexport STA_DEST_DIR_SITE_PUBLIC=$STA_DEST_DIR_SITE_PUBLIC\n" >> "$APACHE_ENVVARS" && \
    mv "$STA_DEST_DIR_SITE/docker/$STA_APACHE_CONF_FILE_NAME" "$APACHE_CONFDIR/sites-available" && \
    a2dissite 000-default && \
    a2ensite $STA_APACHE_CONF_FILE_NAME && \
    a2enmod rewrite

COPY ./docker/sta-entrypoint.sh /usr/local/bin/sta-entrypoint.sh

ENTRYPOINT ["sta-entrypoint.sh"]
