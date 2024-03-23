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

namespace Hyperf\DB\PgSQL;

use Closure;
use Hyperf\DB\AbstractConnection;
use Hyperf\DB\Exception\QueryException;
use Hyperf\DB\Exception\RuntimeException;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\PostgreSQL;
use Swoole\Coroutine\PostgreSQLStatement;

class PgSQLConnection extends AbstractConnection
{
    protected PostgreSQL $connection;

    protected array $config = [
        'driver' => 'pgsql',
        'host' => '127.0.0.1',
        'port' => 5432,
        'database' => 'postgres',
        'username' => 'postgres',
        'password' => '',
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 32,
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

    public function reconnect(): bool
    {
        $connection = new PostgreSQL();
        $result = $connection->connect(sprintf(
            'host=%s port=%s dbname=%s user=%s password=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['database'],
            $this->config['username'],
            $this->config['password']
        ));

        if ($result === false) {
            throw new RuntimeException($connection->error);
        }

        $this->connection = $connection;
        $this->lastUseTime = microtime(true);
        $this->transactions = 0;
        return true;
    }

    public function close(): bool
    {
        unset($this->connection);

        return true;
    }

    public function insert(string $query, array $bindings = []): int
    {
        throw new QueryException('cannot support insert.');
    }

    public function execute(string $query, array $bindings = []): int
    {
        $statement = $this->prepare($query);

        $result = $statement->execute($bindings);
        if ($result === false || ! empty($this->connection->error)) {
            throw new QueryException($this->connection->error);
        }

        $count = $statement->affectedRows();
        if ($count === false) {
            throw new QueryException($this->connection->error);
        }

        return $count;
    }

    public function exec(string $sql): int
    {
        return $this->execute($sql);
    }

    public function query(string $query, array $bindings = []): array
    {
        $statement = $this->prepare($query);

        $result = $statement->execute($bindings);
        if ($result === false || ! empty($this->connection->error)) {
            throw new QueryException($this->connection->error);
        }

        return $statement->fetchAll() ?: [];
    }

    public function fetch(string $query, array $bindings = [])
    {
        $records = $this->query($query, $bindings);

        return array_shift($records);
    }

    public function call(string $method, array $argument = [])
    {
        return match ($method) {
            'beginTransaction' => $this->connection->query('BEGIN'),
            'rollBack' => $this->connection->query('ROLLBACK'),
            'commit' => $this->connection->query('COMMIT'),
            default => $this->connection->{$method}(...$argument),
        };
    }

    public function run(Closure $closure)
    {
        return $closure->call($this, $this->connection);
    }

    /**
     * @param string $needle
     * @param string $replace
     * @param string $haystack
     * @deprecated ,using `strReplaceOnce` instead
     */
    public function str_replace_once($needle, $replace, $haystack): array|string
    {
        return $this->strReplaceOnce($needle, $replace, $haystack);
    }

    public function strReplaceOnce(string $needle, string $replace, string $haystack): array|string
    {
        // Looks for the first occurence of $needle in $haystack
        // and replaces it with $replace.
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            // Nothing found
            return $haystack;
        }

        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }

    protected function prepare(string $query): PostgreSQLStatement
    {
        $num = 1;
        while (strpos($query, '?')) {
            $query = $this->strReplaceOnce('?', '$' . $num++, $query);
        }

        $statement = $this->connection->prepare($query);
        if (! $statement) {
            throw new QueryException($this->connection->error);
        }

        return $statement;
    }
}
