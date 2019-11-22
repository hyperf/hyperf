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

namespace Hyperf\WebSocketClient;

use Swoole\WebSocket\Frame as SwFrame;

class Frame
{
    /**
     * @var bool
     */
    public $finish = true;

    /**
     * @var string
     */
    public $opcode;

    /**
     * @var string
     */
    public $data;

    public function __construct(SwFrame $frame)
    {
        foreach ($frame as $key => $val) {
            $this->{$key} = $val;
        }
    }

    public function __toString()
    {
        return $this->data;
    }

    public function getOpcodeDefinition(): string
    {
        static $map = [
            1 => 'WEBSOCKET_OPCODE_TEXT',
            2 => 'WEBSOCKET_OPCODE_BINARY',
            9 => 'WEBSOCKET_OPCODE_PING',
        ];

        return $map[$this->opcode] ?? 'WEBSOCKET_BAD_OPCODE';
    }

    public function getOpcode(): string
    {
        return $this->opcode;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
