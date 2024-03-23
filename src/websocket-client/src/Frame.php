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

namespace Hyperf\WebSocketClient;

use Stringable;
use Swoole\WebSocket\Frame as SwFrame;

class Frame implements Stringable
{
    public bool $finish = true;

    public int $opcode;

    public string $data;

    public function __construct(SwFrame $frame)
    {
        $this->finish = $frame->finish;
        $this->opcode = $frame->opcode;
        $this->data = $frame->data;
    }

    public function __toString(): string
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

    public function getOpcode(): int
    {
        return $this->opcode;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
