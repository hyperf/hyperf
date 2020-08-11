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
    // The nacos host info
    'host' => '127.0.0.1',
    'port' => 8848,

    'service' => [
        'enable' => true,
        'namespace_id' => 'namespace_id',
        'group_name' => 'api',
        'service_name' => 'hyperf',
        'protect_threshold' => 0.5,
        'cluster' => 'DEFAULT',
        'weight' => 80,
        'ephemeral' => true,
        'beat_enable' => true,
        'beat_interval' => 5,
        'remove_node_when_server_shutdown' => true,
        'load_balancer' => 'random',
    ],

    'config' => [
        'enable' => true,
        'use_standalone_process' => true,
        'reload_interval' => 3,
        'listener_config' => [
            [
                'tenant' => 'namespace_id',
                'group' => 'DEFAULT_GROUP',
                'data_id' => 'hyperf-service-config',
                'mapping_path' => 'xxx.yyy',
            ],
            [
                'tenant' => 'namespace_id',
                'group' => 'DEFAULT_GROUP',
                'data_id' => 'hyperf-service-config-yml',
                'type' => 'yml',
            ],
        ],
    ],
];
