<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DB;

use Closure;
use Hyperf\DB\Events\MySQLConnected;
use Hyperf\DB\Events\StatementPrepared;
use Hyperf\DB\Events\TransactionBeginning;
use Hyperf\DB\Events\TransactionCommitted;
use Hyperf\DB\Events\TransactionRolledBack;
use Hyperf\DB\Exception\RuntimeException;
use Hyperf\Pool\Pool;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\MySQL;
use Swoole\Coroutine\MySQL\Statement;
use Throwable;

class MySQLConnection extends AbstractConnection
{
    /**
     * @var MySQL
     */
    protected $connection;

    /**
     * @var MySQL
     */
    protected $readConnection;

    /**
     * @var array
     */
    protected $config = [
        'driver' => 'pdo',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'hyperf',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => 60.0,
        ],
    ];

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = array_replace_recursive($this->config, $config);
        $this->initReadWriteConfig($this->config);
        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->getReadWriteConnection()->{$name}(...$arguments);
    }

    /**
     * Reconnect the connection.
     *
     * @throws MySQL\Exception
     * @throws Throwable
     */
    public function reconnect(): bool
    {
        if (! empty($this->readConfig)) {
            $this->readConnection = $this->reconnectConnection($this->readConfig);
        }

        $this->connection = $this->reconnectConnection($this->config);
        $this->lastUseTime = microtime(true);

        return true;
    }

    /**
     * Close the connection.
     */
    public function close(): bool
    {
        unset($this->connection, $this->readConnection);

        return true;
    }

    public function insert(string $query, array $bindings = []): int
    {
        $statement = $this->prepare($query);

        $start = microtime(true);

        $statement->execute($bindings);

        $this->logQuery($query, $bindings, $this->getElapsedTime($start));

        $this->recordsHaveBeenModified();

        return $statement->insert_id;
    }

    public function execute(string $query, array $bindings = []): int
    {
        $statement = $this->prepare($query);

        $start = microtime(true);

        $statement->execute($bindings);

        $this->logQuery($query, $bindings, $this->getElapsedTime($start));

        $this->recordsHaveBeenModified(
            ($count = $statement->affected_rows) > 0
        );

        return $count;
    }

    public function exec(string $sql): int
    {
        $connection = $this->getReadWriteConnection();

        $start = microtime(true);

        $res = $connection->query($sql);
        if ($res === false) {
            throw new RuntimeException($connection->error);
        }

        $this->logQuery($sql, [], $this->getElapsedTime($start));

        $this->recordsHaveBeenModified($connection->affected_rows > 0);

        return $connection->affected_rows;
    }

    public function query(string $query, array $bindings = []): array
    {
        $start = microtime(true);

        // For select statements, we'll simply execute the query and return an array
        // of the database result set. Each element in the array will be a single
        // row from the database table, and will either be an array or objects.
        $statement = $this->prepare($query, true);

        $statement->execute($bindings);

        $this->logQuery($query, $bindings, $this->getElapsedTime($start));

        return $statement->fetchAll();
    }

    public function fetch(string $query, array $bindings = [])
    {
        $records = $this->query($query, $bindings);

        return array_shift($records);
    }

    public function call(string $method, array $argument = [])
    {
        $timeout = $this->config['pool']['wait_timeout'];
        $connection = $this->getReadWriteConnection();
        switch ($method) {
            case 'beginTransaction':
                $this->event(new TransactionBeginning($this));
                return $connection->begin($timeout);
            case 'rollBack':
                $this->event(new TransactionCommitted($this));
                return $connection->rollback($timeout);
            case 'commit':
                $this->event(new TransactionRolledBack($this));
                return $connection->commit($timeout);
        }

        return $connection->{$method}(...$argument);
    }

    protected function prepare(string $query, bool $read = false): Statement
    {
        $connection = $this->getReadWriteConnection($read);
        $statement = $connection->prepare($query);

        if ($statement === false) {
            throw new RuntimeException($connection->error);
        }

        $this->event(new StatementPrepared($this, $statement));

        return $statement;
    }

    /**
     * reconnect Connection.
     *
     * @return Closure
     */
    protected function reconnectConnection(array $config)
    {
        return function () use ($config) {
            foreach (Arr::shuffle($hosts = $this->parseHosts($config)) as $key => $host) {
                $config['host'] = $host;
                $config['fetch_mode'] = true;

                try {
                    $connection = new MySQL();

                    $start = microtime(true);

                    $connection->connect([
                        'host' => $config['host'],
                        'port' => $config['port'],
                        'user' => $config['username'],
                        'password' => $config['password'],
                        'database' => $config['database'],
                        'timeout' => $config['pool']['connect_timeout'],
                        'charset' => $config['charset'],
                        'fetch_mode' => $config['fetch_mode'],
                    ]);

                    $this->event(new MySQLConnected($this, $this->getElapsedTime($start), $config));
                    return $connection;
                } catch (MySQL\Exception $e) {
                    continue;
                } catch (Throwable $e) {
                    continue;
                }
            }

            throw $e;
        };
    }

    /**
     * @param bool $read
     * @return MySQL
     */
    protected function getReadWriteConnection($read = false)
    {
        if ($read) {
            return $this->getReadConnection();
        }
        return $this->getWriteConnection();
    }

    /**
     * @return MySQL
     */
    protected function getWriteConnection()
    {
        if ($this->connection instanceof Closure) {
            return $this->connection = call_user_func($this->connection);
        }
        return $this->connection;
    }

    /**
     * @return MySQL
     */
    protected function getReadConnection()
    {
        if ($this->recordsModified && Arr::get($this->config, 'sticky')) {
            return $this->getWriteConnection();
        }

        if ($this->readConnection instanceof Closure) {
            return $this->readConnection = call_user_func($this->readConnection);
        }

        return $this->readConnection ?: $this->getWriteConnection();
    }
}
