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
    // 服务配置 serviceName, groupName, namespaceId
    // protectThreshold, metadata, selector
    'service' => [
        'serviceName' => 'hyperf',
        'groupName' => 'api',
        'namespaceId' => 'namespace_id',
        'protectThreshold' => 0.5,
    ],
    // 节点配置 serviceName, groupName, weight, enabled,
    // healthy, metadata, clusterName, namespaceId, ephemeral
    'client' => [
        'serviceName' => 'hyperf',
        'weight' => 80,
        'cluster' => 'DEFAULT',
        'ephemeral' => true,
        'beatEnable' => true,
        'beatInterval' => 5,
        'namespaceId' => 'namespace_id', // 注意此处必须和service保持一致
    ],
    'deleteServiceWhenShutdown' => true, // 默认false
    'config_reload_interval' => 3,
    // 远程配置合并节点, 默认 config 根节点
    'config_append_node' => 'custom',
    'listenerConfig' => [
        // 配置项 dataId, group, tenant, type, content
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
    // 负载策略 random, RoundRobin, WeightedRandom, WeightedRoundRobin
    'loadBalancer' => 'random',
];
