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

namespace Hyperf\SocketIOServer\SidProvider;

use Hyperf\SocketIOServer\SocketIO;

class DistributedSidProvider implements SidProviderInterface
{
    public function getSid(int $fd): string
    {
        return SocketIO::$serverId . '#' . $fd;
    }

    public function isLocal($sid): bool
    {
        return explode('#', $sid)[0] === SocketIO::$serverId;
    }

    public function getFd($sid): int
    {
        return (int) explode('#', $sid)[1];
    }
}
