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

namespace Hyperf\Nacos;

enum CloudName: string
{
    case Aliyun = 'aliyun';

    public static function safeFrom(mixed $value): ?CloudName
    {
        if ($value instanceof CloudName) {
            return $value;
        }

        return CloudName::tryFrom($value);
    }
}
