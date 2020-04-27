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

namespace Hyperf\SocketIOServer\Emitter;

use Hyperf\SocketIOServer\SocketIO;
use Hyperf\WebSocketServer\Sender;
use Swoole\Coroutine\Channel;

class Future
{
    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var int
     */
    private $fd;

    /**
     * @var string
     */
    private $data;

    /**
     * @var int
     */
    private $flag;

    /**
     * @var int
     */
    private $opcode;

    /**
     * @var SocketIO
     */
    private $socketIO;

    public function __construct(SocketIO $socketIO, Sender $sender, int $fd, string $data, int $opcode, int $flag)
    {
        $this->socketIO = $socketIO;
        $this->sender = $sender;
        $this->fd = $fd;
        $this->data = $data;
        $this->opcode = $opcode;
        $this->flag = $flag;
    }

    public function __destruct()
    {
        $this->sender->push($this->fd, $this->data, $this->opcode, $this->flag);
    }

    public function channel(?int $timeout = null): Channel
    {
        $channel = new Channel(1);
        $i = strval(SocketIO::$messageId->get());
        SocketIO::$messageId->add();
        $this->socketIO->addCallback($i, $channel, $timeout);
        return $channel;
    }

    public function reply(?int $timeout = null)
    {
        return $this->channel($timeout)->pop();
    }
}
