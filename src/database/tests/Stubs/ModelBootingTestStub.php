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

namespace HyperfTest\Database\Stubs;

use Hyperf\Database\Model\Booted;
use Hyperf\Database\Model\Model;

class ModelBootingTestStub extends Model
{
    public function unboot()
    {
        Booted::$container[static::class] = false;
    }

    public function isBooted()
    {
        return Booted::$container[static::class] ?? false;
    }
}
