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

namespace HyperfTest\ModelCache\Handler;

use Hyperf\ModelCache\Handler\RedisStringHandler;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RedisStringHandlerTest extends RedisHandlerTest
{
    protected $handler = RedisStringHandler::class;
}
