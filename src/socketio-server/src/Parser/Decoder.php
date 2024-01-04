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

use Hyperf\Codec\Exception\InvalidArgumentException;
use Throwable;

class Decoder
{
    public function decode($payload): Packet
    {
        $payloadLength = strlen($payload);
        $currentIndex = 0;

        $type = $payload[0] ?? throw new \InvalidArgumentException('Empty packet');
        $nsp = '/';
        $query = [];
        $id = '';
        $data = [];

        if (! in_array($type, [Packet::OPEN, Packet::CLOSE, Packet::EVENT, Packet::ACK], true)) {
            throw new \InvalidArgumentException('Unknown packet type ' . $type);
        }

        // TODO: look up attachments if type binary

        // look up namespace (if any)
        if ($currentIndex + 1 === $payloadLength) {
            goto _out;
        }
        if ($payload[$currentIndex + 1] === '/') {
            $start = $currentIndex + 1;
            while (++$currentIndex) {
                if ($currentIndex === $payloadLength) {
                    break;
                }
                $char = $payload[$currentIndex];
                if ($char === ',') {
                    break;
                }
            }
            $nspStart = $start;
            $nspEnd = $currentIndex;
            $queryStart = $nspStart;
            // look up query in namespace (e.g. "1/ws?foo=bar&baz=1,")
            while (++$queryStart) {
                if ($queryStart === $currentIndex) {
                    break;
                }
                $char = $payload[$queryStart];
                if ($char === '?') {
                    $queryLength = $nspEnd - $queryStart;
                    $queryStr = substr($payload, $queryStart + 1, $queryLength - 1);
                    parse_str($queryStr, $query);
                    $nspEnd = $queryStart;
                    break;
                }
            }
            $nsp = substr($payload, $nspStart, $nspEnd - $nspStart);
        }

        // look up id
        if ($currentIndex === $payloadLength) {
            goto _out;
        }
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
                $data = json_decode(substr($payload, $currentIndex + 1), flags: JSON_THROW_ON_ERROR);
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
