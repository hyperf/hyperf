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
use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;

(function () {
    /** @var null|ClassLoader $classLoader */
    $classLoader = null;
    foreach (spl_autoload_functions() as $loader) {
        if (is_array($loader) && $loader[0] instanceof ClassLoader) {
            $classLoader = $loader[0];
            break;
        }
    }
    if (! $classLoader) {
        return;
    }
    if ($file = $classLoader->findFile(TestCase::class)) {
        $content = file_get_contents($file);
        $replace = 'public function runBare';
        if (strpos($content, $find = 'final ' . $replace) !== false) {
            $content = str_replace($find, $replace, $content);
            file_put_contents($file, $content);
        }
    }
})();
