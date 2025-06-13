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

namespace HyperfTest\Pool\Stub;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;
use Mockery;

class FooPool extends Pool
{
    protected function createConnection(): ConnectionInterface
    {
        return Mockery::mock(ConnectionInterface::class);
    }
}
