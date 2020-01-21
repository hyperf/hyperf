<?php

namespace Hyperf\Nsq;


class Packer
{

    public function packUInt32($int)
    {
        return pack('N', $int);
    }

}