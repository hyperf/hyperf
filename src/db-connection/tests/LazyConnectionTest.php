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
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Query\Expression;
use Hyperf\Database\Query\Grammars\MySqlGrammar;
use Hyperf\Database\Query\Processors\Processor;
use Hyperf\DbConnection\Connection;
use Hyperf\DbConnection\LazyConnection;
use Hyperf\DbConnection\Pool\PoolFactory;
use HyperfTest\DbConnection\Stubs\ContainerStub;
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
class LazyConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set('database.connection.default', null);
        Register::unsetConnectionResolver();
    }

    public function testMetadataMethodsDoNotOccupyPool()
    {
        $container = ContainerStub::mockLazyContainer();
        $pool = $container->get(PoolFactory::class)->getPool('default');
        $resolver = $container->get(ConnectionResolverInterface::class);

        $connection = $resolver->connection();
        $this->assertInstanceOf(LazyConnection::class, $connection);
        $this->assertSame(0, $pool->getCurrentConnections());

        // Metadata methods are answered by a lightweight connection,
        // without occupying a pooled connection.
        $this->assertInstanceOf(MySqlGrammar::class, $connection->getQueryGrammar());
        $this->assertInstanceOf(Processor::class, $connection->getPostProcessor());
        $this->assertInstanceOf(Expression::class, $connection->raw('1'));
        $this->assertSame('default', $connection->getName());
        $this->assertSame('mysql', $connection->getDriverName());
        $this->assertSame('mysql', $connection->getConfig('driver'));
        $this->assertSame('hyperf', $connection->getDatabaseName());
        $this->assertSame('', $connection->getTablePrefix());
        $this->assertSame(0, $pool->getCurrentConnections());

        // Building a query does not occupy a pooled connection either.
        $sql = $connection->table('user')->where('id', 1)->toSql();
        $this->assertSame('select * from `user` where `id` = ?', $sql);
        $this->assertSame(0, $pool->getCurrentConnections());

        // The lazy proxy is cached in the context.
        $this->assertSame($connection, $resolver->connection());
    }

    public function testExecutionResolvesPoolConnectionOnce()
    {
        $container = ContainerStub::mockLazyContainer();
        $pool = $container->get(PoolFactory::class)->getPool('default');
        $resolver = $container->get(ConnectionResolverInterface::class);

        $connection = $resolver->connection();
        $this->assertInstanceOf(LazyConnection::class, $connection);

        // The first execution resolves the pooled connection.
        $this->assertSame([], $connection->select('SELECT 1;'));
        $this->assertSame(1, $pool->getCurrentConnections());

        // The resolution is memoized, following executions reuse it.
        $connection->select('SELECT 1;');
        $connection->table('user')->where('id', 1)->get();
        $this->assertSame(1, $pool->getCurrentConnections());

        // The context was swapped to the real connection.
        $real = Context::get('database.connection.default');
        $this->assertInstanceOf(Connection::class, $real);
        $this->assertSame($real, $resolver->connection());
        $this->assertSame($real, $connection->getConnection());
        $this->assertSame($real, $resolver->resolveConnection('default'));
    }

    public function testModelQueryIsLazyUntilExecution()
    {
        $container = ContainerStub::mockLazyContainer();
        $pool = $container->get(PoolFactory::class)->getPool('default');

        $sql = FooModel::query()->where('id', 1)->toSql();
        $this->assertSame('select * from `foo_models` where `id` = ?', $sql);
        $this->assertSame(0, $pool->getCurrentConnections());

        FooModel::query()->where('id', 1)->get();
        $this->assertSame(1, $pool->getCurrentConnections());
    }

    public function testReleaseWithoutResolveDoesNothing()
    {
        $container = ContainerStub::mockLazyContainer();
        $pool = $container->get(PoolFactory::class)->getPool('default');
        $resolver = $container->get(ConnectionResolverInterface::class);

        $connection = $resolver->connection();
        $this->assertInstanceOf(LazyConnection::class, $connection);
        $connection->release();
        $this->assertSame(0, $pool->getCurrentConnections());
    }

    public function testReleaseWhenCoroutineDestruct()
    {
        $container = ContainerStub::mockLazyContainer();
        $pool = $container->get(PoolFactory::class)->getPool('default');

        $ids = [];
        parallel([
            function () use ($container, $pool, &$ids) {
                $resolver = $container->get(ConnectionResolverInterface::class);
                $connection = $resolver->connection();
                $this->assertInstanceOf(LazyConnection::class, $connection);
                defer(function () {
                    $this->assertFalse(Context::has('database.connection.default'));
                });
                $connection->select('SELECT 1;');
                $this->assertSame(1, $pool->getCurrentConnections());
                $ids[0] = spl_object_id($connection->getConnection());
            },
        ]);

        // The connection was released back to the pool when the coroutine destructed,
        // so a new coroutine reuses the very same pooled connection.
        parallel([
            function () use ($container, $pool, &$ids) {
                $resolver = $container->get(ConnectionResolverInterface::class);
                $connection = $resolver->connection();
                $this->assertInstanceOf(LazyConnection::class, $connection);
                $connection->select('SELECT 1;');
                $ids[1] = spl_object_id($connection->getConnection());
                $this->assertSame(1, $pool->getCurrentConnections());
            },
        ]);

        $this->assertSame($ids[0], $ids[1]);
        $this->assertSame(1, $pool->getCurrentConnections());
    }

    public function testSelectReleaseConnectionAfterUse()
    {
        $container = ContainerStub::mockLazyContainer(releaseAfterUse: true);
        $pool = $container->get(PoolFactory::class)->getPool('default');
        $resolver = $container->get(ConnectionResolverInterface::class);

        $connection = $resolver->connection();
        $this->assertInstanceOf(LazyConnection::class, $connection);

        // Released right after the query finished.
        $connection->select('SELECT 1;');
        $this->assertNull($connection->getResolvedConnection());
        $this->assertSame(1, $pool->getCurrentConnections());

        // The context always holds the lazy proxy itself in release-after-use mode.
        $this->assertSame($connection, Context::get('database.connection.default'));

        // The next query resolves from the pool again, and the released
        // connection in the pool is reused.
        $connection->select('SELECT 1;');
        $this->assertNull($connection->getResolvedConnection());
        $this->assertSame(1, $pool->getCurrentConnections());

        // update() is released as well when sticky is not configured.
        $connection->update('UPDATE user SET name = ? WHERE id = ?', ['hyperf', 1]);
        $this->assertNull($connection->getResolvedConnection());
    }

    public function testInsertDoesNotReleaseAfterUse()
    {
        $container = ContainerStub::mockLazyContainer(releaseAfterUse: true);
        $resolver = $container->get(ConnectionResolverInterface::class);

        /** @var LazyConnection $connection */
        $connection = $resolver->connection();

        // insert() never releases: Processor::processInsertGetId() performs
        // insert() and getPdo()->lastInsertId() as two separate calls which
        // must share one and the same connection.
        $connection->insert('INSERT INTO user (name) VALUES (?)', ['hyperf']);
        $real = $connection->getResolvedConnection();
        $this->assertInstanceOf(Connection::class, $real);
        $connection->getPdo();
        $this->assertSame($real, $connection->getResolvedConnection());
    }

    public function testTransactionHoldAndCommitRelease()
    {
        $container = ContainerStub::mockLazyContainer(releaseAfterUse: true);
        $resolver = $container->get(ConnectionResolverInterface::class);

        /** @var LazyConnection $connection */
        $connection = $resolver->connection();
        $this->assertSame(0, $connection->transactionLevel());
        $this->assertFalse($connection->isTransaction());

        $connection->beginTransaction();
        $real = $connection->getResolvedConnection();
        $this->assertInstanceOf(Connection::class, $real);
        $this->assertSame(1, $connection->transactionLevel());
        $this->assertTrue($connection->isTransaction());

        // The connection is held during the transaction.
        $connection->select('SELECT 1;');
        $this->assertSame($real, $connection->getResolvedConnection());

        // Released after the transaction is committed.
        $connection->commit();
        $this->assertSame(0, $connection->transactionLevel());
        $this->assertNull($connection->getResolvedConnection());

        // The same goes for transaction(Closure).
        $connection->transaction(function () use ($connection) {
            $connection->select('SELECT 1;');
        });
        $this->assertNull($connection->getResolvedConnection());
    }

    public function testWriteHoldOnStickyConnection()
    {
        $container = ContainerStub::mockLazyStickyContainer();
        $resolver = $container->get(ConnectionResolverInterface::class);

        /** @var LazyConnection $connection */
        $connection = $resolver->connection();

        // With sticky read/write config, the connection is held after a write,
        // so following reads still hit the write PDO.
        $connection->update('UPDATE user SET name = ? WHERE id = ?', ['hyperf', 1]);
        $this->assertInstanceOf(Connection::class, $connection->getResolvedConnection());

        $connection->select('SELECT 1;');
        $this->assertInstanceOf(Connection::class, $connection->getResolvedConnection());
    }

    public function testCursorReleaseAfterIteration()
    {
        $container = ContainerStub::mockLazyContainer(releaseAfterUse: true);
        $resolver = $container->get(ConnectionResolverInterface::class);

        /** @var LazyConnection $connection */
        $connection = $resolver->connection();

        $cursor = $connection->cursor('SELECT 1;');
        // Nothing is resolved before the generator is iterated.
        $this->assertNull($connection->getResolvedConnection());

        foreach ($cursor as $row) {
            // The stubbed statement returns no rows.
        }
        // Released after the generator is exhausted.
        $this->assertNull($connection->getResolvedConnection());
    }

    public function testQueryLogPreventsRelease()
    {
        $container = ContainerStub::mockLazyContainer(releaseAfterUse: true);
        $resolver = $container->get(ConnectionResolverInterface::class);

        /** @var LazyConnection $connection */
        $connection = $resolver->connection();

        $connection->enableQueryLog();
        $connection->select('SELECT 1;');
        // The connection is held while the query log is enabled,
        // so getQueryLog() keeps working.
        $this->assertInstanceOf(Connection::class, $connection->getResolvedConnection());
        $this->assertCount(1, $connection->getQueryLog());

        $connection->disableQueryLog();
        $connection->select('SELECT 1;');
        $this->assertNull($connection->getResolvedConnection());
    }

    public function testReleaseAfterUseInCoroutine()
    {
        $container = ContainerStub::mockLazyContainer(releaseAfterUse: true);
        $pool = $container->get(PoolFactory::class)->getPool('default');

        $ids = [];
        foreach ([0, 1] as $i) {
            parallel([
                function () use ($container, $pool, &$ids, $i) {
                    $resolver = $container->get(ConnectionResolverInterface::class);
                    $connection = $resolver->connection();
                    $this->assertInstanceOf(LazyConnection::class, $connection);
                    defer(function () {
                        $this->assertFalse(Context::has('database.connection.default'));
                    });

                    // Read queries are released right after use.
                    $connection->select('SELECT 1;');
                    $this->assertNull($connection->getResolvedConnection());

                    // A transaction holds the connection, commit releases it.
                    $connection->beginTransaction();
                    $real = $connection->getResolvedConnection();
                    $this->assertInstanceOf(Connection::class, $real);
                    $connection->select('SELECT 1;');
                    $this->assertSame($real, $connection->getResolvedConnection());
                    $connection->commit();
                    $this->assertNull($connection->getResolvedConnection());

                    $ids[$i] = spl_object_id($real);
                    $this->assertSame(1, $pool->getCurrentConnections());
                },
            ]);
        }

        // Both coroutines reused the very same pooled connection, which proves
        // the mid-coroutine releases and the coroutine-end defer never released
        // one connection twice.
        $this->assertSame($ids[0], $ids[1]);
        $this->assertSame(1, $pool->getCurrentConnections());
    }
}
