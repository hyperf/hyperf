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

namespace HyperfTest\Kafka\Stub;

use Hyperf\Kafka\AbstractConsumer;
use Hyperf\Kafka\Result;
use longlang\phpkafka\Consumer\ConsumeMessage;

class DemoConsumer extends AbstractConsumer
{
    public function consume(ConsumeMessage $message)
    {
        return Result::ACK;
    }
}
