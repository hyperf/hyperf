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

namespace HyperfTest\DbConnection;

use Hyperf\DbConnection\Pool\PoolFactory;
use HyperfTest\DbConnection\Stubs\ContainerStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PoolFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testFlushAll()
    {
        $container = ContainerStub::mockContainer();
        $factory = $container->get(PoolFactory::class);

        $pool1 = $factory->getPool('default');

        // Use reflection to check the pools array
        $reflection = new ReflectionClass($factory);
        $poolsProperty = $reflection->getProperty('pools');
        $poolsProperty->setAccessible(true);
        $pools = $poolsProperty->getValue($factory);

        $this->assertCount(1, $pools);
        $this->assertArrayHasKey('default', $pools);

        // Verify flushAll doesn't throw when there are pools
        $factory->flushAll();

        // The pools should still exist, just with connections flushed
        $pools = $poolsProperty->getValue($factory);
        $this->assertCount(1, $pools);
    }
}
