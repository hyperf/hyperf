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
namespace HyperfTest\Logger\Stub;

use Hyperf\Utils\Context;
use Monolog\Handler\StreamHandler;

class FooHandler extends StreamHandler
{
    public function write(array $record): void
    {
        Context::set('test.logger.foo_handler.record', $record);
    }
}
