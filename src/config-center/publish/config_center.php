<?php
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