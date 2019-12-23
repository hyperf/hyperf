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

namespace Hyperf\JsonRpc\Packer;

use Hyperf\Contract\PackerInterface;

class JsonPacker implements PackerInterface
{
    const HEAD_LENGTH = 4;

    public function pack($data): string
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        return pack('N', strlen($data)) . $data;
    }

    public function unpack(string $data)
    {
        $data = substr($data, self::HEAD_LENGTH);
        return json_decode($data, true);
    }
}
