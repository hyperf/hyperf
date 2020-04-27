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

class NullAdapter implements AdapterInterface
{
    public function add(string $sid, string ...$rooms)
    {
    }

    public function del(string $sid, string ...$rooms)
    {
    }

    public function broadcast($packet, $opts)
    {
    }

    public function clients(string ...$rooms): array
    {
        return [];
    }

    public function clientRooms(string $sid): array
    {
        return [];
    }
}
