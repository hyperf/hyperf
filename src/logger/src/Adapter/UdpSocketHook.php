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
namespace Monolog\Handler\SyslogUdp;

    function is_resource(mixed $value): bool
    {
        if ($value instanceof \Swoole\Coroutine\Socket) {
            return true;
        }

        return false;
    }
