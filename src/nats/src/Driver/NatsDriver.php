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

namespace Hyperf\Nats\Driver;

use Closure;
use Hyperf\Nats\Connection as NatsConnection;
use Hyperf\Nats\ConnectionOptions;
use Hyperf\Nats\EncodedConnection;
use Hyperf\Nats\Encoders\JSONEncoder;
use Hyperf\Nats\Message;
use Hyperf\Pool\SimplePool\Connection;
use Hyperf\Pool\SimplePool\PoolFactory;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Channel;

class NatsDriver extends AbstractDriver
{
    /**
     * @var \Hyperf\Pool\SimplePool\Pool
     */
    protected $pool;

    public function __construct(ContainerInterface $container, string $name, array $config)
    {
        parent::__construct($container, $name, $config);

        $factory = $this->container->get(PoolFactory::class);
        $poolConfig = $config['pool'] ?? [];

        $this->pool = $factory->get('nats' . $this->name, function () use ($config) {
            $option = new ConnectionOptions($config['options'] ?? []);
            $encoder = make($config['encoder'] ?? JSONEncoder::class);
            $conn = make(EncodedConnection::class, [$option, $encoder]);
            $conn->connect();
            return $conn;
        }, $poolConfig);
    }

    public function publish(string $subject, $payload = null, $inbox = null)
    {
        try {
            /** @var Connection $connection */
            $connection = $this->pool->get();
            /** @var NatsConnection $client */
            $client = $connection->getConnection();
            $client->publish($subject, $payload, $inbox);
        } finally {
            $connection->release();
        }
    }

    public function request(string $subject, $payload, Closure $callback)
    {
        try {
            /** @var Connection $connection */
            $connection = $this->pool->get();
            /** @var NatsConnection $client */
            $client = $connection->getConnection();
            $client->request($subject, $payload, $callback);
        } finally {
            $connection->release();
        }
    }

    public function requestSync(string $subject, $payload, float $timeout = 10.0): Message
    {
        try {
            /** @var Connection $connection */
            $connection = $this->pool->get();
            /** @var NatsConnection $client */
            $client = $connection->getConnection();
            $channel = new Channel(1);
            $client->request($subject, $payload, function (Message $message) use ($channel) {
                $channel->push($message);
            });

            return $channel->pop($timeout);
        } finally {
            $connection->release();
        }
    }

    public function subscribe(string $subject, string $queue, Closure $callback): void
    {
        try {
            /** @var Connection $connection */
            $connection = $this->pool->get();
            /** @var NatsConnection $client */
            $client = $connection->getConnection();
            if (empty($queue)) {
                $client->subscribe($subject, $callback);
            } else {
                $client->queueSubscribe($subject, $queue, $callback);
            }
            $client->wait();
        } finally {
            $connection->release();
        }
    }
}
