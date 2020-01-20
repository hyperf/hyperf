<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Pool;

use Closure;

interface SocketInterface
{
    public function reconnect(): void;

    public function call(Closure $closure);

    public function heartbeat(): void;

    public function isConnected(): bool;

    public function close(): void;
}
