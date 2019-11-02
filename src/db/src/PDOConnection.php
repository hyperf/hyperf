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

use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\Pool;
use PDO;
use Psr\Container\ContainerInterface;

class PDOConnection extends AbstractConnection
{
    /**
     * @var PDO
     */
    protected $connection;

    /**
     * @var array
     */
    protected $config = [
        'driver' => 'pdo',
        'host' => 'localhost',
        'database' => 'hyperf',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
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
        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }

        if (! $this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this;
    }

    /**
     * Reconnect the connection.
     */
    public function reconnect(): bool
    {
        $dbms = $this->config['driver'];
        $host = $this->config['host'];
        $dbName = $this->config['database'];
        $username = $this->config['username'];
        $password = $this->config['password'];
        $dsn = "{$dbms}:host={$host};dbname={$dbName}";
        try {
            $pdo = new \PDO($dsn, $username, $password, $this->config['options']);
        } catch (\Throwable $e) {
            throw new ConnectionException('Connection reconnect failed.:' . $e->getMessage());
        }

        $this->connection = $pdo;
        $this->lastUseTime = microtime(true);

        return true;
    }

    /**
     * Close the connection.
     */
    public function close(): bool
    {
        unset($this->connection);

        return true;
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    public function getErrorCode()
    {
        $errorCode = $this->connection->errorCode();
        return $errorCode == '00000' ? 0 : $errorCode;
    }

    public function getErrorInfo()
    {
        $message = $this->connection->errorInfo()[2];
        return empty($message) ? '' : $message;
    }

    public function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    public function prepare(string $sql, ?array $data = null, array $options = []): bool
    {
        return $this->connection->prepare($sql, $options)->execute($data);
    }

    public function query(string $query, array $bindings = []): array
    {
        // For select statements, we'll simply execute the query and return an array
        // of the database result set. Each element in the array will be a single
        // row from the database table, and will either be an array or objects.
        $statement = $this->connection->prepare($query);

        $this->bindValues($statement, $bindings);

        $statement->execute();

        return $statement->fetchAll();
    }

    public function fetch(string $query, array $bindings = []): array
    {
        $records = $this->query($query, $bindings);

        return array_shift($records);
    }

    public function execute(string $query, array $bindings = []): int
    {
        $statement = $this->connection->prepare($query);

        $this->bindValues($statement, $bindings);

        $statement->execute();

        return $statement->rowCount();
    }

    public function insert(string $query, array $bindings = [])
    {
        $statement = $this->connection->prepare($query);

        $this->bindValues($statement, $bindings);

        $statement->execute();

        return $this->connection->lastInsertId();
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
}
