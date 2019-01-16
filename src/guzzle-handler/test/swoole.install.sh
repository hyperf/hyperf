#!/usr/bin/env bash
wget https://github.com/swoole/swoole-src/archive/v4.2.12.tar.gz -O swoole.tar.gz
mkdir -p swoole
tar -xf swoole.tar.gz -C swoole --strip-components=1
rm swoole.tar.gz
cd swoole
phpize
./configure --enable-async-redis --enable-openssl
make -j$(nproc)
make install
cd -
