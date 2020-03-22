<?php
declare(strict_types=1);

namespace HyperfTest\Nsq\Stub;

use Hyperf\Nsq\AbstractConsumer;
use Hyperf\Nsq\Message;
use Hyperf\Nsq\Result;

class DemoConsumer extends AbstractConsumer
{
    public function consume(Message $message): ?string
    {
        return Result::ACK;
    }
}