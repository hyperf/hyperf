<?php

namespace Hyperf\Kafka\Constants;

use longlang\phpkafka\Consumer\Assignor\RangeAssignor;
use longlang\phpkafka\Consumer\Assignor\RoundRobinAssignor;

class KafkaStrategy
{
    const RANGE_ASSIGNOR = RangeAssignor::class;

    const ROUND_ROBIN_ASSIGNOR = RoundRobinAssignor::class;
}
