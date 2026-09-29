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
}
