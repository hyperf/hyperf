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

namespace Hyperf\SocketIOServer\Emitter;

use Hyperf\Engine\Channel;
use Hyperf\Engine\Contract\WebSocket\FrameInterface;
use Hyperf\Engine\WebSocket\Frame;
use Hyperf\Engine\WebSocket\Opcode;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\WebSocketServer\Sender;

class Future
{
    /**
     * @var callable
     */
    private $encode;

    private string $id;

    private bool $sent;

    /**
     * @param int $flag deprecated it will be removed in v3.2 or v4.0
     */
    public function __construct(
        private SocketIO $socketIO,
        private Sender $sender,
        private int $fd,
        private string $event,
        private array $data,
        callable $encode,
        private int $opcode = Opcode::TEXT,
        private int $flag = 0,
        private ?FrameInterface $frame = null
    ) {
        $this->id = '';
        $this->encode = $encode;
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
        if ($this->frame) {
            $this->sender->pushFrame($this->fd, $this->frame->setPayloadData($message));
        } else {
            $this->sender->pushFrame($this->fd, new Frame(opcode: $this->opcode, payloadData: $message));
        }
    }
}
