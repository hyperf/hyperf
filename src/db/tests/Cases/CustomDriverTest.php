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

namespace HyperfTest\DB\Cases;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\DB\Pool\Pool;
use Hyperf\DB\Pool\PoolFactory;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CustomDriverTest extends AbstractTestCase
{
    public function testCustomDriver()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'db' => [
                'custom' => [
                    'driver' => CustomPool::class,
                    'host' => '127.0.0.1',
                    'password' => '',
                    'database' => 'hyperf',
                    'pool' => [
                        'max_connections' => 20,
                    ],
                    'options' => [],
                ],
            ],
        ]));
        $factory = new PoolFactory($container);

        $pool = $factory->getPool('custom');

        $this->assertInstanceOf(CustomPool::class, $pool);
        $this->assertInstanceOf(CustomConnection::class, $pool->get());
    }
}

class CustomPool extends Pool
{
    protected function createConnection(): ConnectionInterface
    {
        return new CustomConnection();
    }
}

class CustomConnection implements ConnectionInterface
{
    public function getConnection()
    {
        // TODO: Implement getConnection() method.
    }

    public function reconnect(): bool
    {
        // TODO: Implement reconnect() method.
    }

    public function check(): bool
    {
        // TODO: Implement check() method.
    }

    public function close(): bool
    {
        // TODO: Implement close() method.
    }

    public function release(): void
    {
        // TODO: Implement release() method.
    }
}
