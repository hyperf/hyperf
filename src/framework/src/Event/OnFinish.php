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

namespace Hyperf\Framework\Event;

use Swoole\Server;

class OnFinish
{
    public function __construct(public Server $server, public int $taskId, public mixed $data)
    {
    }
}
