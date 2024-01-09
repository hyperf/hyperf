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
    public function decodeBackup($payload): Packet
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

    /**
     * @param string $payload such as `2/ws?foo=xxx,2["event","hellohyperf"]`
     */
    public function decode(string $payload): Packet
    {
        if (! $payload) {
            throw new \InvalidArgumentException('Empty packet');
        }

        $length = strlen($payload);
        $type = $payload[0];
        if (! in_array($type, [Packet::OPEN, Packet::CLOSE, Packet::EVENT, Packet::ACK], true)) {
            throw new \InvalidArgumentException('Unknown packet type ' . $type);
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
                $data = json_decode($payload, flags: JSON_THROW_ON_ERROR);
            } catch (Throwable $exception) {
                throw new InvalidArgumentException('Invalid data', (int) $exception->getCode(), $exception);
            }
        }

        return $this->makePacket($type, $nsp, $query, $id, $data);
    }

    public function makePacket(string $type, string $nsp = '/', array $query = [], string $id = '', array $data = [])
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
