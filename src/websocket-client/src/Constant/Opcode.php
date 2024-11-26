<?php

declare(strict_types=1);

namespace Hyperf\WebSocketClient\Constant;

class Opcode
{
    public const CONTINUATION = 0b0000; // 0x0, 0
    public const TEXT = 0b0001;       // 0x1, 1
    public const BINARY = 0b0010;      // 0x2, 2
    public const CLOSE = 0b1000;      // 0x8, 8
    public const PING = 0b1001;       // 0x9, 9
    public const PONG = 0b1010;       // 0xA, 10

    public static function getOpcodeDefinition(int $opcode): string
    {
        $definitions = [
            self::CONTINUATION => 'Continuation',
            self::TEXT => 'Text',
            self::BINARY => 'Binary',
            self::CLOSE => 'Close',
            self::PING => 'Ping',
            self::PONG => 'Pong',
        ];
        return $definitions[$opcode] ?? 'Unknown Opcode';
    }
}
