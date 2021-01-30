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
namespace Hyperf\Amqp\Message;

use Hyperf\Amqp\Builder\QueueBuilder;
use Hyperf\Amqp\Packer\Packer;
use Hyperf\Amqp\Result;
use Hyperf\Utils\ApplicationContext;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;

abstract class ConsumerMessage extends Message implements ConsumerMessageInterface
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var string
     */
    protected $queue;

    /**
     * @var bool
     */
    protected $requeue = true;

    /**
     * @var array
     */
    protected $routingKey = [];

    /**
     * @var null|array
     */
    protected $qos = [
        'prefetch_size' => 0,
        'prefetch_count' => 1,
        'global' => false,
    ];

    /**
     * @var bool
     */
    protected $enable = true;

    /**
     * @var int
     */
    protected $maxConsumption = 0;

    /**
     * @var float|int
     */
    protected $waitTimeout = 0;

    public function consumeMessage($data, AMQPMessage $message): string
    {
        return $this->consume($data);
    }

    public function consume($data): string
    {
        return Result::ACK;
    }

    public function setQueue(string $queue): self
    {
        $this->queue = $queue;
        return $this;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function isRequeue(): bool
    {
        return $this->requeue;
    }

    public function getQos(): ?array
    {
        return $this->qos;
    }

    public function getQueueBuilder(): QueueBuilder
    {
        return (new QueueBuilder())->setQueue($this->getQueue());
    }

    public function unserialize(string $data)
    {
        $container = ApplicationContext::getContainer();
        $packer = $container->get(Packer::class);

        return $packer->unpack($data);
    }

    public function getConsumerTag(): string
    {
        // TODO(v3.0): Use empty string instead of routing keys
        return implode(',', (array) $this->getRoutingKey());
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): self
    {
        $this->enable = $enable;
        return $this;
    }

    public function getMaxConsumption(): int
    {
        return $this->maxConsumption;
    }

    public function setMaxConsumption(int $maxConsumption)
    {
        $this->maxConsumption = $maxConsumption;
        return $this;
    }

    public function getWaitTimeout()
    {
        return $this->waitTimeout;
    }

    public function setWaitTimeout($timeout)
    {
        $this->waitTimeout = $timeout;
        return $this;
    }

    protected function reply($data, AMQPMessage $message)
    {
        $packer = ApplicationContext::getContainer()->get(Packer::class);

        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];
        $channel->basic_publish(
            new AMQPMessage($packer->pack($data), [
                'correlation_id' => $message->get('correlation_id'),
            ]),
            '',
            $message->get('reply_to')
        );
    }
}
