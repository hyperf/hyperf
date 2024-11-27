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

namespace Hyperf\JsonRpc;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\JsonRpc\Exception\ClientException;
use Hyperf\JsonRpc\Pool\PoolFactory;
use Hyperf\JsonRpc\Pool\RpcConnection;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\Pool\Pool;
use Hyperf\Rpc\Contract\TransporterInterface;
use Hyperf\Rpc\Exception\RecvException;
use Hyperf\Support\Exception\ExceptionThrower;
use Throwable;

use function Hyperf\Coroutine\defer;
use function Hyperf\Support\retry;

class JsonRpcPoolTransporter implements TransporterInterface
{
    use RecvTrait;

    private ?LoadBalancerInterface $loadBalancer;

    /**
     * If $loadBalancer is null, will select a node in $nodes to request,
     * otherwise, use the nodes in $loadBalancer.
     *
     * @var Node[]
     */
    private array $nodes = [];

    private float $connectTimeout;

    private float $recvTimeout;

    private int $retryCount;

    /**
     * @var int millisecond
     */
    private int $retryInterval;

    private $config = [
        'connect_timeout' => 5.0,
        'settings' => [],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 32,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => 60.0,
        ],
        'recv_timeout' => 5.0,
        'retry_count' => 2,
        'retry_interval' => 100,
    ];

    public function __construct(protected PoolFactory $factory, array $config = [])
    {
        $this->config = array_replace_recursive($this->config, $config);

        $this->recvTimeout = $this->config['recv_timeout'] ?? 5.0;
        $this->connectTimeout = $this->config['connect_timeout'] ?? 5.0;
        $this->retryCount = $this->config['retry_count'] ?? 2;
        $this->retryInterval = $this->config['retry_interval'] ?? 100;
    }

    public function send(string $data)
    {
        $result = retry($this->retryCount, function () use ($data) {
            try {
                $client = $this->getConnection();
                if ($client->send($data) === false) {
                    throw new ClientException('Send data failed. ' . $client->errMsg, $client->errCode);
                }
                return $this->recvAndCheck($client, $this->recvTimeout);
            } catch (Throwable $throwable) {
                if (isset($client)) {
                    $client->close();
                }
                if ($throwable instanceof RecvException && $throwable->getCode() === SOCKET_ETIMEDOUT) {
                    // Don't retry, when recv timeout.
                    return new ExceptionThrower($throwable);
                }
                throw $throwable;
            }
        }, $this->retryInterval);
        if ($result instanceof ExceptionThrower) {
            throw $result->getThrowable();
        }
        return $result;
    }

    public function recv()
    {
        $client = $this->getConnection();

        return $this->recvAndCheck($client, $this->recvTimeout);
    }

    /**
     * Get RpcConnection from Context.
     */
    public function getConnection(): RpcConnection
    {
        $class = spl_object_hash($this) . '.Connection';
        /** @var RpcConnection $connection */
        $connection = Context::get($class);
        if (isset($connection)) {
            try {
                if (! $connection->check()) {
                    // Try to reconnect the target server.
                    $connection->reconnect();
                }
                return $connection;
            } catch (Throwable $exception) {
                $this->log($exception);
            }
        }

        $connection = $this->getPool()->get();

        defer(function () use ($connection) {
            $connection->release();
        });

        return Context::set($class, $connection->getConnection());
    }

    public function getPool(): Pool
    {
        $name = spl_object_hash($this) . '.Pool';
        $config = [
            'connect_timeout' => $this->config['connect_timeout'],
            'settings' => $this->config['settings'],
            'pool' => $this->config['pool'],
            'node' => function () {
                return $this->getNode();
            },
        ];

        return $this->factory->getPool($name, $config);
    }

    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        return $this->loadBalancer;
    }

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface
    {
        $this->loadBalancer = $loadBalancer;
        return $this;
    }

    /**
     * @param Node[] $nodes
     */
    public function setNodes(array $nodes): self
    {
        $this->nodes = $nodes;
        return $this;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * If the load balancer is exists, then the node will select by the load balancer,
     * otherwise will get a random node.
     */
    private function getNode(): Node
    {
        if ($this->loadBalancer instanceof LoadBalancerInterface) {
            return $this->loadBalancer->select();
        }
        return $this->nodes[array_rand($this->nodes)];
    }

    private function log($message)
    {
        $container = ApplicationContext::getContainer();
        if ($container->has(StdoutLoggerInterface::class) && $logger = $container->get(StdoutLoggerInterface::class)) {
            $logger->error((string) $message);
        }
    }
}
