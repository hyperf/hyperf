#!/usr/bin/env bash
sudo apt-get update
sudo apt-get install libboost-all-dev
wget https://github.com/swoole/yasd/archive/${YASD_VERSION}.tar.gz -O yasd.tar.gz
mkdir -p yasd
tar -xf yasd.tar.gz -C yasd --strip-components=1
rm yasd.tar.gz
cd yasd
phpize
./configure
make -j$(nproc)
sudo make install
sudo sh -c "echo extension=yasd > /etc/php/${PHP_VERSION}/cli/conf.d/yasd.ini"
php --ri yasd
