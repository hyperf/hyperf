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

namespace Hyperf\Socket;

use Hyperf\Protocol\ProtocolPackerInterface;
use Swoole\Coroutine\Socket as CoSocket;

class Socket implements SocketInterface
{
    /**
     * @var CoSocket
     */
    protected $socket;

    /**
     * @var ProtocolPackerInterface
     */
    protected $packer;

    public function __construct(CoSocket $socket, ProtocolPackerInterface $packer)
    {
        $this->socket = $socket;
        $this->packer = $packer;
    }

    public function send($data, float $timeout = -1)
    {
        $string = $this->packer->pack($data);

        return $this->socket->sendAll($string, $timeout);
    }

    public function recv(float $timeout = -1)
    {
        $string = $this->socket->recvAll($this->packer::HEAD_LENGTH, $timeout);
        $len = $this->packer->length($string);

        $count = intval($len / self::RECV_MAX_LENGTH);
        $left = intval($len % self::RECV_MAX_LENGTH);

        for ($i = 0; $i < $count; ++$i) {
            $string .= $this->socket->recvAll(self::RECV_MAX_LENGTH, $timeout);
        }

        if ($left > 0) {
            $string .= $this->socket->recvAll($left, $timeout);
        }

        return $this->packer->unpack($string);
    }
}
