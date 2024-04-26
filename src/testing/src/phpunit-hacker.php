<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
foreach ([
    __DIR__ . '/../../../vendor/phpunit/phpunit/src/Framework/TestCase.php', // In hyperf/hyperf dir.
    __DIR__ . '/../../../phpunit/phpunit/src/Framework/TestCase.php', // In project dir.
] as $target) {
    if (! file_exists($target)) {
        continue;
    }

    $content = file_get_contents($target);
    $find = 'final public function runBare';
    $replace = 'public function runBare';

    if (empty($content) || strpos($content, $find) === false) {
        return;
    }

    $content = str_replace($find, $replace, $content);
    file_put_contents($target, $content);
}
