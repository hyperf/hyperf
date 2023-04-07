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
namespace Hyperf\Server;

use Hyperf\Engine\Contract\SocketInterface;

class Connection
{
    protected $socket;

    public function __construct(SocketInterface $conn)
    {
        $this->socket = $conn;
    }

    public function recv(float $timeout = 0)
    {
        return $this->socket->recvPacket($timeout);
    }

    public function send(string $data)
    {
        return $this->socket->sendAll($data);
    }

    public function close(): bool
    {
        return $this->socket->close();
    }

    public function exportSocket(): SocketInterface
    {
        return $this->socket;
    }
}
