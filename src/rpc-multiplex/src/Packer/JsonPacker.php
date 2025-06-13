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

namespace Hyperf\RpcMultiplex\Packer;

use Hyperf\Codec\Json;
use Hyperf\Contract\PackerInterface;

class JsonPacker implements PackerInterface
{
    public function pack($data): string
    {
        return Json::encode($data);
    }

    public function unpack(string $data)
    {
        return Json::decode($data);
    }
}
