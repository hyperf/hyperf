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
use Hyperf\Nats\Driver\NatsDriver;
use Hyperf\Nats\Encoders\JSONEncoder;

return [
    'default' => [
        'driver' => NatsDriver::class,
        'encoder' => JSONEncoder::class,
        'timeout' => 10.0,
        'options' => [
            'host' => '127.0.0.1',
            'port' => 4222,
            'user' => 'nats',
            'pass' => 'nats',
            'lang' => 'php',
        ],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => 60,
        ],
    ],
];
