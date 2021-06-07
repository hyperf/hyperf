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
    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var null|Channel
     */
    protected $chan;

    /**
     * @var string
     */
    protected $correlationId;

    /**
     * @var null|string
     */
    protected $queue;

    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
        $this->correlationId = uniqid();
    }

    /**
     * @return static
     */
    public function open()
    {
        if ($this->chan) {
            $this->chan->close();
        }
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

    public function setQueue(?string $queue): RpcChannel
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @return AMQPMessage|false
     */
    public function wait(int $timeout)
    {
        $this->channel->wait(null, false, $timeout);

        return $this->chan->pop(0.001);
    }

    public function close()
    {
        if ($this->chan) {
            $this->chan->close();
        }

        $this->channel->close();
    }
}
