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

namespace Hyperf\Protocol\Packer;

use Hyperf\Protocol\ProtocolPackerInterface;

class SerializePacker implements ProtocolPackerInterface
{
    public function pack($data): string
    {
        return pack('N', strlen($data)) . serialize($data);
    }

    public function unpack(string $data)
    {
        return unserialize($data);
    }

    public function length(string $head): int
    {
        return unpack('N', $head)[1];
    }
}
