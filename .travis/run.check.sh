#!/usr/bin/env bash

set -e

PHP_VER=$(php -r "echo PHP_VERSION_ID;")

if [ $PHP_VER -ge 80100 ]; then
  PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix src --dry-run
fi
composer analyse src
