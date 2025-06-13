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

use Hyperf\Context\Context;
use Monolog\Handler\StreamHandler;
use Monolog\LogRecord;

class FooHandler extends StreamHandler
{
    public function write(array|LogRecord $record): void
    {
        Context::set('test.logger.foo_handler.record', $record);
    }
}
