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
     * @var array
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
     * @var string
     */
    private $event;

    /**
     * @var callable
     */
    private $encode;

    /**
     * @var string
     */
    private $id;

    /**
     * @var SocketIO
     */
    private $socketIO;

    /**
     * @var bool
     */
    private $sent;

    public function __construct(
        SocketIO $socketIO,
        Sender $sender,
        int $fd,
        string $event,
        array $data,
        callable $encode,
        int $opcode,
        int $flag
    ) {
        $this->socketIO = $socketIO;
        $this->sender = $sender;
        $this->fd = $fd;
        $this->id = '';
        $this->event = $event;
        $this->data = $data;
        $this->encode = $encode;
        $this->opcode = $opcode;
        $this->flag = $flag;
        $this->sent = false;
    }

    public function __destruct()
    {
        $this->send();
    }

    public function channel(?int $timeout = null): Channel
    {
        $channel = new Channel(1);
        $this->id = strval(SocketIO::$messageId->get());
        SocketIO::$messageId->add();
        $this->socketIO->addCallback($this->id, $channel, $timeout);
        return $channel;
    }

    public function reply(?int $timeout = null)
    {
        $channel = $this->channel($timeout);
        $this->send();
        return $channel->pop();
    }

    private function send()
    {
        if ($this->sent) {
            return;
        }
        $message = ($this->encode)($this->id, $this->event, $this->data);
        $this->sent = true;
        $this->sender->push($this->fd, $message, $this->opcode, $this->flag);
    }
}
