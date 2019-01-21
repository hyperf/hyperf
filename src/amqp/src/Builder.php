<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Amqp;

use Hyperf\Amqp\Message\ConsumerInterface;
use Hyperf\Amqp\Message\ProducerInterface;
use Hyperf\Amqp\Pool\PoolFactory;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use Psr\Container\ContainerInterface;

class Builder
{
    protected $name = 'default';

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function declare(): void
    {
        if ($this->message instanceof ProducerInterface) {
            $this->channel->exchange_declare(
                $this->message->getExchange(),
                $this->message->getType(),
                false,
                true,
                false
            );
        } elseif ($this->message instanceof ConsumerInterface) {
            $this->channel->exchange_declare(
                $this->message->getExchange(),
                $this->message->getType(),
                false,
                true,
                false
            );

            $header = [
                'x-ha-policy' => ['S', 'all']
            ];
            $this->channel->queue_declare(
                $this->message->getQueue(),
                false,
                true,
                false,
                false,
                false,
                $header
            );

            $this->channel->queue_bind(
                $this->message->getQueue(),
                $this->message->getExchange(),
                $this->message->getRoutingKey()
            );
        }
    }

    protected function getChannel(string $poolName): AMQPChannel
    {
        /** @var Connection $conn */
        $conn = $this->getConnection($poolName);
        $connection = $conn->getConnection();
        try {
            $channel = $connection->channel();
        } catch (AMQPRuntimeException $ex) {
            // Fetch channel failed, try again.
            $connection->reconnect();
            $channel = $connection->channel();
        }

        return $channel;
    }

    protected function getConnection(string $poolName): Connection
    {
        /** @var PoolFactory $factory */
        $factory = $this->container->get(PoolFactory::class);
        $pool = $factory->getAmqpPool($poolName);
        return $pool->get();
    }
}
