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

namespace Hyperf\Redis;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\PoolInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Redis\Exception\InvalidRedisConnectionException;
use Hyperf\Redis\Exception\InvalidRedisOptionException;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use Redis;
use RedisCluster;
use RedisException;
use Throwable;

/**
 * @method bool select(int $db)
 */
class RedisConnection extends BaseConnection implements ConnectionInterface
{
    use Traits\ScanCaller;
    use Traits\MultiExec;

    protected null|Redis|RedisCluster $connection = null;

    protected array $config = [
        'host' => 'localhost',
        'port' => 6379,
        'auth' => null,
        'db' => 0,
        'timeout' => 0.0,
        'reserved' => null,
        'retry_interval' => 0,
        'read_timeout' => 0.0,
        'cluster' => [
            'enable' => false,
            'name' => null,
            'seeds' => [],
            'read_timeout' => 0.0,
            'persistent' => false,
            'context' => [],
        ],
        'sentinel' => [
            'enable' => false,
            'master_name' => '',
            'nodes' => [],
            'persistent' => '',
            'read_timeout' => 0,
        ],
        'options' => [],
        'context' => [],
    ];

    /**
     * Current redis database.
     */
    protected ?int $database = null;

    public function __construct(ContainerInterface $container, PoolInterface $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = array_replace_recursive($this->config, $config);

        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        try {
            $result = $this->connection->{$name}(...$arguments);
        } catch (Throwable $exception) {
            $result = $this->retry($name, $arguments, $exception);
        }

        return $result;
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
     * @throws RedisException
     * @throws ConnectionException
     */
    public function reconnect(): bool
    {
        $auth = $this->config['auth'];
        $db = $this->config['db'];
        $cluster = $this->config['cluster']['enable'] ?? false;
        $sentinel = $this->config['sentinel']['enable'] ?? false;

        $redis = match (true) {
            $cluster => $this->createRedisCluster(),
            $sentinel => $this->createRedisSentinel(),
            default => $this->createRedis($this->config),
        };

        $options = $this->config['options'] ?? [];

        foreach ($options as $name => $value) {
            if (is_string($name)) {
                $name = match (strtolower($name)) {
                    'serializer' => Redis::OPT_SERIALIZER, // 1
                    'prefix' => Redis::OPT_PREFIX, // 2
                    'read_timeout' => Redis::OPT_READ_TIMEOUT, // 3
                    'scan' => Redis::OPT_SCAN, // 4
                    'failover' => defined(Redis::class . '::OPT_SLAVE_FAILOVER') ? Redis::OPT_SLAVE_FAILOVER : 5, // 5
                    'keepalive' => Redis::OPT_TCP_KEEPALIVE, // 6
                    'compression' => Redis::OPT_COMPRESSION, // 7
                    'reply_literal' => Redis::OPT_REPLY_LITERAL, // 8
                    'compression_level' => Redis::OPT_COMPRESSION_LEVEL, // 9
                    default => throw new InvalidRedisOptionException(sprintf('The redis option key `%s` is invalid.', $name)),
                };
            }
            $redis->setOption($name, $value);
        }

        if ($redis instanceof Redis && isset($auth) && $auth !== '') {
            $redis->auth($auth);
        }

        $database = $this->database ?? $db;
        if ($database > 0) {
            $redis->select($database);
        }

        $this->connection = $redis;
        $this->lastUseTime = microtime(true);

        return true;
    }

    public function close(): bool
    {
        unset($this->connection);

        return true;
    }

    public function release(): void
    {
        try {
            if ($this->database && $this->database != $this->config['db']) {
                // Select the origin db after execute select.
                $this->select($this->config['db']);
                $this->database = null;
            }
            parent::release();
        } catch (Throwable $exception) {
            $this->log('Release connection failed, caused by ' . $exception, LogLevel::CRITICAL);
        }
    }

    public function setDatabase(?int $database): void
    {
        $this->database = $database;
    }

    protected function createRedisCluster(): RedisCluster
    {
        try {
            $parameters = [];
            $parameters[] = $this->config['cluster']['name'] ?? null;
            $parameters[] = $this->config['cluster']['seeds'] ?? [];
            $parameters[] = $this->config['timeout'] ?? 0.0;
            $parameters[] = $this->config['cluster']['read_timeout'] ?? 0.0;
            $parameters[] = $this->config['cluster']['persistent'] ?? false;
            if (isset($this->config['auth'])) {
                $parameters[] = $this->config['auth'];
            }
            if (! empty($this->config['cluster']['context'])) {
                $parameters[] = $this->config['cluster']['context'];
            }

            $redis = new RedisCluster(...$parameters);
        } catch (Throwable $e) {
            throw new ConnectionException('Connection reconnect failed ' . $e->getMessage());
        }

        return $redis;
    }

    protected function retry($name, $arguments, Throwable $exception)
    {
        $this->log('Redis::__call failed, because ' . $exception->getMessage());

        try {
            $this->reconnect();
            $result = $this->connection->{$name}(...$arguments);
        } catch (Throwable $exception) {
            $this->lastUseTime = 0.0;
            throw $exception;
        }

        return $result;
    }

    /**
     * @throws ConnectionException
     */
    protected function createRedisSentinel(): Redis
    {
        try {
            $nodes = $this->config['sentinel']['nodes'] ?? [];
            $timeout = $this->config['timeout'] ?? 0;
            $persistent = $this->config['sentinel']['persistent'] ?? null;
            $retryInterval = $this->config['retry_interval'] ?? 0;
            $readTimeout = $this->config['sentinel']['read_timeout'] ?? 0;
            $masterName = $this->config['sentinel']['master_name'] ?? '';
            $auth = $this->config['sentinel']['auth'] ?? null;

            shuffle($nodes);

            $host = null;
            $port = null;
            foreach ($nodes as $node) {
                try {
                    $resolved = parse_url($node);
                    if (! isset($resolved['host'], $resolved['port'])) {
                        $this->log(sprintf('The redis sentinel node [%s] is invalid.', $node), LogLevel::ERROR);
                        continue;
                    }
                    $options = [
                        'host' => $resolved['host'],
                        'port' => (int) $resolved['port'],
                        'connectTimeout' => $timeout,
                        'persistent' => $persistent,
                        'retryInterval' => $retryInterval,
                        'readTimeout' => $readTimeout,
                        ...($auth ? ['auth' => $auth] : []),
                    ];
                    $sentinel = $this->container->get(RedisSentinelFactory::class)->create($options);
                    $masterInfo = $sentinel->getMasterAddrByName($masterName);
                    if (is_array($masterInfo) && count($masterInfo) >= 2) {
                        [$host, $port] = $masterInfo;
                        break;
                    }
                } catch (Throwable $exception) {
                    $this->log('Redis sentinel connection failed, caused by ' . $exception->getMessage());
                    continue;
                }
            }

            if ($host === null && $port === null) {
                throw new InvalidRedisConnectionException('Connect sentinel redis server failed.');
            }

            $redis = $this->createRedis([
                'host' => $host,
                'port' => $port,
                'timeout' => $timeout,
                'retry_interval' => $retryInterval,
                'read_timeout' => $readTimeout,
            ]);
        } catch (Throwable $e) {
            throw new ConnectionException('Connection reconnect failed ' . $e->getMessage());
        }

        return $redis;
    }

    /**
     * @throws ConnectionException
     * @throws RedisException
     */
    protected function createRedis(array $config): Redis
    {
        $parameters = [
            $config['host'] ?? '',
            (int) ($config['port'] ?? 6379),
            $config['timeout'] ?? 0.0,
            $config['reserved'] ?? null,
            $config['retry_interval'] ?? 0,
            $config['read_timeout'] ?? 0.0,
        ];

        if (! empty($config['context'])) {
            $parameters[] = $config['context'];
        }

        $redis = new Redis();
        if (! $redis->connect(...$parameters)) {
            throw new ConnectionException('Connection reconnect failed.');
        }
        return $redis;
    }

    private function log(string $message, string $level = LogLevel::WARNING): void
    {
        if ($this->container->has(StdoutLoggerInterface::class) && $logger = $this->container->get(StdoutLoggerInterface::class)) {
            $logger->log($level, $message);
        }
    }
}
