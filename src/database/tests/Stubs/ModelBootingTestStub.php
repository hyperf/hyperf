<?php

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