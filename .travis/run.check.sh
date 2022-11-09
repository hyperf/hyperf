#!/usr/bin/env bash
vendor/bin/php-cs-fixer fix src --dry-run
composer analyse src
