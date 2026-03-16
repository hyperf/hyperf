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

use Closure;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Engine\Contract\Socket\SocketFactoryInterface;
use Hyperf\Engine\Contract\SocketInterface;
use Hyperf\Engine\Socket\SocketOption;
use Hyperf\LoadBalancer\Node;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\value;

/**
 * @property int $errCode
 * @property string $errMsg
 */
class RpcConnection extends BaseConnection implements ConnectionInterface
{
    protected SocketInterface $connection;

    protected SocketFactoryInterface $factory;

    protected array $config = [
        'node' => null,
        'connect_timeout' => 5.0,
        'settings' => [],
    ];

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->factory = $container->get(SocketFactoryInterface::class);
        $this->config = array_replace($this->config, $config);

        $this->reconnect();
    }

    public function __get($name)
    {
        return $this->connection->{$name};
    }

    public function send(string $data): false|int
    {
        return $this->connection->sendAll($data);
    }

    public function recv(float $timeout = 0): false|string
    {
        return $this->recvPacket($timeout);
    }

    public function recvPacket(float $timeout = 0): false|string
    {
        return $this->connection->recvPacket($timeout);
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
        if (! $this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }
        return $this;
    }

    public function reconnect(): bool
    {
        if (! $this->config['node'] instanceof Closure) {
            throw new ConnectionException('Node of Connection is invalid.');
        }

        /** @var Node $node */
        $node = value($this->config['node']);
        $host = $node->host;
        $port = $node->port;
        $connectTimeout = $this->config['connect_timeout'];

        $this->connection = $this->factory->make(new SocketOption(
            $host,
            $port,
            $connectTimeout,
            $this->config['settings'] ?? []
        ));
        $this->lastUseTime = microtime(true);
        return true;
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
