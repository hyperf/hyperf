#!/usr/bin/env bash

php -m | grep -i swoole

if [ $? -eq 1 ]; then

    # Install PIE
    wget https://github.com/php/pie/releases/download/0.2.0/pie.phar
    chmod +x pie.phar
    mv pie.phar /usr/local/bin/pie

    # Upgrade dependencies
    sudo apt-get clean
    sudo apt-get update
    sudo apt-get upgrade -f
    sudo apt-get install libcurl4-openssl-dev libc-ares-dev libpq-dev

    # Install Swoole
    # wget https://github.com/swoole/swoole-src/archive/${SW_VERSION}.tar.gz -O swoole.tar.gz
    # mkdir -p swoole
    # tar -xf swoole.tar.gz -C swoole --strip-components=1
    # rm swoole.tar.gz
    # cd swoole
    # phpize
    # ./configure --enable-openssl --enable-swoole-curl --enable-cares --enable-swoole-pgsql --enable-brotli
    # make -j$(nproc)
    # sudo make install

    pie install swoole:${SW_VERSION} --enable-openssl --enable-swoole-curl --enable-cares --enable-swoole-pgsql --enable-brotli

    # Add extension to php.ini
    sudo sh -c "echo extension=swoole > /etc/php/${PHP_VERSION}/cli/conf.d/swoole.ini"

    # Print Swoole info
    php --ri swoole

fi

sudo sh -c "echo swoole.use_shortname='Off' >> /etc/php/${PHP_VERSION}/cli/conf.d/swoole.ini"
