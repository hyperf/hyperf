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
     * @param string $payload such as `42/ws?foo=xxx,2["event","hellohyperf"]`
     */
    public function decode(string $payload): Packet
    {
        $engine = $payload[0];

        if ($engine !== Engine::MESSAGE) {
            throw new InvalidArgumentException('Invalid engine ' . $engine);
        }

        $payloadLength = strlen($payload);
        $currentIndex = 1;

        $type = $payload[1] ?? throw new InvalidArgumentException('Empty packet');
        $nsp = '/';
        $query = [];
        $id = '';
        $data = [];

        if (! in_array($type, [Packet::OPEN, Packet::CLOSE, Packet::EVENT, Packet::ACK], true)) {
            throw new InvalidArgumentException('Unknown packet type ' . $type);
        }

        // TODO: look up attachments if type binary

        // look up namespace (if any)
        if ($currentIndex + 1 === $payloadLength) {
            goto _out;
        }

        if ($payload[$currentIndex + 1] === '/') {
            $nspStart = $currentIndex + 1;
            $nspEnd = strpos($payload, ',');
            $queryStart = strpos($payload, '?');

            $currentIndex = $nspEnd !== false ? $nspEnd : $payloadLength;

            if ($queryStart !== false) {
                $queryLength = $nspEnd === false ? $currentIndex - $queryStart : $currentIndex - $queryStart - 1;
                $queryStr = substr($payload, $queryStart + 1, $queryLength);

                $nsp = substr($payload, $nspStart, $queryStart - $nspStart);
                parse_str($queryStr, $query);
            } else {
                $nsp = substr($payload, $nspStart, $currentIndex - $nspStart);
            }
        }

        if ($currentIndex >= $payloadLength) {
            goto _out;
        }

        // Parser packet id
        $start = $currentIndex + 1;
        while (++$currentIndex) {
            if ($currentIndex === $payloadLength) {
                $id = substr($payload, $start);
                goto _out;
            }
            $char = $payload[$currentIndex];
            if (! is_numeric($char)) {
                --$currentIndex;
                break;
            }
        }
        $id = substr($payload, $start, $currentIndex - $start + 1);

        // look up json data
        if ($currentIndex < $payloadLength - 1) {
            try {
                $data = json_decode(substr($payload, $currentIndex + 1), associative: true, flags: JSON_THROW_ON_ERROR);
            } catch (Throwable $exception) {
                throw new InvalidArgumentException('Invalid data', (int) $exception->getCode(), $exception);
            }
        }

        _out:
        return Packet::create([
            'type' => $type,
            'nsp' => $nsp,
            'id' => $id,
            'data' => $data,
            'query' => $query,
        ]);
    }
}
