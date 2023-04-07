#!/usr/bin/env sh

SW_VER=$(php -r "echo SWOOLE_VERSION_ID;")

if [ $SW_VER -ge 50000 ]; then
  composer require hyperf/engine:^2.8 --dev -W
fi

composer update -oW

