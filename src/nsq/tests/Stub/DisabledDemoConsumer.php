<?php
declare(strict_types=1);

namespace HyperfTest\Nsq\Stub;

use Hyperf\Nsq\AbstractConsumer;
use Hyperf\Nsq\Message;
use Hyperf\Nsq\Result;

class DisabledDemoConsumer extends AbstractConsumer
{
    public function consume(Message $message): ?string
    {
        return Result::ACK;
    }

    public function isEnable(): bool
    {
        return false;
    }
}