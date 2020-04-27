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

namespace Hyperf\SocketIOServer\Room;

class MemoryRoom implements RoomInterface
{
    protected $container = [];

    public function add(string $sid)
    {
        $this->container[$sid] = true;
    }

    public function del(string $sid)
    {
        unset($this->container[$sid]);
    }

    public function size(): int
    {
        return count(array_keys($this->container));
    }

    public function list(): array
    {
        return array_keys($this->container);
    }
}
