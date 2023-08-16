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
namespace HyperfTest\ExceptionHandler;

use ErrorException;
use Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ErrorExceptionHandlerTest extends TestCase
{
    public function testHandleError()
    {
        $listener = new ErrorExceptionHandler();
        $listener->process((object) []);

        $this->expectException(ErrorException::class);
        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            $this->expectExceptionMessage('Undefined array key 1');
        } else {
            $this->expectExceptionMessage('Undefined offset: 1');
        }
        try {
            $array = [];
            $array[1];
        } finally {
            restore_error_handler();
        }
    }
}
