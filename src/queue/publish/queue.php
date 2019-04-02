<?php

return [
    'default' => [
        'driver' => \Hyperf\Queue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'timeout' => 2,
        'retry_seconds' => 5,
        'processes' => 1,
    ],
];
