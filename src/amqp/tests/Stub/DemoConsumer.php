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

class DemoConsumer extends ConsumerMessage
{
    protected string $exchange = 'hyperf';

    protected array|string $routingKey = [
        'hyperf1',
        'hyperf2',
    ];

    protected ?string $queue = 'hyperf';

    public function consume($data): Result
    {
        return Result::ACK;
    }
}
