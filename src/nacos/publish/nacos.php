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
use Hyperf\Nacos\Constants;

return [
    'enable' => true,
    // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
    // 'url' => '',
    // The nacos host info
    'host' => '127.0.0.1',
    'port' => 8848,
    // The nacos account info
    'username' => null,
    'password' => null,
    'config_merge_mode' => Constants::CONFIG_MERGE_OVERWRITE,
    // The service info.
    'service' => [
        'service_name' => 'hyperf',
        'group_name' => 'api',
        'namespace_id' => 'namespace_id',
        'protect_threshold' => 0.5,
    ],
    // The client info.
    'client' => [
        'service_name' => 'hyperf',
        'group_name' => 'api',
        'weight' => 80,
        'cluster' => 'DEFAULT',
        'ephemeral' => true,
        'beat_enable' => true,
        'beat_interval' => 5,
        'namespace_id' => 'namespace_id', // It must be equal with service.namespaceId.
    ],
    'remove_node_when_server_shutdown' => true,
    'config_reload_interval' => 3,
    'config_append_node' => 'nacos_config',
    'listener_config' => [
        // dataId, group, tenant, type, content
        //[
        //    'tenant' => 'tenant', // corresponding with service.namespaceId
        //    'data_id' => 'hyperf-service-config',
        //    'group' => 'DEFAULT_GROUP',
        //],
        //[
        //    'data_id' => 'hyperf-service-config-yml',
        //    'group' => 'DEFAULT_GROUP',
        //    'type' => 'yml',
        //],
    ],
    'load_balancer' => 'random',
];
