#!/usr/bin/env bash
wget https://github.com/swoole/swoole-src/archive/v${SW_VERSION}.tar.gz -O swoole.tar.gz
mkdir -p swoole
tar -xf swoole.tar.gz -C swoole --strip-components=1
rm swoole.tar.gz
cd swoole
phpize
./configure --enable-openssl --enable-mysqlnd
make -j$(nproc)
make install
