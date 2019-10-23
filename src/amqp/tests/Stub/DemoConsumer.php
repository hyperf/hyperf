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

class DemoConsumer extends ConsumerMessage
{
    protected $exchange = 'hyperf';

    protected $routingKey = [
        'hyperf1',
        'hyperf2',
    ];

    protected $queue = 'hyperf';

    public function consume($data): string
    {
        return Result::ACK;
    }
}
