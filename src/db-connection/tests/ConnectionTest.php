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

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\DbConnection\Connection;
use Hyperf\DbConnection\Pool\PoolFactory;
use HyperfTest\DbConnection\Stubs\ConnectionStub;
use HyperfTest\DbConnection\Stubs\ContainerStub;
use HyperfTest\DbConnection\Stubs\PDOStub;
use Mockery;
use PHPUnit\Framework\TestCase;

use function Hyperf\Coroutine\defer;
use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set('database.connection.default', null);
    }

    public function testResolveConnection()
    {
        $container = ContainerStub::mockContainer();

        $resolver = $container->get(ConnectionResolverInterface::class);

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

    public function testConnectionRollback()
    {
        $container = ContainerStub::mockContainer();

        $resolver = $container->get(ConnectionResolverInterface::class);

        /** @var \Hyperf\Database\Connection $connection */
        $connection = $resolver->connection();

        $connection->beginTransaction();
        $this->assertSame(1, $connection->transactionLevel());
        $connection->rollBack();
        $this->assertSame(0, $connection->transactionLevel());

        $connection->beginTransaction();
        $connection->beginTransaction();
        $this->assertSame(2, $connection->transactionLevel());
        $connection->rollBack(0);
        $this->assertSame(0, $connection->transactionLevel());

        $connection->beginTransaction();
        $connection->beginTransaction();
        $connection->beginTransaction();
        $this->assertSame(3, $connection->transactionLevel());
        $connection->rollBack();
        $this->assertSame(2, $connection->transactionLevel());
        $connection->rollBack(0);
        $this->assertSame(0, $connection->transactionLevel());
    }

    public function testConnectionReadWrite()
    {
        $container = ContainerStub::mockReadWriteContainer();

        $resolver = $container->get(ConnectionResolverInterface::class);

        /** @var \Hyperf\Database\Connection $connection */
        $connection = $resolver->connection();

        /** @var PDOStub $pdo */
        $pdo = $connection->getPdo();
        $this->assertSame('mysql:host=192.168.1.2;dbname=hyperf', $pdo->dsn);
        $pdo = $connection->getReadPdo();
        $this->assertSame('mysql:host=192.168.1.1;dbname=hyperf', $pdo->dsn);
    }

    public function testPdoDontDestruct()
    {
        $container = ContainerStub::mockContainer();
        $pool = $container->get(PoolFactory::class)->getPool('default');
        $config = $container->get(ConfigInterface::class)->get('databases.default');

        $callables = [function ($connection) {
            $connection->selectOne('SELECT 1;');
        }, function ($connection) {
            $connection->table('user')->leftJoin('user_ext', 'user.id', '=', 'user_ext.id')->get();
        }];

        $closes = [function ($connection) {
            $connection->close();
        }, function ($connection) {
            $connection->reconnect();
        }];

        foreach ($callables as $callable) {
            foreach ($closes as $closure) {
                $connection = new ConnectionStub($container, $pool, $config);
                $connection->setPdo(new PDOStub('', '', '', []));

                PDOStub::$destruct = 0;
                $callable($connection);
                $this->assertSame(0, PDOStub::$destruct);
                $closure($connection);
                $this->assertSame(1, PDOStub::$destruct);
            }
        }
    }

    public function testConnectionSticky()
    {
        $container = ContainerStub::mockReadWriteContainer();

        parallel([function () use ($container) {
            $resolver = $container->get(ConnectionResolverInterface::class);

            /** @var \Hyperf\Database\Connection $connection */
            $connection = $resolver->connection();
            $connection->statement('UPDATE hyperf.test SET name = 1 WHERE id = 1;');

            /** @var PDOStub $pdo */
            $pdo = $connection->getPdo();
            $this->assertSame('mysql:host=192.168.1.2;dbname=hyperf', $pdo->dsn);
            $pdo = $connection->getReadPdo();
            $this->assertSame('mysql:host=192.168.1.2;dbname=hyperf', $pdo->dsn);
        }]);

        parallel([function () use ($container) {
            $resolver = $container->get(ConnectionResolverInterface::class);

            /** @var \Hyperf\Database\Connection $connection */
            $connection = $resolver->connection();

            /** @var PDOStub $pdo */
            $pdo = $connection->getPdo();
            $this->assertSame('mysql:host=192.168.1.2;dbname=hyperf', $pdo->dsn);
            $pdo = $connection->getReadPdo();
            $this->assertSame('mysql:host=192.168.1.1;dbname=hyperf', $pdo->dsn);
        }]);
    }

    public function testDbConnectionUseInDefer()
    {
        $container = ContainerStub::mockReadWriteContainer();

        parallel([function () use ($container) {
            $resolver = $container->get(ConnectionResolverInterface::class);

            defer(function () {
                $this->assertFalse(Context::has('database.connection.default'));
            });
            $resolver->connection();
        }]);
    }
}
