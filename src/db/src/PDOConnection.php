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
use Hyperf\Pool\Pool;
use Hyperf\Utils\Arr;
use PDO;
use PDOStatement;
use Psr\Container\ContainerInterface;
use Throwable;

class PDOConnection extends AbstractConnection
{
    /**
     * @var PDO
     */
    protected $connection;

    /**
     * @var PDO
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
        'fetch_mode' => PDO::FETCH_ASSOC,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => 60.0,
        ],
        'options' => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ];

    /**
     * Current mysql database.
     * @var null|int
     */
    protected $database;

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
     * @throws \PDOException
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

    public function query(string $query, array $bindings = []): array
    {
        $connection = $this->getReadWriteConnection(true);

        $start = microtime(true);

        // For select statements, we'll simply execute the query and return an array
        // of the database result set. Each element in the array will be a single
        // row from the database table, and will either be an array or objects.
        $statement = $connection->prepare($query);

        $this->event(new StatementPrepared($this, $statement));

        $this->bindValues($statement, $bindings);

        $statement->execute();

        $this->logQuery($query, $bindings, $this->getElapsedTime($start));

        $fetchModel = $this->config['fetch_mode'];

        return $statement->fetchAll($fetchModel);
    }

    public function fetch(string $query, array $bindings = [])
    {
        $records = $this->query($query, $bindings);

        return array_shift($records);
    }

    public function execute(string $query, array $bindings = []): int
    {
        $connection = $this->getReadWriteConnection();

        $start = microtime(true);

        $statement = $connection->prepare($query);

        $this->event(new StatementPrepared($this, $statement));

        $this->bindValues($statement, $bindings);

        $statement->execute();

        $this->logQuery($query, $bindings, $this->getElapsedTime($start));

        $this->recordsHaveBeenModified(
            ($count = $statement->rowCount()) > 0
        );

        return $count;
    }

    public function exec(string $sql): int
    {
        $connection = $this->getReadWriteConnection();

        $start = microtime(true);

        $count = $connection->exec($sql);

        $this->logQuery($sql, [], $this->getElapsedTime($start));

        $this->recordsHaveBeenModified($count > 0);

        return $count;
    }

    public function insert(string $query, array $bindings = []): int
    {
        $connection = $this->getReadWriteConnection();

        $start = microtime(true);

        $statement = $connection->prepare($query);

        $this->event(new StatementPrepared($this, $statement));

        $this->bindValues($statement, $bindings);

        $statement->execute();

        $this->logQuery($query, $bindings, $this->getElapsedTime($start));

        $this->recordsHaveBeenModified();

        return (int) $connection->lastInsertId();
    }

    public function call(string $method, array $argument = [])
    {
        $connection = $this->getReadWriteConnection();
        switch ($method) {
            case 'beginTransaction':
                $this->event(new TransactionBeginning($this));
                break;
            case 'rollBack':
                $this->event(new TransactionCommitted($this));
                break;
            case 'commit':
                $this->event(new TransactionRolledBack($this));
        }

        return $connection->{$method}(...$argument);
    }

    /**
     * Bind values to their parameters in the given statement.
     */
    protected function bindValues(PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * Build the DSN string for a host / port configuration.
     */
    protected function buildDsn(array $config): string
    {
        $host = $config['host'] ?? null;
        $port = $config['port'] ?? 3306;
        $database = $config['database'] ?? null;
        return sprintf('mysql:host=%s;port=%d;dbname=%s', $host, $port, $database);
    }

    /**
     * Configure the connection character set and collation.
     */
    protected function configureCharset(PDO $connection, array $config)
    {
        if (isset($config['charset'])) {
            $connection->prepare(sprintf("set names '%s'%s", $config['charset'], $this->getCollation($config)))->execute();
        }
    }

    /**
     * Get the collation for the configuration.
     */
    protected function getCollation(array $config): string
    {
        return isset($config['collation']) ? " collate '{$config['collation']}'" : '';
    }

    /**
     * Configure the timezone on the connection.
     */
    protected function configureTimezone(PDO $connection, array $config): void
    {
        if (isset($config['timezone'])) {
            $connection->prepare(sprintf('set time_zone="%s"', $config['timezone']))->execute();
        }
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

                try {
                    $username = $config['username'];
                    $password = $config['password'];

                    $dsn = $this->buildDsn($config);

                    try {
                        $start = microtime(true);

                        $connection = new \PDO($dsn, $username, $password, $this->config['options']);

                        $this->event(new MySQLConnected($this, $this->getElapsedTime($start), $config));
                    } catch (Throwable $e) {
                        continue;
                    }

                    $this->configureCharset($connection, $config);

                    $this->configureTimezone($connection, $config);

                    return $connection;
                } catch (\PDOException $e) {
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
     * @return PDO
     */
    protected function getReadWriteConnection($read = false)
    {
        if ($read) {
            return $this->getReadConnection();
        }
        return $this->getWriteConnection();
    }

    /**
     * @return PDO
     */
    protected function getWriteConnection()
    {
        if ($this->connection instanceof Closure) {
            return $this->connection = call_user_func($this->connection);
        }

        return $this->connection;
    }

    /**
     * @return PDO
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
