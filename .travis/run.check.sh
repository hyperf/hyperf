#!/usr/bin/env bash

set -e

PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix src --dry-run
composer analyse src
