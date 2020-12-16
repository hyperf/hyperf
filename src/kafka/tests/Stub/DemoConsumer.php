<?php


namespace HyperfTest\Kafka\Stub;


use Hyperf\Kafka\AbstractConsumer;
use Hyperf\Kafka\Result;
use longlang\phpkafka\Consumer\ConsumeMessage;

class DemoConsumer extends AbstractConsumer
{
    public function consume(ConsumeMessage $message): string
    {
        return Result::ACK;
    }
}
