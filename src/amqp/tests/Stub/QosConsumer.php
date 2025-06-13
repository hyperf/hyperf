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

use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;

class QosConsumer extends ConsumerMessage
{
    protected string $exchange = 'qos';

    protected ?string $queue = 'qos.rk.queue';

    protected array|string $routingKey = 'qos.rk';

    protected ?array $qos = [
        'prefetch_count' => 10,
    ];

    public function consume($data): Result
    {
        return Result::ACK;
    }
}
