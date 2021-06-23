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
    'enable' => env('CONFIG_CENTER_ENABLE', true),
    'driver' => env('CONFIG_CENTER_DRIVER', 'apollo'),
    'use_standalone_process' => env('CONFIG_CENTER_USE_STANDALONE_PROCESS', true),
    'drivers' => [
        'apollo' => [
            'driver' => \Hyperf\ConfigApollo\ApolloDriver::class,
            'server' => 'http://127.0.0.1:9080',
            'appid' => 'test',
            'cluster' => 'default',
            'namespaces' => [
                'application',
            ],
            'interval' => 5,
            'strict_mode' => false,
            'client_ip' => current(swoole_get_local_ip()),
            'pullTimeout' => 10,
            'interval_timeout' => 1,
        ],
        'nacos' => [
            'driver' => '',
        ],
        'aliyun_acm' => [
            'driver' => '',
        ],
        'etcd' => [
            'driver' => '',
        ],
        'zookeeper' => [
            'driver' => '',
        ],
    ],
];
