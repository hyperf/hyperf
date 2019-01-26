<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

go(function () {
    global $argc, $argv;

    if (version_compare('5.6.0', PHP_VERSION, '>')) {
        fwrite(
            STDERR,
            sprintf(
                'This version of PHPUnit is supported on PHP 5.6, PHP 7.0, and PHP 7.1.' . PHP_EOL .
                'You are using PHP %s (%s).' . PHP_EOL,
                PHP_VERSION,
                PHP_BINARY
            )
        );

        die(1);
    }

    if (! ini_get('date.timezone')) {
        ini_set('date.timezone', 'UTC');
    }

    foreach ([__DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php'] as $file) {
        if (file_exists($file)) {
            define('PHPUNIT_COMPOSER_INSTALL', $file);

            break;
        }
    }

    unset($file);

    if (! defined('PHPUNIT_COMPOSER_INSTALL')) {
        fwrite(
            STDERR,
            'You need to set up the project dependencies using Composer:' . PHP_EOL . PHP_EOL .
            '    composer install' . PHP_EOL . PHP_EOL .
            'You can learn all about Composer on https://getcomposer.org/.' . PHP_EOL
        );

        die(1);
    }

    require PHPUNIT_COMPOSER_INSTALL;

    $status = PHPUnit_TextUI_Command::main(false);
    if ($status !== PHPUnit_TextUI_TestRunner::SUCCESS_EXIT) {
        exit($status);
    }

    swoole_event_exit();
});
