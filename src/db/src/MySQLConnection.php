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
namespace Hyperf\DB;

use Closure;
use Hyperf\DB\Exception\RuntimeException;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\MySQL;
use Swoole\Coroutine\MySQL\Statement;

/**
 * @deprecated since 3.1, will be removed in 4.0.
 */
class MySQLConnection extends AbstractConnection
{
    protected ?MySQL $connection = null;

    protected array $config = [
        'driver' => 'pdo',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'hyperf',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'defer_release' => false,
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
        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    /**
     * Reconnect the connection.
     */
    public function reconnect(): bool
    {
        $connection = new MySQL();
        $connection->connect([
            'host' => $this->config['host'],
            'port' => $this->config['port'],
            'user' => $this->config['username'],
            'password' => $this->config['password'],
            'database' => $this->config['database'],
            'timeout' => $this->config['pool']['connect_timeout'],
            'charset' => $this->config['charset'],
            'fetch_mode' => true,
        ]);

        $this->connection = $connection;
        $this->lastUseTime = microtime(true);
        $this->transactions = 0;
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

    public function insert(string $query, array $bindings = []): int
    {
        $statement = $this->prepare($query);

        $statement->execute($bindings);

        return $statement->insert_id;
    }

    public function execute(string $query, array $bindings = []): int
    {
        $statement = $this->prepare($query);

        $statement->execute($bindings);

        return $statement->affected_rows;
    }

    public function exec(string $sql): int
    {
        $res = $this->connection->query($sql);
        if ($res === false) {
            throw new RuntimeException($this->connection->error);
        }

        return $this->connection->affected_rows;
    }

    public function query(string $query, array $bindings = []): array
    {
        // For select statements, we'll simply execute the query and return an array
        // of the database result set. Each element in the array will be a single
        // row from the database table, and will either be an array or objects.
        $statement = $this->prepare($query);

        $statement->execute($bindings);

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

        return match ($method) {
            'beginTransaction' => $this->connection->begin($timeout),
            'rollBack' => $this->connection->rollback($timeout),
            'commit' => $this->connection->commit($timeout),
            default => $this->connection->{$method}(...$argument),
        };
    }

    public function run(Closure $closure)
    {
        return $closure->call($this, $this->connection);
    }

    protected function prepare(string $query): Statement
    {
        $statement = $this->connection->prepare($query);

        if ($statement === false) {
            throw new RuntimeException($this->connection->error);
        }

        return $statement;
    }
}
