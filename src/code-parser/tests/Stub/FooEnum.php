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
namespace HyperfTest\CodeParser\Stub;

if (PHP_VERSION_ID > 80100) {
    enum FooEnum: int
    {
        case DEFAULT = 1;
    }
}
