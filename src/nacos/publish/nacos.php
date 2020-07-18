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
return [
    'host' => '127.0.0.1',
    'port' => '8848',
    // The service info.
    // serviceName, groupName, namespaceId,
    // protectThreshold, metadata, selector
    'service' => [
        'serviceName' => 'hyperf',
        'groupName' => 'api',
        'namespaceId' => 'namespace_id',
        'protectThreshold' => 0.5,
    ],
    // The client info.
    // serviceName, groupName, weight, enabled,
    // healthy, metadata, clusterName, namespaceId, ephemeral
    'client' => [
        'serviceName' => 'hyperf',
        'weight' => 80,
        'cluster' => 'DEFAULT',
        'ephemeral' => true,
        'beatEnable' => true,
        'beatInterval' => 5,
        'namespaceId' => 'namespace_id', // It must be equal with service.namespaceId.
    ],
    'delete_service_when_shutdown' => true,
    'config_reload_interval' => 3,
    'config_append_node' => 'custom',
    'listener_config' => [
        // dataId, group, tenant, type, content
        [
            'dataId' => 'hyperf-service-config',
            'group' => 'DEFAULT_GROUP',
        ],
        [
            'dataId' => 'hyperf-service-config-yml',
            'group' => 'DEFAULT_GROUP',
            'type' => 'yml',
        ],
    ],
    'load_balancer' => 'random',
];
