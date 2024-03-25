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

namespace Hyperf\Cache\Driver;

use Carbon\Carbon;
use Hyperf\Coordinator\Timer;
use Hyperf\Pool\SimplePool\Pool;
use Hyperf\Pool\SimplePool\PoolFactory;
use InvalidArgumentException;
use PDO;
use Psr\Container\ContainerInterface;

class SqliteDriver extends Driver
{
    protected ?Pool $pool = null;

    protected ?Timer $timer = null;

    protected string $table;

    protected bool $tableCreated = false;

    public function __construct(ContainerInterface $container, array $config)
    {
        // @todo check if the sqlite pdo extension is installed and hooked

        $config = array_replace([
            'database' => ':memory:',
            'table' => 'hyperf_cache' . uniqid('_'),
            'prefix' => '',
            'max_connections' => 10,
            'options' => [],
        ], $config);
        parent::__construct($container, $config);

        $this->table = $config['table'];
    }

    public function fetch(string $key, $default = null): array
    {
        return $this->execute(function (PDO $pdo) use ($key, $default) {
            $sql = sprintf('SELECT value, expiration FROM %s WHERE id = ?', $this->table);
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$this->getCacheKey($key)]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result === false ? [false, $default] : [true, $this->packer->unpack($result['value'])];
        });
    }

    public function clearExpired()
    {
        return $this->execute(function (PDO $pdo) {
            $sql = sprintf('DELETE FROM %s WHERE expiration > 0 AND expiration < ?', $this->table);
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$this->currentTime()]);
        });
    }

    public function clearPrefix(string $prefix): bool
    {
        return $this->execute(function (PDO $pdo) use ($prefix) {
            $sql = sprintf('DELETE FROM %s WHERE id LIKE ?', $this->table);
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$this->getCacheKey($prefix . '%')]);
        });
    }

    public function get($key, $default = null): mixed
    {
        return $this->execute(function (PDO $pdo) use ($key, $default) {
            $sql = sprintf('SELECT value, expiration FROM %s WHERE id = ?', $this->table);
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$this->getCacheKey($key)]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result === false ? $default : $this->packer->unpack($result['value']);
        });
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->execute(function (PDO $pdo) use ($key, $value, $ttl) {
            $seconds = $this->secondsUntil($ttl);
            $sql = sprintf('INSERT OR REPLACE INTO %s (id, value, expiration) VALUES (?, ?, ?)', $this->table);
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                $this->getCacheKey($key),
                $this->packer->pack($value),
                $seconds > 0 ? Carbon::now()->addSeconds($seconds)->timestamp : 0,
            ]);
        });
    }

    public function delete($key): bool
    {
        return $this->execute(function (PDO $pdo) use ($key) {
            $sql = sprintf('DELETE FROM %s WHERE id = ?', $this->table);
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$this->getCacheKey($key)]);
        });
    }

    public function clear(): bool
    {
        return $this->execute(function (PDO $pdo) {
            $sql = sprintf('DELETE FROM %s', $this->table);
            $stmt = $pdo->prepare($sql);
            return $stmt->execute();
        });
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return $this->execute(function (PDO $pdo) use ($keys, $default) {
            $sql = sprintf('SELECT id, value, expiration FROM %s WHERE id IN (%s)', $this->table, implode(', ', array_fill(0, count($keys), '?')));
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_map(fn ($key) => $this->getCacheKey($key), $keys));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $values = [];
            foreach ($keys as $key) {
                $values[$key] = $default;
            }
            foreach ($result as $item) {
                $values[$item['key']] = $this->packer->unpack($item['value']);
            }

            return $values;
        });
    }

    public function setMultiple($values, $ttl = null): bool
    {
        return $this->execute(function (PDO $pdo) use ($values, $ttl) {
            $seconds = $this->secondsUntil($ttl);
            $sql = sprintf('INSERT OR REPLACE INTO %s (id, value, expiration) VALUES (?, ?, ?)', $this->table);
            $stmt = $pdo->prepare($sql);
            foreach ($values as $key => $value) {
                $stmt->execute([
                    $this->getCacheKey($key),
                    $this->packer->pack($value),
                    $seconds > 0 ? Carbon::now()->addSeconds($seconds)->timestamp : 0,
                ]);
            }

            return true;
        });
    }

    public function deleteMultiple($keys): bool
    {
        return $this->execute(function (PDO $pdo) use ($keys) {
            $sql = sprintf('DELETE FROM %s WHERE id IN (%s)', $this->table, implode(', ', array_fill(0, count($keys), '?')));
            $stmt = $pdo->prepare($sql);
            return $stmt->execute(array_map(fn ($key) => $this->getCacheKey($key), $keys));
        });
    }

    public function has($key): bool
    {
        return $this->execute(function (PDO $pdo) use ($key) {
            $sql = sprintf('SELECT 1 FROM %s WHERE id = ?', $this->table);
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$this->getCacheKey($key)]);
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        });
    }

    public function dump()
    {
        dump($this->execute(function (PDO $pdo) {
            $sql = sprintf('SELECT * FROM %s', $this->table);
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }));
    }

    protected function connect(array $config): PDO
    {
        $options = array_replace([
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ], $config['options'] ?? []);

        if ($config['database'] === ':memory:') {
            return $this->createConnection('sqlite::memory:', $config, $options);
        }

        $path = realpath($config['database']);

        if ($path === false) {
            throw new InvalidArgumentException("Database ({$config['database']}) does not exist.");
        }

        return $this->createConnection("sqlite:{$path}", $config, $options);
    }

    protected function createConnection($dsn, array $config, array $options): PDO
    {
        return new PDO($dsn, null, null, $options);
    }

    protected function createTable(PDO $pdo): void
    {
        $creation = <<<SQL
CREATE TABLE IF NOT EXISTS {$this->table} (
    id TEXT PRIMARY KEY NOT NULL,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS expiration_index ON {$this->table} (expiration)
SQL;

        $pdo->exec($creation);
    }

    protected function execute(callable $callback)
    {
        if (! $this->pool) {
            $factory = $this->container->get(PoolFactory::class);
            $config = $this->config;
            $this->pool = $factory->get(static::class . '.pool', fn () => $this->connect($config), [
                'max_connections' => (int) ($config['max_connections'] ?? 10),
            ]);
        }

        $connection = $this->pool->get();
        $pdo = $connection->getConnection();

        if (! $this->tableCreated) {
            $this->createTable($pdo);
            $this->timer ??= new Timer();
            $this->timer->tick(1, fn () => $this->clearExpired());
            $this->tableCreated = true;
        }

        try {
            return $callback($pdo);
        } finally {
            $connection->release();
        }
    }
}
