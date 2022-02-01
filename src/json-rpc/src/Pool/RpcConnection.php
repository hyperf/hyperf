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

namespace Hyperf\JsonRpc\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\JsonRpc\Exception\ClientException;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Client as SwooleClient;

/**
 * @method bool|int send($data)
 * @method bool|string recv(float $timeout)
 * @property int $errCode
 * @property string $errMsg
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
        'node' => null,
        'connect_timeout' => 5.0,
        'settings' => [],
    ];

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = array_replace($this->config, $config);

        $this->reconnect();
    }

    public function reconnect(): bool
    {
        if (!$this->config['node'] instanceof \Closure) {
            throw new ConnectionException('Node of Connection is invalid.');
        }

        /** @var Node $node */
        $node = value($this->config['node']);
        $host = $node->host;
        $port = $node->port;
        $connectTimeout = $this->config['connect_timeout'];
        $client = new SwooleClient(SWOOLE_SOCK_TCP);
        $client->set($this->config['settings'] ?? []);
        $result = $client->connect($host, $port, $connectTimeout);
        if ($result === false) {
            if ($this->config['loadBalancer'] instanceof \Closure) {
                /** @var LoadBalancerInterface $loadBalance */
                $loadBalance = value($this->config['loadBalancer']);
                $loadBalance->removeNode($node);
                $rpcRegitsrProtocol = $loadBalance->getRegistryProtocol();
                if (empty($rpcRegitsrProtocol)) {
                    go(function () use ($loadBalance, $node) {
                        sleep(10);
                        $nodes = $loadBalance->getNodes();
                        array_push($nodes, $node);
                        $loadBalance->setNodes($nodes);
                    });
                }
            }
            // Force close and reconnect to server.
            $client->close();
            throw new ClientException('Connect to server failed. ' . $client->errMsg, $client->errCode);
        }

        $this->connection = $client;
        $this->lastUseTime = microtime(true);
        return true;
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
     * @return $this
     * @throws ConnectionException
     */
    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }
        if (!$this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }
        return $this;
    }

    public function close(): bool
    {
        $this->lastUseTime = 0.0;
        $this->connection->close();
        return true;
    }

    public function resetLastUseTime(): void
    {
        $this->lastUseTime = 0.0;
    }
}
