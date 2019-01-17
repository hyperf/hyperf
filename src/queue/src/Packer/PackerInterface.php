<?php

namespace Hyperf\Queue\Packer;


interface PackerInterface
{
    public function pack($data): string;

    public function unpack(string $data);
}