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

use Hyperf\Amqp\Exception\MessageException;
use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Amqp\Message\MessageInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Consumer extends Builder
{
    /**
     * @var ConsumerInterface
     */
    protected $message;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    protected $status = true;

    protected $signals
        = [
            SIGQUIT,
            SIGTERM,
            SIGTSTP,
        ];

    public function signalHandler()
    {
        $this->status = false;
    }

    public function consume(ConsumerMessageInterface $message): void
    {
        pcntl_async_signals(true);

        foreach ($this->signals as $signal) {
            pcntl_signal($signal, [$this, 'signalHandler']);
        }

        $this->message = $message;
        $this->channel = $this->getChannel($message->getPoolName());

        $this->declare($message, $this->channel);

        $this->channel->basic_consume($this->message->getQueue(), $this->message->getRoutingKey(), false, false, false, false, [
                $this,
                'callback'
            ]);

        while ($this->status && count($this->channel->callbacks) > 0) {
            $this->channel->wait();
        }

        $this->channel->close();
    }

    public function callback(AMQPMessage $message)
    {
        $body = $message->getBody();
        $consumerMessage = $this->message->unserialize($body);

        try {
            $result = $this->message->consume($consumerMessage);
            if ($result === Result::ACK) {
                $this->channel->basic_ack($message->delivery_info['delivery_tag']);
            } elseif ($this->message->isRequeue() && $result === Result::REQUEUE) {
                $this->channel->basic_reject($message->delivery_info['delivery_tag'], true);
            }
        } catch (\Throwable $exception) {

        }
        $this->channel->basic_reject($message->delivery_info['delivery_tag'], false);
    }

    public function declare(MessageInterface $message, ?Channel $channel = null): void
    {
        if (! $message instanceof ConsumerMessageInterface) {
            throw new MessageException('Message must instanceof ' . ConsumerInterface::class);
        }

        if (! $channel) {
            $channel = $this->getChannel($message->getPoolName());
        }

        parent::declare($message, $channel);

        $builder = $message->getQueueBuilder();

        $channel->queue_declare($builder->getQueue(), $builder->isPassive(), $builder->isDurable(), $builder->isExclusive(), $builder->isAutoDelete(), $builder->isNowait(), $builder->getArguments(), $builder->getTicket());

        $channel->queue_bind($message->getQueue(), $message->getExchange(), $message->getRoutingKey());
    }
}
