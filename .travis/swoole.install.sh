#!/usr/bin/env bash

php -m | grep -i swoole

if [ $? -eq 1 ]; then

    sudo apt-get clean
    sudo apt-get update
    sudo apt-get upgrade -f
    sudo apt-get install libcurl4-openssl-dev libc-ares-dev libpq-dev
    wget https://github.com/swoole/swoole-src/archive/${SW_VERSION}.tar.gz -O swoole.tar.gz
    mkdir -p swoole
    tar -xf swoole.tar.gz -C swoole --strip-components=1
    rm swoole.tar.gz
    cd swoole
    phpize
    ./configure --enable-openssl --enable-swoole-curl --enable-cares --enable-swoole-pgsql --enable-brotli
    make -j$(nproc)
    sudo make install
    sudo sh -c "echo extension=swoole > /etc/php/${PHP_VERSION}/cli/conf.d/swoole.ini"

fi

sudo sh -c "echo swoole.use_shortname='Off' >> /etc/php/${PHP_VERSION}/cli/conf.d/swoole.ini"
