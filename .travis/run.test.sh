#!/usr/bin/env bash

set -e

php -dswoole.use_shortname='Off' bin/co-phpunit src/phar --exclude-group NonCoroutine
php -dswoole.use_shortname='Off' bin/co-phpunit --exclude-group NonCoroutine
php -dswoole.use_shortname='Off' vendor/bin/phpunit --group NonCoroutine
php -dswoole.use_shortname='Off' vendor/bin/phpunit src/filesystem --group NonCoroutine
