#!/usr/bin/env bash

set -e

php -dswoole.use_shortname='Off' bin/co-phpunit --exclude-group NonCoroutine --log-events-text test.log
php -dswoole.use_shortname='Off' vendor/bin/phpunit --group NonCoroutine
