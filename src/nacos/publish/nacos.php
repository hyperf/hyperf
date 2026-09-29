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
    // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
    // 'uri' => 'http://127.0.0.1:8848/',
    // The nacos host info
    'host' => '127.0.0.1',
    'port' => 8848,
    // The nacos account info
    'username' => null,
    'password' => null,
    'guzzle' => [
        'config' => null,
    ],
    // Only support for nacos v2.
    'grpc' => [
        'enable' => false,
        'heartbeat' => 10,
    ],
    'service' => [
        'enable' => true,
        'service_name' => 'service_name',
        'namespace_id' => 'public',
        'group_name' => 'api',
        'protect_threshold' => 0.0,
        'metadata' => [
        ],
        'selector' => [
        ],

        'instance' => [
            // If the instance is ephemeral, it will be removed when the service is down.
            // If the instance is not ephemeral, it will be removed when the service is removed.
            // If the instance is null, it will be determined by the nacos server.
            'ephemeral' => null,
            // The cluster name of the instance.
            // If the cluster name is null, it will be determined by the nacos server.
            'cluster' => null,
            // The weight of the instance.
            // If the weight is null, it will be determined by the nacos server.
            'weight' => null,
            // The heartbeat interval of the instance.
            // If the heartbeat interval is null, it will be determined by the nacos server.
            'heartbeat' => 5,
        ],
    ],
];
