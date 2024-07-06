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

namespace Hyperf\Amqp\Event;

use Hyperf\Amqp\Message\ConsumerMessageInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class FailToConsume extends ConsumeEvent
{
    public function __construct(ConsumerMessageInterface $message, protected Throwable $throwable, protected AMQPMessage $amqpMessage)
    {
        parent::__construct($message);
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }

    public function getAMQPMessage(): AMQPMessage
    {
        return $this->amqpMessage;
    }
}
