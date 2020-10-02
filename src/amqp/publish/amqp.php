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
return [
    'default' => [
        'host' => env('AMQP_HOST', 'localhost'),
        'port' => (int) env('AMQP_PORT', 5672),
        'user' => env('AMQP_USER', 'guest'),
        'password' => env('AMQP_PASSWORD', 'guest'),
        'vhost' => env('AMQP_VHOST', '/'),
        'concurrent' => [
            'limit' => 1,
        ],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
        ],
        'params' => [
            'insist' => false,
            'login_method' => 'AMQPLAIN',
            'login_response' => null,
            'locale' => 'en_US',
            'connection_timeout' => 3.0,
            'read_write_timeout' => 6.0,
            'context' => null,
            'keepalive' => true,
            'heartbeat' => 3,
            'close_on_destruct' => false,
        ],
    ],
];
