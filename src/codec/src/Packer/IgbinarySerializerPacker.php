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

class IgbinarySerializerPacker implements PackerInterface
{
    public function pack($data): string
    {
        return igbinary_serialize($data);
    }

    public function unpack(string $data)
    {
        return igbinary_unserialize($data);
    }
}
