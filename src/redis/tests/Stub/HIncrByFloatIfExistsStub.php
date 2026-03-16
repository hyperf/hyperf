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

namespace HyperfTest\Redis\Stub;

use Hyperf\Redis\Lua\Hash\HIncrByFloatIfExists;

class HIncrByFloatIfExistsStub extends HIncrByFloatIfExists
{
    protected $sha = 'xxxx';
}
