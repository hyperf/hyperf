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

namespace Hyperf\Kafka\Constants;

use longlang\phpkafka\Consumer\Assignor\RangeAssignor;
use longlang\phpkafka\Consumer\Assignor\RoundRobinAssignor;

class KafkaStrategy
{
    public const RANGE_ASSIGNOR = RangeAssignor::class;

    public const ROUND_ROBIN_ASSIGNOR = RoundRobinAssignor::class;
}
