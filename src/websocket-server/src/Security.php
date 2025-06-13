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

namespace Hyperf\WebSocketServer;

class Security
{
    public const VERSION = '13';

    public const PATTEN = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';

    public const KEY = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    public const SEC_WEBSOCKET_KEY = 'sec-websocket-key';

    public const SEC_WEBSOCKET_PROTOCOL = 'sec-webSocket-protocol';

    public function isInvalidSecurityKey(string $key): bool
    {
        return preg_match(self::PATTEN, $key) === 0 || strlen(base64_decode($key)) !== 16;
    }

    public function handshakeHeaders(string $key): array
    {
        return [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $this->sign($key),
            'Sec-WebSocket-Version' => self::VERSION,
        ];
    }

    public function sign(string $key): string
    {
        return base64_encode(sha1(trim($key) . self::KEY, true));
    }
}
