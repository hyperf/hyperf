<?php

namespace HyperfTest\Validation\Cases\fixtures;

use Hyperf\Utils\Contracts\Arrayable;

class Values implements Arrayable
{
    public function toArray(): array
    {
        return [1, 2, 3, 4];
    }
}
