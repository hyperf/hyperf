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
namespace HyperfTest\Retry;

use Hyperf\Retry\Policy\FallbackRetryPolicy;
use Hyperf\Retry\Policy\MaxAttemptsRetryPolicy;
use Hyperf\Retry\Retry;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Retry\Stub\Foo;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class RetryTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testWith()
    {
        $i = 0;
        $result = Retry::with(new MaxAttemptsRetryPolicy(3))->call(function () use (&$i) {
            return ++$i;
        });
        $this->assertEquals(3, $result);
    }

    public function testWhenReturns()
    {
        $i = 0;
        $result = Retry::with(new MaxAttemptsRetryPolicy(3))->whenReturns(1)->call(function () use (&$i) {
            return ++$i;
        });
        $this->assertEquals(2, $result);
    }

    public function testWhenThrows()
    {
        $i = -1;
        $this->expectException('InvalidArgumentException');
        $result = Retry::with(new MaxAttemptsRetryPolicy(3))->whenThrows('RuntimeException')->call(function () use (&$i) {
            $ex = [new \RuntimeException(), new \InvalidArgumentException()];
            throw $ex[++$i];
        });
    }

    public function testWhen()
    {
        $i = 0;
        $result = Retry::when(function ($context) {
            if (! isset($context['lastResult'])) {
                return true;
            }
            return $context['lastResult'] < 5;
        })->call(function () use (&$i) {
            return ++$i;
        });
        $this->assertEquals(5, $result);
    }

    public function testInSeconds()
    {
        $i = 0;
        $result = Retry::InSeconds(0.01)->call(function () use (&$i) {
            usleep(1000);
            return ++$i;
        });
        $this->assertLessThan(11, $result);
        $this->assertGreaterThan(6, $result);
    }

    public function testFallback()
    {
        $i = 0;
        $result = Retry::max(2)->fallback(function () {
            return 10;
        })->call(function () use (&$i) {
            return $i;
        });
        $this->assertEquals(10, $result);

        $i = 0;
        $result = Retry::max(2)->fallback([new Foo(), 'fallback'])->call(function () use (&$i) {
            return $i;
        });
        $this->assertEquals(10, $result);

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(Foo::class)->once()->andReturn(new Foo());
        ApplicationContext::setContainer($container);
        $i = 0;
        $result = Retry::with(new FallbackRetryPolicy(Foo::class . '@fallback'))->max(2)->call(function () use (&$i) {
            return $i;
        });
        $this->assertEquals(10, $result);

        $i = 0;
        $result = Retry::with(new FallbackRetryPolicy(Foo::class . '::staticCall'))->max(2)->call(function () use (&$i) {
            return $i;
        });
        $this->assertEquals(10, $result);

        $this->assertTrue(is_callable('Hyperf\\Utils\\Arr::accessible'));
        $this->assertTrue(is_callable(Foo::class . '::fallback'));
        $this->assertTrue(is_callable(Foo::class . '::staticCall'));
    }
}
