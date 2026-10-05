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
use Hyperf\Database\ConnectionMetadata;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectorInterface;
use Hyperf\Database\MySqlConnection;
use Hyperf\Database\PgSQL\Listener\RegisterConnectionListener;
use Hyperf\Database\Query\Grammars\MySqlGrammar;
use Hyperf\Database\Query\Processors\MySqlProcessor;
use Hyperf\Database\Sqlsrv\Query\SqlServerBuilder;
use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\LazyConnection;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Engine\Channel;
use HyperfTest\DbConnection\Stubs\LifecycleContainer;
use HyperfTest\DbConnection\Stubs\PDOStatementStubPHP8;
use HyperfTest\DbConnection\Stubs\PDOStub;
use LogicException;
use Mockery;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

use function Hyperf\Coroutine\defer;
use function Hyperf\Coroutine\parallel;

/** @internal */
#[CoversNothing]
class LazyConnectionLifecycleTest extends TestCase
{
    private LifecycleContainer $container;

    private LazyConnection $connection;

    protected function setUp(): void
    {
        Context::destroy('database.connection.default');
        $this->container = new LifecycleContainer();
        $this->connection = $this->container->get(ConnectionResolverInterface::class)->connection();
    }

    protected function tearDown(): void
    {
        DatabaseConnection::clearBeforeExecutingCallbacks();
        $this->connection->close();
        $this->container->get(PoolFactory::class)->flushAll();
        unlink($this->container->path);
        Context::destroy('database.connection.default');
        Context::destroy('database.release_after_use.default');
        Mockery::close();
    }

    public function testSQLiteMetadataDoesNotOpenPdo(): void
    {
        $this->assertSame('select * from "users"', $this->connection->table('users')->toSql());
        $this->assertSame(0, $this->container->opens);
        $this->assertSame(0, $this->pool()->getCurrentConnections());
        $this->connection->select('SELECT 1');
        $this->assertSame(1, $this->container->opens);
        $this->assertNull($this->connection->getResolvedConnection());
    }

    public function testInsertIdIsReadBeforeReturningTheLease(): void
    {
        $this->prewarmTwoConnections();
        $this->connection->statement('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
        $this->assertSame(1, $this->connection->table('users')->insertGetId(['name' => 'first']));
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertSame(2, $this->connection->table('users')->insertGetId(['name' => 'second']));
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertSame(2, $this->connection->scalar('SELECT COUNT(*) FROM users'));
    }

    public function testPretendCannotChangeLeaseMidCallback(): void
    {
        $this->prewarmTwoConnections();
        $this->connection->statement('CREATE TABLE writes (id INTEGER)');
        $log = $this->connection->pretend(function ($connection) {
            $this->assertSame($this->connection, $connection);
            $connection->statement('INSERT INTO writes VALUES (1)');
            $real = $connection->getResolvedConnection();
            $this->connection->statement('INSERT INTO writes VALUES (2)');
            $this->assertSame($real, $connection->getResolvedConnection());
        });
        $this->assertCount(2, $log);
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertSame(0, $this->connection->scalar('SELECT COUNT(*) FROM writes'));
    }

    public function testNestedPretendAndExceptionRestorePhysicalState(): void
    {
        $this->connection->statement('CREATE TABLE writes (id INTEGER)');
        $log = $this->connection->pretend(function ($connection) {
            $connection->statement('INSERT INTO writes VALUES (1)');
            $connection->pretend(fn ($nested) => $nested->statement('INSERT INTO writes VALUES (2)'));
            $connection->statement('INSERT INTO writes VALUES (3)');
        });
        $this->assertCount(2, $log);
        try {
            $this->connection->pretend(function ($connection) {
                $connection->statement('INSERT INTO writes VALUES (4)');
                throw new RuntimeException('stop');
            });
            $this->fail('The callback exception must be preserved.');
        } catch (RuntimeException $exception) {
            $this->assertSame('stop', $exception->getMessage());
        }
        $this->connection->statement('INSERT INTO writes VALUES (5)');
        $this->assertSame(1, $this->connection->scalar('SELECT COUNT(*) FROM writes'));
        $this->assertFalse($this->connection->pretending());
    }

    public function testExecutionHookCannotReturnTheOuterLease(): void
    {
        $outer = null;
        DatabaseConnection::beforeExecuting(function ($sql) use (&$outer) {
            if ($sql !== 'SELECT 2') {
                return;
            }
            $outer = $this->connection->getResolvedConnection();
            $this->connection->select('SELECT 1');
            $this->assertSame($outer, $this->connection->getResolvedConnection());
            $competitor = $this->pool()->get();
            $this->assertNotSame($outer, $competitor);
            $competitor->beginTransaction();
            $competitor->rollBack();
            $competitor->release();
        });
        $this->connection->select('SELECT 2');
        $this->assertNotNull($outer);
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertSame(2, $this->pool()->getConnectionsInChannel());
    }

    public function testSchemaHandlePinsItsConnection(): void
    {
        $schema = $this->connection->getSchemaBuilder();
        $real = $this->connection->getResolvedConnection();
        $this->connection->select('SELECT 1');
        $this->assertSame($real, $this->connection->getResolvedConnection());
        $competitor = $this->pool()->get();
        $this->assertNotSame($real, $competitor);
        $competitor->beginTransaction();
        $schema->create('escaped_table', fn ($table) => $table->integer('id'));
        $competitor->rollBack();
        $competitor->release();
        $this->assertSame(1, $this->connection->scalar("SELECT COUNT(*) FROM sqlite_master WHERE name='escaped_table'"));
    }

    public function testExplicitScopeReleasesHandlesAndRestoresAfterException(): void
    {
        try {
            Db::withConnection(function ($connection) {
                $pdo = $connection->getPdo();
                $this->assertSame($pdo, $connection->getPdo());
                $connection->select('SELECT 1');
                $this->assertNotNull($connection->getResolvedConnection());
                try {
                    $connection->release();
                    $this->fail('Active scopes must not be released.');
                } catch (LogicException) {
                }
                throw new RuntimeException('scope failed');
            });
        } catch (RuntimeException $exception) {
            $this->assertSame('scope failed', $exception->getMessage());
        }
        $this->assertNull($this->connection->getResolvedConnection());
    }

    public function testSessionLogsSurviveMultiplePhysicalConnections(): void
    {
        $this->prewarmTwoConnections();
        $this->connection->enableQueryLog();
        $this->connection->select('SELECT 1');
        $this->connection->select('SELECT 2');
        $this->assertNull($this->connection->getResolvedConnection());
        $this->connection->disableQueryLog();
        $this->assertSame(['SELECT 1', 'SELECT 2'], array_column($this->connection->getQueryLog(), 'query'));
        $lease = $this->container->get(ConnectionResolverInterface::class)->acquireLease('default');
        $this->assertFalse($lease->getDatabaseConnection()->logging());
        $this->assertSame([], $lease->getDatabaseConnection()->getQueryLog());
        $lease->release();
    }

    public function testMetadataSettersKeepTheLogicalEntryAndResetPoolSettings(): void
    {
        $this->assertSame($this->connection, $this->connection->setTablePrefix('tenant_'));
        $this->assertSame('tenant_', $this->connection->getTablePrefix());
        $this->assertSame('select * from "tenant_users"', $this->connection->table('users')->toSql());
        $this->connection->statement('CREATE TABLE tenant_users (id INTEGER)');
        $this->connection->table('users')->insert(['id' => 7]);
        $this->assertSame(7, $this->connection->table('users')->first()->id);
        $lease = $this->container->get(ConnectionResolverInterface::class)->acquireLease('default');
        $this->assertSame('', $lease->getDatabaseConnection()->getTablePrefix());
        $lease->release();
    }

    public function testScopedPolicyIsNestedAndRestoredAfterException(): void
    {
        try {
            Db::withReleaseAfterUse(false, function ($connection) {
                $connection->select('SELECT 1');
                $real = $connection->getResolvedConnection();
                Db::withReleaseAfterUse(true, function ($nested) use ($connection) {
                    $this->assertSame($connection, $nested);
                    $nested->select('SELECT 2');
                    $this->assertNull($nested->getResolvedConnection());
                });
                $connection->select('SELECT 3');
                $this->assertNotNull($real);
                $this->assertNotNull($connection->getResolvedConnection());
                throw new RuntimeException('policy failed');
            });
        } catch (RuntimeException $exception) {
            $this->assertSame('policy failed', $exception->getMessage());
        }
        $this->assertNull($this->connection->getResolvedConnection());
        $this->connection->select('SELECT 4');
        $this->assertNull($this->connection->getResolvedConnection());
        $this->assertFalse(Context::has('database.release_after_use.default'));
    }

    public function testLeaseCanOnlyBeReturnedOnce(): void
    {
        $resolver = $this->container->get(ConnectionResolverInterface::class);
        $lease = $resolver->acquireLease('default');
        $lease->release();
        $lease->release();
        $first = $resolver->acquireLease('default');
        $second = $resolver->acquireLease('default');
        $this->assertNotSame($first->getConnection(), $second->getConnection());
        $first->release();
        $second->release();
        $this->assertSame(2, $this->pool()->getCurrentConnections());
        $this->assertSame(2, $this->pool()->getConnectionsInChannel());
    }

    public function testCleanupRejectsCapturedSessionReacquisition(): void
    {
        $errors = 0;
        foreach ([false, true] as $registerBeforeCreation) {
            $done = new Channel(1);
            parallel([function () use ($registerBeforeCreation, &$errors, $done) {
                defer(fn () => $done->push(true));
                $connection = null;
                $lateQuery = function () use (&$connection, &$errors) {
                    try {
                        $connection->select('SELECT 1');
                    } catch (LogicException) {
                        ++$errors;
                    }
                };
                if ($registerBeforeCreation) {
                    defer($lateQuery);
                }
                $connection = $this->container->get(ConnectionResolverInterface::class)->connection();
                if (! $registerBeforeCreation) {
                    defer($lateQuery);
                }
                $connection->withReleaseAfterUse(false, fn ($db) => $db->select('SELECT 1'));
            }]);
            $done->pop();
        }
        $this->assertSame(1, $errors);
        $this->assertSame(1, $this->pool()->getCurrentConnections());
        $this->assertSame(1, $this->pool()->getConnectionsInChannel());
    }

    public function testFreshResolverSessionWorksInLateDefer(): void
    {
        parallel([function () {
            defer(function () {
                $this->container->get(ConnectionResolverInterface::class)->connection()->select('SELECT 1');
            });
            $this->container->get(ConnectionResolverInterface::class)->connection()->select('SELECT 2');
        }]);
        $this->assertSame(1, $this->pool()->getCurrentConnections());
        $this->assertSame(1, $this->pool()->getConnectionsInChannel());
    }

    public function testCursorBreakKeepsLeaseUntilGeneratorDestruction(): void
    {
        $this->connection->statement('CREATE TABLE users (id INTEGER)');
        $this->connection->statement('INSERT INTO users VALUES (1), (2)');
        $cursor = $this->connection->cursor('SELECT id FROM users');
        $this->assertNull($this->connection->getResolvedConnection());
        foreach ($cursor as $row) {
            $this->assertSame(1, $row->id);
            $this->connection->select('SELECT 1');
            break;
        }
        $this->assertNotNull($this->connection->getResolvedConnection());
        unset($cursor);
        $this->assertNull($this->connection->getResolvedConnection());
    }

    public function testPostgresInsertGetIdKeepsStickyState(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->set('databases.default', [
            'driver' => 'pgsql', 'database' => 'review', 'prefix' => '',
            'read' => ['host' => 'replica'], 'write' => ['host' => 'primary'],
            'sticky' => true, 'lazy' => true, 'release_after_use' => true,
        ]);
        $this->container->entries['db.connector.pgsql'] = new class implements ConnectorInterface {
            public function connect(array $config)
            {
                return new class('pgsql:host=' . $config['host']) extends PDOStub {
                    public function prepare(string $query, array $options = []): bool|PDOStatement
                    {
                        return new class($query) extends PDOStatementStubPHP8 {
                            public function fetchAll(int $mode = PDO::FETCH_BOTH, mixed ...$args): array
                            {
                                return str_contains($this->statement, 'returning') ? [(object) ['id' => 17]] : [];
                            }
                        };
                    }
                };
            }
        };
        (new RegisterConnectionListener($this->container))->process(new stdClass());
        $this->assertSame(17, $this->connection->table('users')->insertGetId(['name' => 'review']));
        $real = $this->connection->getResolvedConnection();
        $this->connection->select('SELECT 1');
        $this->connection->select('SELECT 2');
        $this->assertSame($real, $this->connection->getResolvedConnection());
        $this->assertSame('pgsql:host=primary', $real->getReadPdo()->dsn);
    }

    public function testReadOnlyStickyTransactionDoesNotPinAfterCommit(): void
    {
        $this->container->get(ConfigInterface::class)->set('databases.default.sticky', true);
        $this->connection->transaction(function ($connection) {
            $this->assertSame($this->connection, $connection);
            $connection->select('SELECT 1');
        });
        $this->assertNull($this->connection->getResolvedConnection());
    }

    public function testCustomDriverWithoutMetadataUsesPinnedRealSettings(): void
    {
        DatabaseConnection::resolverFor('review-custom', static fn ($pdo, $database, $prefix, $config) => new MySqlConnection($pdo, $database, $prefix, $config));
        $this->container->get(ConfigInterface::class)->set('databases.default.driver', 'review-custom');
        $this->container->entries['db.connector.review-custom'] = new class implements ConnectorInterface {
            public function connect(array $config)
            {
                return new PDOStub('custom');
            }
        };
        $this->assertInstanceOf(MySqlGrammar::class, $this->connection->getQueryGrammar());
        $this->connection->select('SELECT 1');
        $this->assertNotNull($this->connection->getResolvedConnection());
    }

    public function testScopePoliciesDoNotAffectOtherCoroutines(): void
    {
        $ready = new Channel(1);
        $done = new Channel(1);
        parallel([
            function () use ($ready, $done) {
                $this->container->get(ConnectionResolverInterface::class)->withReleaseAfterUse(false, function ($db) use ($ready, $done) {
                    $db->select('SELECT 1');
                    $held = $db->getResolvedConnection();
                    $ready->push(true);
                    $done->pop();
                    $this->assertSame($held, $db->getResolvedConnection());
                });
            },
            function () use ($ready, $done) {
                $ready->pop();
                $db = $this->container->get(ConnectionResolverInterface::class)->connection();
                $db->select('SELECT 2');
                $this->assertNull($db->getResolvedConnection());
                $done->push(true);
            },
        ]);
    }

    public function testSessionCannotBeSharedAcrossCoroutines(): void
    {
        $connection = $this->connection;
        parallel([function () use ($connection) {
            try {
                $connection->select('SELECT 1');
                $this->fail('A coroutine must not borrow through another session.');
            } catch (LogicException $exception) {
                $this->assertStringContainsString('another coroutine', $exception->getMessage());
            }
        }]);
        $this->assertSame(0, $this->pool()->getCurrentConnections());
    }

    public function testWorkerToggleDoesNotReplaceAnExistingEagerHandle(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->set('databases.default.lazy', false);
        $config->set('databases.default.release_after_use', false);
        $resolver = $this->container->get(ConnectionResolverInterface::class);
        $done = new Channel(1);
        parallel([function () use ($resolver, $done) {
            defer(fn () => $done->push(true));
            $eager = $resolver->connection();
            $resolver->setReleaseAfterUse('default', true);
            $this->assertSame($eager, $resolver->connection());
            try {
                $resolver->withReleaseAfterUse(true, fn ($db) => $db->select('SELECT 1'));
                $this->fail('Retained eager handles cannot be replaced safely.');
            } catch (LogicException $exception) {
                $this->assertStringContainsString('before obtaining an eager connection', $exception->getMessage());
            }
        }]);
        $done->pop();
        parallel([function () use ($resolver) {
            $db = $resolver->connection();
            $this->assertInstanceOf(LazyConnection::class, $db);
            $db->select('SELECT 1');
            $this->assertNull($db->getResolvedConnection());
        }]);
        $resolver->resetReleaseAfterUse('default');
    }

    public function testScopePolicyCanCreateALogicalConnectionWithoutLazyConfig(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->set('databases.default.lazy', false);
        $config->set('databases.default.release_after_use', false);
        parallel([function () {
            $this->container->get(ConnectionResolverInterface::class)->withReleaseAfterUse(true, function ($db) {
                $db->select('SELECT 1');
                $this->assertNull($db->getResolvedConnection());
            });
            $this->assertFalse(Context::has('database.release_after_use.default'));
        }]);
    }

    public function testAnAbandonedCursorInvalidatesItsPhysicalConnection(): void
    {
        $cursor = null;
        $pdo = null;
        $done = new Channel(1);
        parallel([function () use (&$cursor, &$pdo, $done) {
            defer(fn () => $done->push(true));
            $db = $this->container->get(ConnectionResolverInterface::class)->connection();
            $cursor = $db->cursor('SELECT 1 AS id UNION ALL SELECT 2 AS id');
            $cursor->rewind();
            $pdo = $db->getResolvedConnection()->getPdo();
        }]);
        $done->pop();
        $lease = $this->container->get(ConnectionResolverInterface::class)->acquireLease('default');
        $this->assertNotSame($pdo, $lease->getDatabaseConnection()->getPdo());
        $lease->release();
        unset($cursor);
    }

    public function testStaleLeaseCannotUseAReborrowedWrapper(): void
    {
        $resolver = $this->container->get(ConnectionResolverInterface::class);
        $stale = $resolver->acquireLease('default');
        $raw = $stale->getConnection();
        $raw->release();
        $raw->release();
        $current = $resolver->acquireLease('default');
        try {
            $stale->getConnection();
            $this->fail('A lease from an earlier generation must be invalid.');
        } catch (LogicException) {
            $this->assertSame($raw, $current->getConnection());
        }
        $current->release();
        $this->assertSame(1, $this->pool()->getConnectionsInChannel());
    }

    public function testSqlServerIdOperationSupportsNativeAndOdbc(): void
    {
        (new \Hyperf\Database\Sqlsrv\Listener\RegisterConnectionListener($this->container))->process(new stdClass());
        $this->container->entries['db.connector.sqlsrv'] = new class implements ConnectorInterface {
            public function connect(array $config)
            {
                return new class('sqlsrv:review') extends PDOStub {
                    public function lastInsertId(?string $name = null): false|string
                    {
                        return '42';
                    }

                    public function prepare(string $query, array $options = []): bool|PDOStatement
                    {
                        return new class($query) extends PDOStatementStubPHP8 {
                            public function fetchAll(int $mode = PDO::FETCH_BOTH, mixed ...$args): array
                            {
                                return [(object) ['insertid' => 42]];
                            }
                        };
                    }
                };
            }
        };
        foreach ([false, true] as $odbc) {
            $name = $odbc ? 'sqlsrv_odbc' : 'sqlsrv_native';
            $this->container->get(ConfigInterface::class)->set('databases.' . $name, [
                'driver' => 'sqlsrv', 'database' => 'review', 'prefix' => '', 'odbc' => $odbc,
                'lazy' => true, 'release_after_use' => true,
            ]);
            $connection = $this->container->get(ConnectionResolverInterface::class)->connection($name);
            $this->assertInstanceOf(SqlServerBuilder::class, $connection->query());
            $this->assertSame(42, $connection->table('users')->insertGetId(['name' => 'review']));
            $this->assertNull($connection->getResolvedConnection());
            $connection->close();
        }
    }

    public function testRawSessionStateIsDiscardedAtScopeExit(): void
    {
        $pdo = null;
        Db::withConnection(function ($connection) use (&$pdo) {
            $pdo = $connection->getPdo();
            $pdo->exec('CREATE TEMP TABLE scoped_state (id INTEGER)');
        });
        $lease = $this->container->get(ConnectionResolverInterface::class)->acquireLease('default');
        $this->assertNotSame($pdo, $lease->getDatabaseConnection()->getPdo());
        $this->assertSame([], $lease->getDatabaseConnection()->select("SELECT name FROM sqlite_temp_master WHERE name='scoped_state'"));
        $lease->release();
    }

    public function testResetFailureDiscardsDriverAndPreservesCallbackException(): void
    {
        DatabaseConnection::resolverFor('review-reset', static function ($pdo, $database, $prefix, $config) {
            return new class($pdo, $database, $prefix, $config) extends MySqlConnection {
                public bool $failReset = false;

                public function setTablePrefix(string $prefix): static
                {
                    if ($prefix === '' && $this->failReset) {
                        throw new RuntimeException('reset failed');
                    }
                    return parent::setTablePrefix($prefix);
                }
            };
        }, static fn (array $config) => new ConnectionMetadata(
            $config,
            new MySqlGrammar(),
            new MySqlProcessor(),
            $config['database'],
            $config['prefix']
        ));
        $this->container->get(ConfigInterface::class)->set('databases.default.driver', 'review-reset');
        $this->container->entries['db.connector.review-reset'] = new class implements ConnectorInterface {
            public function connect(array $config)
            {
                return new PDOStub('review-reset');
            }
        };
        $this->connection->setTablePrefix('tenant_');
        try {
            $this->connection->runOperation(function ($driver) {
                $driver->failReset = true;
                throw new RuntimeException('callback failed');
            });
            $this->fail('The original callback failure must be preserved.');
        } catch (RuntimeException $exception) {
            $this->assertSame('callback failed', $exception->getMessage());
        }
        $this->assertSame(1, $this->pool()->getConnectionsInChannel());
        $this->connection->select('SELECT 1');
        $this->assertNull($this->connection->getResolvedConnection());
    }

    private function pool()
    {
        return $this->container->get(PoolFactory::class)->getPool('default');
    }

    private function prewarmTwoConnections(): void
    {
        $first = $this->pool()->get();
        $second = $this->pool()->get();
        $first->select('SELECT 1');
        $second->select('SELECT 1');
        $first->release();
        $second->release();
    }
}
