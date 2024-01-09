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
namespace Hyperf\SocketIOServer\Parser;

use InvalidArgumentException;
use Throwable;

class Decoder
{
    /**
     * @param string $payload such as `2/ws?foo=xxx,2["event","hellohyperf"]`
     */
    public function decode(string $payload): Packet
    {
        if (! $payload) {
            throw new InvalidArgumentException('Empty packet');
        }

        $length = strlen($payload);
        $type = $payload[0];
        if (! in_array($type, [Packet::OPEN, Packet::CLOSE, Packet::EVENT, Packet::ACK], true)) {
            throw new InvalidArgumentException('Unknown packet type ' . $type);
        }

        if ($length === 1) {
            return $this->makePacket($type);
        }

        $nsp = '/';
        $query = [];

        $payload = substr($payload, 1);
        if ($payload[0] === '/') {
            // parse url
            $exploded = explode(',', $payload, 2);
            $parsedUrl = parse_url($exploded[0]);
            $nsp = $parsedUrl['path'];
            if (! empty($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $query);
            }

            $payload = $exploded[1] ?? null;
        }

        if (! $payload) {
            return $this->makePacket($type, $nsp, $query);
        }

        $offset = 0;
        while (true) {
            $char = $payload[$offset] ?? null;
            if ($char === null) {
                break;
            }

            if (is_numeric($char)) {
                ++$offset;
            } else {
                break;
            }
        }

        $id = substr($payload, 0, $offset);
        $payload = substr($payload, $offset);
        $data = [];
        if ($payload) {
            try {
                $data = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
            } catch (Throwable $exception) {
                throw new InvalidArgumentException('Invalid data', (int) $exception->getCode(), $exception);
            }
        }

        return $this->makePacket($type, $nsp, $query, $id, $data);
    }

    public function makePacket(string $type, string $nsp = '/', array $query = [], string $id = '', array $data = []): Packet
    {
        return Packet::create([
            'type' => $type,
            'nsp' => $nsp,
            'id' => $id,
            'data' => $data,
            'query' => $query,
        ]);
    }
}
