FROM dunglas/frankenphp as base
RUN docker-php-ext-install pdo pdo_mysql
RUN echo "session.save_path=/app/data/sessions" >> $PHP_INI_DIR/conf.d/php_session.ini
COPY . /app

FROM base AS php-cli

RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/composer-setup.php \
    && composer --version

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> $PHP_INI_DIR/conf.d/xdebug.ini \
    && echo "xdebug.force_display_errors=1" >> $PHP_INI_DIR/conf.d/xdebug.ini \
    && echo "xdebug.extended_info=1" >> $PHP_INI_DIR/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> $PHP_INI_DIR/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9001" >> $PHP_INI_DIR/conf.d/xdebug.ini \
    && echo "xdebug.discover_client_host=0" >> $PHP_INI_DIR/conf.d/xdebug.ini \

ENTRYPOINT ["php"]
CMD ["-v"]