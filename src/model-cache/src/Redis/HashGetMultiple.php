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

namespace Hyperf\ModelCache\Redis;

class HashGetMultiple implements OperatorInterface
{
    public function getScript(): string
    {
        return <<<'LUA'
    local values = {}; 
    for i,v in ipairs(KEYS) do 
        if(redis.call('type',v).ok == 'hash') then
            values[#values+1] = redis.call('hgetall',v);
        end
    end
    return values;
LUA;
    }

    public function parseResponse($data)
    {
        $result = [];
        foreach ($data ?? [] as $item) {
            if (! empty($item) && is_array($item)) {
                $temp = [];
                $count = count($item);
                for ($i = 0; $i < $count; ++$i) {
                    $temp[$item[$i]] = $item[++$i];
                }

                $result[] = $temp;
            }
        }

        return $result;
    }
}
