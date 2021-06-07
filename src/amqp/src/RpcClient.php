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

use Hyperf\Amqp\Builder\QueueBuilder;
use Hyperf\Amqp\Exception\TimeoutException;
use Hyperf\Amqp\Message\RpcMessageInterface;
use Hyperf\Engine\Channel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class RpcClient extends Builder
{
    public const MAX_CHANNEL = 64;

    protected $poolChannels = [];

    public function call(RpcMessageInterface $rpcMessage, int $timeout = 5)
    {
        $pool = $rpcMessage->getPoolName();
        $exchange = $rpcMessage->getExchange();
        $queue = $rpcMessage->getQueueBuilder()->getQueue();

        $chan = $this->initPoolChannel($pool, $exchange, $queue);
        $channel = null;
        try {
            if (! $chan->isEmpty()) {
                $channel = $chan->pop(0.001);
            }

            if (empty($channel)) {
                $connection = $this->factory->getConnection($rpcMessage->getPoolName());
                $channel = new RpcChannel($connection->getChannel());
                $this->initChannel($channel, $rpcMessage->getQueueBuilder());
            }

            $channel->open();

            $message = new AMQPMessage(
                $rpcMessage->serialize(),
                [
                    'correlation_id' => $channel->getCorrelationId(),
                    'reply_to' => $channel->getQueue(),
                ]
            );

            $channel->getChannel()->basic_publish($message, $rpcMessage->getExchange(), $rpcMessage->getRoutingKey());

            $amqpMessage = $channel->wait($timeout);
            if (empty($amqpMessage)) {
                throw new TimeoutException('RPC execute timeout.');
            }

            $result = $rpcMessage->unserialize($amqpMessage->getBody());
        } catch (\Throwable $exception) {
            isset($channel) && $channel->close();
            throw $exception;
        }

        $this->release($chan, $channel);

        return $result;
    }

    protected function initChannel(RpcChannel $channel, QueueBuilder $builder)
    {
        $queue = $channel->getChannel()->queue_declare(
            $builder->getQueue(),
            $builder->isPassive(),
            $builder->isDurable(),
            $builder->isExclusive(),
            $builder->isAutoDelete(),
            $builder->isNowait(),
            $builder->getArguments(),
            $builder->getTicket()
        );

        $channel->getChannel()->basic_consume(
            $queue,
            '',
            false,
            true,
            false,
            false,
            function (AMQPMessage $message) use ($channel) {
                if ($message->get('correlation_id') == $channel->getCorrelationId()) {
                    $channel->getChan()->push($message);
                }
            }
        );

        $channel->setQueue($queue);
    }

    protected function release(Channel $chan, AMQPChannel $channel): void
    {
        if ($chan->getLength() > static::MAX_CHANNEL) {
            $channel->close();
            return;
        }

        if (! $chan->push($channel, 0.001)) {
            $channel->close();
        }
    }

    protected function initPoolChannel(string $pool, string $exchange, string $queue)
    {
        if (! isset($this->poolChannels[$pool][$exchange][$queue])) {
            $this->poolChannels[$pool][$exchange][$queue] = new Channel(static::MAX_CHANNEL);
        }

        return $this->poolChannels[$pool][$exchange][$queue];
    }
}
