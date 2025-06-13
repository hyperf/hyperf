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

namespace Hyperf\Protocol\Packer;

use Hyperf\Protocol\ProtocolPackerInterface;

class SerializePacker implements ProtocolPackerInterface
{
    public function pack($data): string
    {
        $string = serialize($data);
        return pack('N', strlen($string)) . $string;
    }

    public function unpack(string $data)
    {
        return unserialize(substr($data, self::HEAD_LENGTH));
    }

    public function length(string $head): int
    {
        return unpack('Nlen', $head)['len'];
    }
}
