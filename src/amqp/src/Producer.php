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

use Hyperf\Amqp\Message\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class Producer extends Builder
{
    public function produce(ProducerInterface $message): void
    {
        $payload = $message->payload();

        $msg = new AMQPMessage($payload, $message->getProperties());
        $this->getChannel($message->getPoolName())->basic_publish(
            $msg,
            $message->getExchange(),
            $message->getRoutingKey()
        );
    }

    public function declare(ProducerInterface $message): void
    {
        $builder = $message->getExchangeDeclareBuilder();

        $this->getChannel($message->getPoolName())->exchange_declare(
            $builder->getExchange(),
            $builder->getType(),
            $builder->isPassive(),
            $builder->isDurable(),
            $builder->isAutoDelete(),
            $builder->isInternal(),
            $builder->isNowait(),
            $builder->getArguments(),
            $builder->getTicket()
        );
    }
}
