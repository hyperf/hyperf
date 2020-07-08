<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Amqp\Stub;

use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;

class QosConsumer extends ConsumerMessage
{
    protected $exchange = 'qos';

    protected $queue = 'qos.rk.queue';

    protected $routingKey = 'qos.rk';

    protected $qos = [
        'prefetch_count' => 10,
    ];

    public function consume($data): string
    {
        return Result::ACK;
    }
}
