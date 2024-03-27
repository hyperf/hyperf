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
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

class AfterConsume extends ConsumeEvent
{
    public function __construct(ConsumerMessageInterface $message, protected Result $result, protected AMQPMessage $amqpMessage)
    {
        parent::__construct($message);
    }

    public function getResult(): Result
    {
        return $this->result;
    }

    public function getAMQPMessage(): AMQPMessage
    {
        return $this->amqpMessage;
    }
}
