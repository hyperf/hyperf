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
namespace HyperfTest\Pool;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Pool;
use HyperfTest\Pool\Stub\ActiveConnectionStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetActiveConnectionAgain()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive('warning')->withAnyArgs()->once()->andReturnTrue();
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->once()->andReturnTrue();
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->once()->andReturn($logger);

        $connection = new ActiveConnectionStub($container, Mockery::mock(Pool::class));
        $this->assertEquals($connection, $connection->getConnection());
    }
}
