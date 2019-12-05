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

namespace Hyperf\JsonRpc\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Swoole\Coroutine\Client as SwooleClient;

/**
 * @method bool|int send($data)
 * @method bool|string recv(float $timeout)
 * @property int $errCode
 */
class RpcConnection extends BaseConnection implements ConnectionInterface
{
    /**
     * @var SwooleClient
     */
    protected $connection;

    /**
     * @var array
     */
    protected $config = [
        'host' => 'localhost',
        'port' => 9501,
        'connect_timeout' => 5.0,
        'options' => [],
    ];

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = array_replace($this->config, $config);

        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function __get($name)
    {
        return $this->connection->{$name};
    }

    /**
     * @throws ConnectionException
     * @return $this
     */
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

    public function reconnect(): bool
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $connectTimeout = $this->config['connect_timeout'];

        $client = new SwooleClient(SWOOLE_SOCK_TCP);
        $client->set($this->config['options'] ?? []);
        $result = $client->connect($host, $port, $connectTimeout);
        if ($result === false && ($client->errCode === 114 || $client->errCode === 115)) {
            // Force close and reconnect to server.
            $client->close();
            throw new RuntimeException('Connect to server failed.');
        }

        $this->connection = $client;
        $this->lastUseTime = microtime(true);
        return true;
    }

    public function close(): bool
    {
        $this->connection->close();
        return true;
    }

    public function resetLastUseTime(): void
    {
        $this->lastUseTime = 0.0;
    }
}
