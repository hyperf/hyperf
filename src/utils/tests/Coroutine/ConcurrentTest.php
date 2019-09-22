<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Utils\Coroutine;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine\Concurrent;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine;

/**
 * @internal
 * @coversNothing
 */
class ConcurrentTest extends TestCase
{
    protected function setUp()
    {
        $this->getContainer();
    }

    public function testConcurrent()
    {
        $concurrent = new Concurrent($limit = 10, 1);
        $this->assertSame($limit, $concurrent->getLimit());
        $this->assertTrue($concurrent->isEmpty());
        $this->assertFalse($concurrent->isFull());
        $count = 0;
        for ($i = 0; $i < 15; ++$i) {
            $concurrent->create(function () use (&$count) {
                Coroutine::sleep(0.1);
                ++$count;
            });
        }

        $this->assertTrue($concurrent->isFull());
        $this->assertSame(5, $count);
        $this->assertSame($limit, $concurrent->getRunningCoroutineCount());
        $this->assertSame($limit, $concurrent->getLength());
        $this->assertSame($limit, $concurrent->length());

        $concurrent->wait();
        $this->assertTrue($concurrent->isEmpty());
        $this->assertSame(15, $count);
    }

    public function testException()
    {
        $con = new Concurrent(10, 1);
        $count = 0;
        for ($i = 0; $i < 15; ++$i) {
            $con->create(function () use (&$count) {
                Coroutine::sleep(0.1);
                ++$count;
                throw new \Exception('ddd');
            });
        }

        $this->assertSame(5, $count);
        $this->assertSame(10, $con->getRunningCoroutineCount());

        $con->wait();
        $this->assertTrue($con->isEmpty());
        $this->assertSame(15, $count);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturn(false);

        ApplicationContext::setContainer($container);
    }
}
