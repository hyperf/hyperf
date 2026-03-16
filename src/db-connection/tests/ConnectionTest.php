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

use Exception;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Waiter;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Exception\QueryException;
use Hyperf\Database\Model\Register;
use Hyperf\DbConnection\Connection;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Support\Reflection\ClassInvoker;
use HyperfTest\DbConnection\Stubs\ConnectionStub;
use HyperfTest\DbConnection\Stubs\ConnectionStub2;
use HyperfTest\DbConnection\Stubs\ContainerStub;
use HyperfTest\DbConnection\Stubs\PDOStub;
use HyperfTest\DbConnection\Stubs\PDOStub2;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Coroutine\defer;
use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set('database.connection.default', null);
        Register::unsetConnectionResolver();
        Register::unsetEventDispatcher();
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
        (new Waiter())->wait(function () {
            $container = ContainerStub::mockContainer();
            $pool = $container->get(PoolFactory::class)->getPool('default');
            $config = $container->get(ConfigInterface::class)->get('databases.default');

            $callables = [
                function (Connection $connection) {
                    $connection->selectOne('SELECT 1;');
                }, function (Connection $connection) {
                    $connection->table('user')->leftJoin('user_ext', 'user.id', '=', 'user_ext.id')->get();
                },
            ];

            $closes = [
                function (Connection $connection) {
                    $connection->close();
                }, function (Connection $connection) {
                    $connection->reconnect();
                },
            ];

            Context::set(PDOStub2::class . '::destruct', 0);
            $count = 0;
            foreach ($callables as $callable) {
                foreach ($closes as $closure) {
                    $connection = new ConnectionStub2($container, $pool, $config);
                    $connection->setPdo(new PDOStub2('', '', '', []));
                    $callable($connection);
                    $this->assertSame($count, Context::get(PDOStub2::class . '::destruct', 0));
                    $closure($connection);
                    $this->assertSame(++$count, Context::get(PDOStub2::class . '::destruct', 0));
                }
            }
        }, 10);
    }

    public function testConnectionSticky()
    {
        $container = ContainerStub::mockReadWriteContainer();

        parallel([
            function () use ($container) {
                $resolver = $container->get(ConnectionResolverInterface::class);

                /** @var \Hyperf\Database\Connection $connection */
                $connection = $resolver->connection();
                $connection->statement('UPDATE hyperf.test SET name = 1 WHERE id = 1;');

                /** @var PDOStub $pdo */
                $pdo = $connection->getPdo();
                $this->assertSame('mysql:host=192.168.1.2;dbname=hyperf', $pdo->dsn);
                $pdo = $connection->getReadPdo();
                $this->assertSame('mysql:host=192.168.1.2;dbname=hyperf', $pdo->dsn);
            },
        ]);

        parallel([
            function () use ($container) {
                $resolver = $container->get(ConnectionResolverInterface::class);

                /** @var \Hyperf\Database\Connection $connection */
                $connection = $resolver->connection();

                /** @var PDOStub $pdo */
                $pdo = $connection->getPdo();
                $this->assertSame('mysql:host=192.168.1.2;dbname=hyperf', $pdo->dsn);
                $pdo = $connection->getReadPdo();
                $this->assertSame('mysql:host=192.168.1.1;dbname=hyperf', $pdo->dsn);
            },
        ]);
    }

    public function testDbConnectionUseInDefer()
    {
        $container = ContainerStub::mockReadWriteContainer();

        parallel([
            function () use ($container) {
                $resolver = $container->get(ConnectionResolverInterface::class);

                defer(function () {
                    $this->assertFalse(Context::has('database.connection.default'));
                });
                $resolver->connection();
            },
        ]);
    }

    public function testDbConnectionResetWhenThrowTooManyExceptions()
    {
        $container = ContainerStub::mockContainer();
        $pool = $container->get(PoolFactory::class)->getPool('default');
        $dbConnection = $pool->get();
        $connection = $dbConnection->getConnection();
        $this->assertSame(0, $connection->getErrorCount());
        $id = spl_object_hash((new ClassInvoker($connection))->connection);

        $dbConnection->release();
        $dbConnection = $pool->get();
        $connection = $dbConnection->getConnection();
        $id2 = spl_object_hash((new ClassInvoker($connection))->connection);

        $this->assertSame($id, $id2);

        $invoker = new ClassInvoker($connection);
        for ($i = 0; $i < 101; ++$i) {
            try {
                (new ClassInvoker($invoker->connection))->runQueryCallback('', [], fn () => throw new Exception('xxx'));
            } catch (QueryException) {
            }
        }
        $this->assertSame(101, $connection->getErrorCount());

        $dbConnection->release();
        $dbConnection = $pool->get();
        $connection = $dbConnection->getConnection();
        $id3 = spl_object_hash((new ClassInvoker($connection))->connection);
        $this->assertNotSame($id, $id3);
    }
}
