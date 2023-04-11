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
use Hyperf\Context\ApplicationContext;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;

abstract class ConsumerMessage extends Message implements ConsumerMessageInterface
{
    public ?ContainerInterface $container = null;

    protected ?string $queue = null;

    protected bool $requeue = true;

    protected array|string $routingKey = [];

    protected ?array $qos = [
        'prefetch_size' => 0,
        'prefetch_count' => 1,
        'global' => false,
    ];

    protected bool $enable = true;

    protected int $maxConsumption = 0;

    protected int|float $waitTimeout = 0;

    protected int $nums = 1;

    public function consumeMessage($data, AMQPMessage $message): string
    {
        return $this->consume($data);
    }

    public function consume($data): string
    {
        return Result::ACK;
    }

    public function setQueue(string $queue): static
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
        return '';
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;
        return $this;
    }

    public function getMaxConsumption(): int
    {
        return $this->maxConsumption;
    }

    public function setMaxConsumption(int $maxConsumption): static
    {
        $this->maxConsumption = $maxConsumption;
        return $this;
    }

    public function getWaitTimeout(): int|float
    {
        return $this->waitTimeout;
    }

    public function setWaitTimeout(int|float $timeout): static
    {
        $this->waitTimeout = $timeout;
        return $this;
    }

    public function getNums(): int
    {
        return $this->nums;
    }

    public function setNums(int $nums): static
    {
        $this->nums = $nums;
        return $this;
    }

    public function setContainer(ContainerInterface $container): static
    {
        $this->container = $container;
        return $this;
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
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
