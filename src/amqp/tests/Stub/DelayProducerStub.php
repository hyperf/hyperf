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

namespace HyperfTest\Amqp\Stub;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerDelayedMessageTrait;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Message\Type;

#[Producer]
class DelayProducerStub extends ProducerMessage
{
    use ProducerDelayedMessageTrait;

    protected string $exchange = 'ext.hyperf.delay';

    protected string|Type $type = Type::DIRECT;

    protected array|string $routingKey = '';

    public function __construct($data)
    {
        $this->payload = $data;
    }
}
