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
use Hyperf\Database\Connection as DatabaseConnection;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Register;
use Hyperf\DbConnection\ConnectionResolver;
use Hyperf\DbConnection\LazyConnection;
use Hyperf\DbConnection\Pool\DbPool;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Engine\Channel;
use HyperfTest\DbConnection\Stubs\ContainerStub;
use Mockery;
use PDO;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function Hyperf\Coroutine\defer;
use function Hyperf\Coroutine\parallel;

/**
 * Real MySQL checks, enabled explicitly with HYPERF_MYSQL_DATABASE.
 * Each test owns a randomly named table and removes it in tearDown().
 * @internal
 */
#[CoversNothing]
#[Group('mysql-integration')]
class MySqlConnectionLifecycleTest extends TestCase
{
    private ?ContainerInterface $container = null;

    private ?LazyConnection $connection = null;

    private string $table;

    private bool $tableCreated = false;

    protected function setUp(): void
    {
        $database = getenv('HYPERF_MYSQL_DATABASE');
        if ($database === false || $database === '') {
            $this->markTestSkipped('Set HYPERF_MYSQL_DATABASE to enable real MySQL integration tests.');
        }
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('PDO MySQL is required.');
        }

        Context::destroy('database.connection.default');
        Context::destroy('database.release_after_use.default');
        $this->table = 'hyperf_pr7819_' . bin2hex(random_bytes(6));
        $this->container = ContainerStub::mockContainer();
        $config = $this->container->get(ConfigInterface::class);
        $config->set('databases.default', [
            'driver' => 'mysql',
            'host' => getenv('HYPERF_MYSQL_HOST') ?: '127.0.0.1',
            'port' => (int) (getenv('HYPERF_MYSQL_PORT') ?: 3306),
            'database' => $database,
            'username' => getenv('HYPERF_MYSQL_USER') ?: 'root',
            'password' => getenv('HYPERF_MYSQL_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'lazy' => true,
            'release_after_use' => true,
            'options' => [PDO::ATTR_EMULATE_PREPARES => false],
            'pool' => ['min_connections' => 1, 'max_connections' => 3, 'wait_timeout' => 2.0],
        ]);
        $this->connection = $this->resolver()->connection();
    }

    protected function tearDown(): void
    {
        if ($this->container === null) {
            return;
        }
        DatabaseConnection::clearBeforeExecutingCallbacks();
        try {
            if ($this->connection->transactionLevel() > 0) {
                $this->connection->rollBack(0);
            }
            $this->connection->close();
            if ($this->tableCreated) {
                $cleanup = $this->resolver()->connection();
                try {
                    $cleanup->statement("DROP TABLE IF EXISTS `{$this->table}`");
                } finally {
                    $cleanup->close();
                }
            }
        } finally {
            $this->container->get(PoolFactory::class)->flushAll();
            Context::destroy('database.connection.default');
            Context::destroy('database.release_after_use.default');
            Register::unsetConnectionResolver();
            Register::unsetEventDispatcher();
            Mockery::close();
        }
    }

    public function testMetadataCompilationDoesNotAcquireAConnection(): void
    {
        $this->assertSame('select * from `users`', $this->connection->table('users')->toSql());
        $this->assertSame(0, $this->pool()->getCurrentConnections());
        $this->assertNull($this->connection->getResolvedConnection());
    }

    public function testInsertIdsSurviveAlternatingPhysicalConnections(): void
    {
        $this->createTable();
        $this->prewarmTwoConnections();
        $first = $this->connection->table($this->table)->insertGetId(['value' => 11]);
        $this->assertNull($this->connection->getResolvedConnection());
        $second = $this->connection->table($this->table)->insertGetId(['value' => 22]);
        $this->assertSame($first + 1, $second);
        $this->assertSame(11, (int) $this->connection->table($this->table)->where('id', $first)->first()->value);
        $this->assertSame(22, (int) $this->connection->table($this->table)->where('id', $second)->first()->value);
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertSame(2, $this->pool()->getConnectionsInChannel());
    }

    public function testNestedSavepointsAndRollbackToZero(): void
    {
        $this->createTable();
        $this->connection->beginTransaction();
        $this->connection->table($this->table)->insert(['value' => 1]);
        $this->connection->beginTransaction();
        $this->connection->table($this->table)->insert(['value' => 2]);
        $this->connection->beginTransaction();
        $this->assertSame(3, $this->connection->transactionLevel());
        $this->connection->rollBack();
        $this->assertSame(2, $this->connection->transactionLevel());
        $this->connection->rollBack();
        $this->assertSame(1, $this->connection->transactionLevel());
        $this->assertSame(1, (int) $this->connection->table($this->table)->count());
        $this->connection->rollBack(0);
        $this->assertSame(0, $this->connection->transactionLevel());
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertSame(0, (int) $this->connection->table($this->table)->count());
    }

    public function testTransactionExceptionRollsBackBeforeReturn(): void
    {
        $this->createTable();
        try {
            $this->connection->transaction(function ($connection) {
                $this->assertSame($this->connection, $connection);
                $connection->table($this->table)->insert(['value' => 1]);
                throw new RuntimeException('rollback this operation');
            });
            $this->fail('The transaction callback must throw.');
        } catch (RuntimeException $exception) {
            $this->assertSame('rollback this operation', $exception->getMessage());
        }
        $this->assertSame(0, $this->connection->transactionLevel());
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertSame(0, (int) $this->connection->table($this->table)->count());
    }

    public function testPretendNeverWritesAcrossTwoPooledConnections(): void
    {
        $this->createTable();
        $this->prewarmTwoConnections();
        $log = $this->connection->pretend(function ($connection) {
            $connection->table($this->table)->insert(['value' => 1]);
            $this->connection->table($this->table)->insert(['value' => 2]);
        });
        $this->assertCount(2, $log);
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertSame(0, (int) $this->connection->table($this->table)->count());
        $this->connection->table($this->table)->insert(['value' => 3]);
        $this->assertSame(1, (int) $this->connection->table($this->table)->count());
    }

    public function testStickyReadsUseTheWritePdoAfterInsertId(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->set('databases.default.sticky', true);
        // Both PDOs use this server: CONNECTION_ID proves routing, not replication.
        $config->set('databases.default.read', ['host' => $config->get('databases.default.host')]);
        $config->set('databases.default.write', ['host' => $config->get('databases.default.host')]);
        $this->createTable();
        $this->connection->resetRecordsModified();
        $ids = $this->connection->runOperation(fn ($driver) => [
            (int) $driver->getPdo()->query('SELECT CONNECTION_ID()')->fetchColumn(),
            (int) $driver->getReadPdo()->query('SELECT CONNECTION_ID()')->fetchColumn(),
        ]);
        $this->assertNotSame($ids[0], $ids[1]);
        $this->connection->table($this->table)->insertGetId(['value' => 7]);
        $held = $this->connection->getResolvedConnection();
        $this->assertNotNull($held);
        $this->assertSame($ids[0], (int) $this->connection->scalar('SELECT CONNECTION_ID()'));
        $this->assertSame($ids[0], (int) $this->connection->scalar('SELECT CONNECTION_ID()'));
        $this->assertSame($held, $this->connection->getResolvedConnection());
        $this->connection->resetRecordsModified();
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertSame($ids[1], (int) $this->connection->scalar('SELECT CONNECTION_ID()'));
    }

    public function testExplicitScopeKeepsMysqlSessionVariablesOnOneConnection(): void
    {
        $this->prewarmTwoConnections();
        $this->connection->withConnection(function ($connection) {
            $id = (int) $connection->scalar('SELECT CONNECTION_ID()');
            $connection->statement('SET @hyperf_pr7819_value = 42');
            try {
                $this->assertSame(42, (int) $connection->scalar('SELECT @hyperf_pr7819_value'));
                $this->assertSame($id, (int) $connection->scalar('SELECT CONNECTION_ID()'));
            } finally {
                $connection->statement('SET @hyperf_pr7819_value = NULL');
            }
        });
        $this->assertNull($this->connection->getResolvedConnection());
    }

    public function testRawTemporaryTableCannotReachTheNextBorrower(): void
    {
        $id = $this->connection->withConnection(function ($connection) {
            $pdo = $connection->getPdo();
            $pdo->exec("CREATE TEMPORARY TABLE `{$this->table}` (id INT)");
            return (int) $pdo->query('SELECT CONNECTION_ID()')->fetchColumn();
        });
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertNotSame($id, (int) $this->connection->scalar('SELECT CONNECTION_ID()'));
        $this->connection->statement("CREATE TEMPORARY TABLE `{$this->table}` (id INT)");
        $this->connection->statement("DROP TEMPORARY TABLE `{$this->table}`");
        $this->assertNull($this->connection->getResolvedConnection());
    }

    public function testUnbufferedCursorDestructionMakesThePoolSlotUsable(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->set('databases.default.options', [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
        ]);
        $config->set('databases.default.pool.max_connections', 1);
        $cursor = $this->connection->cursor('SELECT 1 AS value UNION ALL SELECT 2 AS value');
        $cursor->rewind();
        $this->assertSame(1, (int) $cursor->current()->value);
        $this->assertNotNull($this->connection->getResolvedConnection());
        unset($cursor);
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertSame(3, (int) $this->connection->scalar('SELECT 3'));
        $this->assertSame(1, $this->pool()->getConnectionsInChannel());
    }

    public function testCoroutineCleanupRollsBackAnUnfinishedTransaction(): void
    {
        $this->createTable();
        $done = new Channel(1);
        parallel([function () use ($done) {
            defer(fn () => $done->push(true));
            $connection = $this->resolver()->connection();
            $connection->beginTransaction();
            $connection->table($this->table)->insert(['value' => 1]);
        }]);
        $done->pop();
        $this->assertSame(0, (int) $this->connection->table($this->table)->count());
        $this->assertSame(1, $this->pool()->getCurrentConnections());
        $this->assertSame(1, $this->pool()->getConnectionsInChannel());
    }

    private function createTable(): void
    {
        $this->connection->statement("CREATE TABLE `{$this->table}` (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, value INT NOT NULL) ENGINE=InnoDB");
        $this->tableCreated = true;
    }

    private function prewarmTwoConnections(): void
    {
        $first = $this->resolver()->acquireLease('default');
        $second = $this->resolver()->acquireLease('default');
        try {
            $first->getDatabaseConnection()->select('SELECT 1');
            $second->getDatabaseConnection()->select('SELECT 1');
        } finally {
            $first->release();
            $second->release();
        }
    }

    private function resolver(): ConnectionResolver
    {
        return $this->container->get(ConnectionResolverInterface::class);
    }

    private function pool(): DbPool
    {
        return $this->container->get(PoolFactory::class)->getPool('default');
    }
}
