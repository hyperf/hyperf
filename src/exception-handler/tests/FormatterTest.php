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

use Hyperf\ExceptionHandler\Formatter\DefaultFormatter;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FormatterTest extends TestCase
{
    public function testDefaultFormatter()
    {
        $formatter = new DefaultFormatter();

        $message = uniqid();
        $code = rand(1000, 9999);
        $exception = new RuntimeException($message, $code);
        $this->assertSame((string) $exception, $formatter->format($exception));
    }
}
