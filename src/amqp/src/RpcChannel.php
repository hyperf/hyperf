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

namespace Hyperf\Amqp;

use Hyperf\Engine\Channel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class RpcChannel
{
    protected ?Channel $chan = null;

    protected string $correlationId;

    protected ?string $queue = null;

    public function __construct(protected AMQPChannel $channel)
    {
        $this->correlationId = uniqid();
    }

    public function open(): static
    {
        $this->chan?->close();
        $this->chan = new Channel(1);
        return $this;
    }

    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    public function getChan(): ?Channel
    {
        return $this->chan;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    public function getQueue(): ?string
    {
        return $this->queue;
    }

    public function setQueue(?string $queue): static
    {
        $this->queue = $queue;
        return $this;
    }

    public function wait(int $timeout): AMQPMessage|bool
    {
        $this->channel->wait(null, false, $timeout);

        return $this->chan->pop(0.001);
    }

    public function close(): void
    {
        $this->chan?->close();
        $this->channel->close();
    }
}
