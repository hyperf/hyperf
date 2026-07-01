# Introduction

Hyperf provides support for externalized configuration in distributed systems, and natively adapts to:

- [ctripcorp/apollo](https://github.com/ctripcorp/apollo), an open-source project by Trip.com, supported by the [hyperf/config-apollo](https://github.com/hyperf/config-apollo) component.
- [Application Config Manager (ACM)](https://help.aliyun.com/product/59604.html), a free configuration center service provided by Alibaba Cloud, supported by the [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) component.
- ETCD
- Nacos
- Zookeeper

## Why use a Configuration Center?

With the development of business and the upgrade of microservice architecture, the number of services and application configurations is increasing (various microservices, server addresses, parameters). Traditional configuration file methods and database methods may no longer meet the needs of developers for configuration management. At the same time, configuration management may involve ACL permission management, configuration version management and rollback, format validation, configuration canary releases, cluster configuration isolation, and more, as well as:

- Security: Configurations are stored in version control systems along with source code, which can easily lead to configuration leaks.
- Timeliness: Modifying configurations requires each server and each application to be modified and the service restarted.
- Limitations: Dynamic adjustment is not supported, such as log switches, feature switches, etc.

Therefore, we can use a configuration center to uniformly manage related configurations in a scientific way.

## Installation

### Unified Configuration Center Access Layer

```bash
composer require hyperf/config-center
```

### For Apollo

```bash
composer require hyperf/config-apollo
```

### For Aliyun ACM

```bash
composer require hyperf/config-aliyun-acm
```

### For Etcd

```bash
composer require hyperf/config-etcd
```

### For Nacos

```bash
composer require hyperf/config-nacos
```

#### gRPC Bidirectional Stream

Traditional Nacos configuration center is based on short polling for configuration synchronization, which causes services to be unable to obtain the latest configuration within the polling interval. `Nacos V2` added support for gRPC bidirectional streams. If you want Nacos to push configuration changes to relevant services in a timely manner after discovering them, you can enable the gRPC bidirectional stream function by following these steps.

- First, install the necessary components:

```shell
composer require "hyperf/http2-client:3.1.*"
composer require "hyperf/grpc:3.1.*"
```

- Modify configuration:

Modify `config_center.drivers.nacos.client.grpc.enable` to `true`, as follows:

```php
<?php

declare(strict_types=1);

use Hyperf\ConfigApollo\PullMode;
use Hyperf\ConfigCenter\Mode;

return [
    'enable' => (bool) env('CONFIG_CENTER_ENABLE', true),
    'driver' => env('CONFIG_CENTER_DRIVER', 'nacos'),
    'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'nacos' => [
            'driver' => Hyperf\ConfigNacos\NacosDriver::class,
            'merge_mode' => Hyperf\ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE,
            'interval' => 3,
            'default_key' => 'nacos_config',
            'listener_config' => [
                'nacos_config' => [
                    'tenant' => 'tenant', // corresponding with service.namespaceId
                    'data_id' => 'hyperf-service-config',
                    'group' => 'DEFAULT_GROUP',
                ],
            ],
            'client' => [
                // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
                // 'uri' => '',
                'host' => '127.0.0.1',
                'port' => 8848,
                'username' => null,
                'password' => null,
                'guzzle' => [
                    'config' => null,
                ],
                // Only support for nacos v2.
                'grpc' => [
                    'enable' => true,
                    'heartbeat' => 10,
                ],
            ],
        ],
    ],
];

```

- Next, start the service.

### For Zookeeper

```bash
composer require hyperf/config-zookeeper
```

## Accessing Configuration Center

### Configuration File

```php
<?php

declare(strict_types=1);

use Hyperf\ConfigCenter\Mode;

return [
    // Whether to enable the configuration center
    'enable' => (bool) env('CONFIG_CENTER_ENABLE', true),
    // The driver type used, corresponding to the key under the drivers configuration at the same level
    'driver' => env('CONFIG_CENTER_DRIVER', 'apollo'),
    // The operating mode of the configuration center, PROCESS mode is recommended for multi-process models, and COROUTINE mode is recommended for single-process models
    'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'apollo' => [
            'driver' => Hyperf\ConfigApollo\ApolloDriver::class,
            // Apollo Server
            'server' => 'http://127.0.0.1:9080',
            // Your AppId
            'appid' => 'test',
            // The cluster where the current application is located
            'cluster' => 'default',
            // The namespace that the current application needs to access, multiple can be configured
            'namespaces' => [
                'application',
            ],
            // Configuration update interval (seconds)
            'interval' => 5,
            // Strict mode, when false, the pulled configuration values are all string types; when true, the pulled configuration values will be converted to the original data type
            'strict_mode' => false,
            // Client IP
            'client_ip' => \Hyperf\Support\Network::ip(),
            // Pull configuration timeout
            'pullTimeout' => 10,
            // Pull configuration interval
            'interval_timeout' => 1,
        ],
        'nacos' => [
            'driver' => Hyperf\ConfigNacos\NacosDriver::class,
            // Configuration merging method, supports overwrite and merge
            'merge_mode' => Hyperf\ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE,
            'interval' => 3,
            // If the corresponding mapping key is not set, use the default key
            'default_key' => 'nacos_config',
            'listener_config' => [
                // dataId, group, tenant, type, content
                // Mapped configuration KEY => Actual configuration in Nacos
                'nacos_config' => [
                    'tenant' => 'tenant', // corresponding with service.namespaceId
                    'data_id' => 'hyperf-service-config',
                    'group' => 'DEFAULT_GROUP',
                ],
                'nacos_config.data' => [
                    'data_id' => 'hyperf-service-config-yml',
                    'group' => 'DEFAULT_GROUP',
                    'type' => 'yml',
                ],
            ],
            'client' => [
                // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
                // 'uri' => '',
                'host' => '127.0.0.1',
                'port' => 8848,
                'username' => null,
                'password' => null,
                'guzzle' => [
                    'config' => null,
                ],
            ],
        ],
        'aliyun_acm' => [
            'driver' => Hyperf\ConfigAliyunAcm\AliyunAcmDriver::class,
            // Configuration update interval (seconds)
            'interval' => 5,
            // Aliyun ACM endpoint, depends on your availability zone
            'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
            // Namespace that the current application needs to access
            'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
            // Data ID corresponding to your configuration
            'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
            // Group corresponding to your configuration
            'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
            // Access Key for your Aliyun account
            'access_key' => env('ALIYUN_ACM_AK', ''),
            // Secret Key for your Aliyun account
            'secret_key' => env('ALIYUN_ACM_SK', ''),
            'ecs_ram_role' => env('ALIYUN_ACM_RAM_ROLE', ''),
        ],
        'etcd' => [
            'driver' => Hyperf\ConfigEtcd\EtcdDriver::class,
            'packer' => Hyperf\Codec\Packer\JsonPacker::class,
            // Prefix of data to be synchronized
            'namespaces' => [
                '/application',
            ],
            // The mapping relationship between `Etcd` and `Config`. Keys that do not exist in the mapping will not be synchronized to `Config`
            'mapping' => [
                // etcd key => config key
                '/application/test' => 'test',
            ],
            // Configuration update interval (seconds)
            'interval' => 5,
            'client' => [
                # Etcd Client
                'uri' => 'http://127.0.0.1:2379',
                'version' => 'v3beta',
                'options' => [
                    'timeout' => 10,
                ],
            ],
        ],
        'zookeeper' => [
            'driver' => Hyperf\ConfigZookeeper\ZookeeperDriver::class,
            'server' => env('ZOOKEEPER_SERVER', '127.0.0.1:2181'),
            'path' => env('ZOOKEEPER_CONFIG_PATH', '/conf'),
            'interval' => 5,
        ],
    ],
];
```

If the configuration file does not exist, you can execute the `php bin/hyperf.php vendor:publish hyperf/config-center` command to generate it.

## Scope of Configuration Updates

In the default implementation, a `ConfigFetcherProcess` process pulls the configuration of the corresponding `namespace` from the Configuration Center Server according to the configured `interval`, and passes the new configuration to each Worker through IPC communication, updating it into the corresponding object within `Hyperf\Contract\ConfigInterface`.

It should be noted that updated configurations will only update the `Config` object, so this is limited to application-level or business-level configurations. It does not involve configuration changes at the framework level because framework-level configuration changes require restarting the service. If you have such requirements, you can also achieve this by implementing `ConfigFetcherProcess` yourself.

## Configuration Update Events

During the operation of the configuration center, when the configuration changes, it will trigger the `Hyperf\ConfigCenter\Event\ConfigChanged` event. You can listen to these events to meet your needs.

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\ConfigCenter\Event\ConfigChanged;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            ConfigChanged::class,
        ];
    }

    public function process(object $event)
    {
        var_dump($event);
    }
}
```
