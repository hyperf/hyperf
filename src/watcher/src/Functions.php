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

use RuntimeException;

use function passthru;

if (function_exists('exec')) {
    /**
     * @return mixed
     */
    function exec(string $command)
    {
        if (class_exists(\Swoole\Coroutine\System::class)) {
            return \Swoole\Coroutine\System::exec($command);
        }

        if (function_exists('\exec')) {
            \exec($command, $output, $code);
            $output = implode(PHP_EOL, $output);

            return compact('code', 'output');
        }

        if (function_exists('\passthru')) {
            ob_start();
            passthru($command, $code);
            $output = ob_get_clean();
            ob_end_clean();

            return compact('code', 'output');
        }

        throw new RuntimeException('No available function to run command.');
    }
}
