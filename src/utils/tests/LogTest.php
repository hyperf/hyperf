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

namespace HyperfTest\Utils;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\DefaultFormatter;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Log;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LogTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testLogMessage()
    {
        $container = $this->getContainer();
        $expected = ['id' => uniqid()];
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function ($_) use ($expected) {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('log')->with(Mockery::any(), Mockery::any(), Mockery::any())->andReturnUsing(function ($level, $message, $context) use ($expected) {
                $this->assertSame('info', $level);
                $this->assertSame('Hello World!', $message);
                $this->assertSame($expected, $context);
            });

            return $logger;
        });

        Log::info('Hello World!', $expected);
    }

    public function testLogMessageThrowable()
    {
        $container = $this->getContainer();
        $expected = [uniqid()];
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturn(true);
        $container->shouldReceive('has')->with(FormatterInterface::class)->andReturn(false);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function ($_) use ($expected) {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('log')->with(Mockery::any(), Mockery::any(), Mockery::any())->andReturnUsing(function ($level, $message, $context) use ($expected) {
                $this->assertSame('warning', $level);
                $this->assertRegExp('/^RuntimeException: Invalid Operation\./', $message);
                $this->assertSame($expected, $context);
            });

            return $logger;
        });

        Log::warning(new \RuntimeException('Invalid Operation.'), $expected);
    }

    public function testLogMessageThrowableFormatter()
    {
        $container = $this->getContainer();
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(FormatterInterface::class)->andReturn(new DefaultFormatter());
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function ($_) {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('log')->with(Mockery::any(), Mockery::any(), Mockery::any())->andReturnUsing(function ($level, $message, $context) {
                $this->assertSame('error', $level);
                $this->assertRegExp('/^RuntimeException:Invalid Operation\.\(0\)/', $message);
                $this->assertSame([], $context);
            });

            return $logger;
        });

        Log::error(new \RuntimeException('Invalid Operation.'));
    }

    public function testLogNoLogger()
    {
        $container = $this->getContainer();
        $container->shouldReceive('has')->andReturn(false);

        ob_start();
        Log::debug('Hello World.');
        $this->assertSame('[DEBUG] Hello World. []', trim(ob_get_clean()));

        ob_start();
        Log::error(new \RuntimeException('Invalid Operation.'));
        $this->assertRegExp('/\[ERROR\] RuntimeException: Invalid Operation\./', trim(ob_get_clean()));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        return $container;
    }
}
