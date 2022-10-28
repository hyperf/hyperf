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
namespace Hyperf\Watcher;

if (function_exists('exec')) {
    /**
     * @return mixed
     */
    function exec(string $command)
    {
        if (class_exists(\Swoole\Coroutine\System::class)) {
            return \Swoole\Coroutine\System::exec($command);
        }

        \exec($command, $output, $code);

        $output = implode(PHP_EOL, $output);

        return compact('code', 'output');
    }
}
