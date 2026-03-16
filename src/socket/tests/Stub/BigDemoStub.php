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

namespace HyperfTest\Socket\Stub;

class BigDemoStub
{
    public $data;

    public function __construct()
    {
        $this->data = str_repeat('1', 70000);
    }
}
