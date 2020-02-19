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

namespace HyperfTest\Redis\Stub;

use Hyperf\Redis\Lua\Hash\HGetAllMultiple;

class HGetAllMultipleStub extends HGetAllMultiple
{
    protected $sha = 'xxxx';
}
