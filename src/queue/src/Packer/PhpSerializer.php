<?php
/**
 * Created by PhpStorm.
 * User: limx
 * Date: 2019/1/17
 * Time: 4:00 PM
 */

namespace Hyperf\Queue\Packer;


class PhpSerializer implements PackerInterface
{
    public function pack($data): string
    {
        return serialize($data);
    }

    public function unpack(string $data)
    {
        return unserialize($data);
    }
}