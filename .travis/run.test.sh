#!/usr/bin/env bash
composer analyse src
php -dswoole.use_shortname='Off' bin/co-phpunit --exclude-group NonCoroutine
php -dswoole.use_shortname='Off' vendor/bin/phpunit --group NonCoroutine
php -dswoole.use_shortname='Off' vendor/bin/phpunit src/filesystem --group NonCoroutine
vendor/bin/php-cs-fixer fix src --dry-run
