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

        ob_start();
        passthru($command, $code);
        $output = ob_get_contents();
        ob_end_clean();

        return compact('code', 'output');
    }
}
