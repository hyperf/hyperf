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

    protected $signals = [
        SIGQUIT,
        SIGTERM,
        SIGTSTP
    ];

    public function signalHandler()
    {
        $this->status = false;
    }

    public function consume(ConsumerInterface $message): void
    {
        pcntl_async_signals(true);

        foreach ($this->signals as $signal) {
            pcntl_signal($signal, [$this, 'signalHandler']);
        }

        $this->message = $message;
        $this->channel = $this->getChannel($message->getPoolName());

        $this->declare($message);

        $this->channel->basic_consume(
            $this->message->getQueue(),
            $this->message->getRoutingKey(),
            false,
            false,
            false,
            false,
            [$this, 'callback']
        );

        while ($this->status && count($this->channel->callbacks) > 0) {
            $this->channel->wait();
        }

        $this->channel->close();
    }

    public function callback(AMQPMessage $msg)
    {
        $body = $msg->getBody();
        $data = $this->message->unserialize($body);

        try {
            if ($this->message->consume($data)) {
                $this->channel->basic_ack($msg->delivery_info['delivery_tag']);
            } else {
                $this->channel->basic_reject($msg->delivery_info['delivery_tag'], $this->message->isRequeue());
            }
        } catch (\Throwable $ex) {
            $this->channel->basic_reject($msg->delivery_info['delivery_tag'], $this->message->isRequeue());
        }
    }

    public function declare(ConsumerInterface $message, ?AMQPChannel $channel = null): void
    {
        if (!$channel) {
            $channel = $this->getChannel($message->getPoolName());
        }

        $channel->exchange_declare(
            $message->getExchange(),
            $message->getType(),
            false,
            true,
            false
        );

        $header = [
            'x-ha-policy' => ['S', 'all']
        ];
        $channel->queue_declare(
            $message->getQueue(),
            false,
            true,
            false,
            false,
            false,
            $header
        );

        $channel->queue_bind(
            $message->getQueue(),
            $message->getExchange(),
            $message->getRoutingKey()
        );
    }
}
