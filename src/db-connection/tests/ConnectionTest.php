<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\DbConnection;

use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Connection;
use Hyperf\DbConnection\ConnectionResolver;
use Hyperf\DbConnection\Pool\PoolFactory;
use HyperfTest\DbConnection\Stubs\ConnectionStub;
use HyperfTest\DbConnection\Stubs\ContainerStub;
use HyperfTest\DbConnection\Stubs\PDOStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConnectionTest extends TestCase
{
    public function testResolveConnection()
    {
        $container = ContainerStub::mockContainer();

        $resolver = $container->get(ConnectionResolver::class);

        $connection = $resolver->connection();

        $this->assertInstanceOf(Connection::class, $connection);
    }

    public function testConnectionRefresh()
    {
        $container = ContainerStub::mockContainer();
        $pool = $container->get(PoolFactory::class)->getPool('default');
        $config = $container->get(ConfigInterface::class)->get('databases.default');
        $connection = new ConnectionStub($container, $pool, $config);

        $connection->setPdo(null);
        $this->assertNull($connection->getPdo());

        $connection->select('SELECT 1;');
        $pdo = $connection->getPdo();
        $this->assertInstanceOf(PDOStub::class, $pdo);
    }
}
