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

namespace Hyperf\Codec\Packer;

use Hyperf\Contract\PackerInterface;
use InvalidArgumentException;

class Resp3Packer implements PackerInterface
{
    public function pack($data): string
    {
        return $this->encode($data);
    }

    public function unpack(string $data)
    {
        $pos = 0;
        return $this->decode($data, $pos);
    }

    private function encode($data): string
    {
        if ($data === null) {
            return "_\r\n";
        }

        if (is_bool($data)) {
            return '#' . ($data ? 't' : 'f') . "\r\n";
        }

        if (is_int($data)) {
            return ':' . $data . "\r\n";
        }

        if (is_float($data)) {
            return ',' . $data . "\r\n";
        }

        if (is_string($data)) {
            return '$' . strlen($data) . "\r\n" . $data . "\r\n";
        }

        if (is_array($data)) {
            if ($this->isAssoc($data)) {
                return $this->encodeMap($data);
            }
            return $this->encodeArray($data);
        }

        throw new InvalidArgumentException('Unsupported data type: ' . gettype($data));
    }

    private function encodeArray(array $data): string
    {
        $result = '*' . count($data) . "\r\n";
        foreach ($data as $item) {
            $result .= $this->encode($item);
        }
        return $result;
    }

    private function encodeMap(array $data): string
    {
        $result = '%' . count($data) . "\r\n";
        foreach ($data as $key => $value) {
            $result .= $this->encode($key);
            $result .= $this->encode($value);
        }
        return $result;
    }

    private function decode(string $data, int &$pos)
    {
        if ($pos >= strlen($data)) {
            throw new InvalidArgumentException('Unexpected end of data');
        }

        $type = $data[$pos];
        ++$pos;

        switch ($type) {
            case '_':
                $this->skipCrlf($data, $pos);
                return null;
            case '#':
                $value = $this->readLine($data, $pos);
                return $value === 't';
            case ':':
                $value = $this->readLine($data, $pos);
                return (int) $value;
            case ',':
                $value = $this->readLine($data, $pos);
                return (float) $value;
            case '+':
                return $this->readLine($data, $pos);
            case '-':
                $error = $this->readLine($data, $pos);
                throw new InvalidArgumentException('RESP3 Error: ' . $error);
            case '$':
                $length = (int) $this->readLine($data, $pos);
                if ($length === -1) {
                    return null;
                }
                $value = substr($data, $pos, $length);
                $pos += $length;
                $this->skipCrlf($data, $pos);
                return $value;
            case '*':
                $length = (int) $this->readLine($data, $pos);
                if ($length === -1) {
                    return null;
                }
                $array = [];
                for ($i = 0; $i < $length; ++$i) {
                    $array[] = $this->decode($data, $pos);
                }
                return $array;
            case '%':
                $length = (int) $this->readLine($data, $pos);
                $map = [];
                for ($i = 0; $i < $length; ++$i) {
                    $key = $this->decode($data, $pos);
                    $value = $this->decode($data, $pos);
                    $map[$key] = $value;
                }
                return $map;
            case '~':
                $length = (int) $this->readLine($data, $pos);
                $set = [];
                for ($i = 0; $i < $length; ++$i) {
                    $set[] = $this->decode($data, $pos);
                }
                return array_unique($set);
            default:
                throw new InvalidArgumentException('Unknown RESP3 type: ' . $type);
        }
    }

    private function readLine(string $data, int &$pos): string
    {
        $start = $pos;
        while ($pos < strlen($data) && $data[$pos] !== "\r") {
            ++$pos;
        }

        if ($pos >= strlen($data) - 1 || $data[$pos + 1] !== "\n") {
            throw new InvalidArgumentException('Expected CRLF');
        }

        $line = substr($data, $start, $pos - $start);
        $pos += 2; // Skip \r\n
        return $line;
    }

    private function skipCrlf(string $data, int &$pos): void
    {
        if ($pos < strlen($data) - 1 && $data[$pos] === "\r" && $data[$pos + 1] === "\n") {
            $pos += 2;
        }
    }

    private function isAssoc(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
