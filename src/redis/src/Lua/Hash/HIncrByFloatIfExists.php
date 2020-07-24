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
namespace Hyperf\Redis\Lua\Hash;

use Hyperf\Redis\Lua\Script;

class HIncrByFloatIfExists extends Script
{
    public function getScript(): string
    {
        return <<<'LUA'
    if(redis.call('type', KEYS[1]).ok == 'hash') then
        return redis.call('HINCRBYFLOAT', KEYS[1], ARGV[1], ARGV[2]);
    end
    return "";
LUA;
    }

    /**
     * @param null|float $data
     * @return null|float
     */
    public function format($data)
    {
        if (is_numeric($data)) {
            return $data;
        }
        return null;
    }

    protected function getKeyNumber(array $arguments): int
    {
        return 1;
    }
}
