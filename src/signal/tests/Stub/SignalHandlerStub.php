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

namespace HyperfTest\Signal\Stub;

use Hyperf\Context\Context;
use Hyperf\Signal\SignalHandlerInterface;

class SignalHandlerStub implements SignalHandlerInterface
{
    public function listen(): array
    {
        return [
            [self::WORKER, SIGTERM],
        ];
    }

    public function handle(int $signal): void
    {
        Context::set('test.signal', $signal);
    }
}
