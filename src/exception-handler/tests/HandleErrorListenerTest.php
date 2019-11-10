<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\ExceptionHandler;

use Hyperf\ExceptionHandler\Listener\HandleErrorListener;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class HandleErrorListenerTest extends TestCase
{
    public function testHandleError()
    {
        $listener = new HandleErrorListener();
        $listener->process((object) []);

        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Undefined offset: 1');
        try {
            $array = [];
            $array[1];
        } finally {
            restore_error_handler();
        }
    }
}
