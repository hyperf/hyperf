#!/usr/bin/env bash

basepath=$(cd `dirname $0`; pwd)

echo ../../

if [ ! -f "composer.lock" ]; then
  echo "Not found composer.lock, please composer install first."
  exit
fi

rm -rf runtime/container

echo "Runtime cleared"

php bin/hyperf.php di:init-proxy

echo "Finish!"

