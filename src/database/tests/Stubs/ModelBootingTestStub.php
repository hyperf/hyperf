<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Database\Stubs;

use Hyperf\Database\Model\Model;

class ModelBootingTestStub extends Model
{
    public static function unboot()
    {
        unset(static::$booted[static::class]);
    }

    public static function isBooted()
    {
        return array_key_exists(static::class, static::$booted);
    }
}
