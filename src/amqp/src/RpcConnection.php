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
namespace Hyperf\Amqp;

use Hyperf\Amqp\Builder\QueueBuilder;
use Hyperf\Amqp\Pool\AmqpConnectionPool;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;

class RpcConnection extends Connection
{
    /**
     * @var string
     */
    protected $queue;

    /**
     * @var AMQPMessage
     */
    protected $message;

    /**
     * @var string
     */
    protected $correlationId;

    public function __construct(ContainerInterface $container, AmqpConnectionPool $pool, array $config)
    {
        parent::__construct($container, $pool, $config);
    }

    public function initChannel(QueueBuilder $builder, string $correlationId): AMQPChannel
    {
        $this->message = null;
        $this->correlationId = $correlationId;

        if (! $this->channel || ! $this->check()) {
            $this->channel = $this->getConnection()->channel();
            [$this->queue] = $this->channel->queue_declare(
                $builder->getQueue(),
                $builder->isPassive(),
                $builder->isDurable(),
                $builder->isExclusive(),
                $builder->isAutoDelete(),
                $builder->isNowait(),
                $builder->getArguments(),
                $builder->getTicket()
            );

            var_dump(111);
            $this->channel->basic_consume(
                $this->queue,
                '',
                false,
                true,
                false,
                false,
                function (AMQPMessage $message) {
                    var_dump($message->get('correlation_id'));
                    if ($message->get('correlation_id') == $this->correlationId) {
                        $this->message = $message;
                    }
                }
            );
        }
        return $this->channel;
    }

    public function getAMQPMessage(): AMQPMessage
    {
        while (is_null($this->message)) {
            var_dump('wait...');
            $this->channel->wait();
        }

        $message = $this->message;
        $this->message = null;
        return $message;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }
}
