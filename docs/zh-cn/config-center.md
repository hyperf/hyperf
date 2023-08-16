# 简介

Hyperf 为您提供了分布式系统的外部化配置支持，默认适配了:

- 由携程开源的 [ctripcorp/apollo](https://github.com/ctripcorp/apollo)，由 [hyperf/config-apollo](https://github.com/hyperf/config-apollo) 组件提供功能支持。
- 阿里云提供的免费配置中心服务 [应用配置管理(ACM, Application Config Manager)](https://help.aliyun.com/product/59604.html)，由 [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) 组件提供功能支持。
- ETCD
- Nacos
- Zookeeper

## 为什么要使用配置中心？

随着业务的发展，微服务架构的升级，服务的数量、应用的配置日益增多（各种微服务、各种服务器地址、各种参数），传统的配置文件方式和数据库的方式已经可能无法满足开发人员对配置管理的要求，同时对于配置的管理可能还会牵涉到 ACL 权限管理、配置版本管理和回滚、格式验证、配置灰度发布、集群配置隔离等问题，以及：

- 安全性：配置跟随源代码保存在版本管理系统中，容易造成配置泄漏
- 时效性：修改配置，需要每台服务器每个应用修改并重启服务
- 局限性：无法支持动态调整，例如日志开关、功能开关等   

因此，我们可以通过一个配置中心以一种科学的管理方式来统一管理相关的配置。

## 安装

### 配置中心统一接入层

```bash
composer require hyperf/config-center
```

### 使用 Apollo 需安装

```bash
composer require hyperf/config-apollo
```

### 使用 Aliyun ACM 需安装

```bash
composer require hyperf/config-aliyun-acm
```

### 使用 Etcd 需安装

```bash
composer require hyperf/config-etcd
```

### 使用 Nacos 需安装

```bash
composer require hyperf/config-nacos
```

#### GRPC 双向流

Nacos 传统的配置中心，是基于短轮询进行配置同步的，就会导致轮训间隔内，服务无法拿到最新的配置。`Nacos V2` 版本增加了 GRPC 双向流的支持，如果你想让 Nacos 在发现配置变更后，及时推送给相关服务。

可以按照以下步骤，开启 GRPC 双向流功能。

- 首先，我们安装必要的组件

```shell
composer require "hyperf/http2-client:~3.0.0"
composer require "hyperf/grpc:~3.0.0"
```

- 修改配置项

修改 `config_center.drivers.nacos.client.grpc.enable` 为 `true`，具体如下

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

- 接下里启动服务即可

### 使用 Zookeeper 需安装

```bash
composer require hyperf/config-zookeeper
```

## 接入配置中心

### 配置文件

```php
<?php

declare(strict_types=1);

use Hyperf\ConfigCenter\Mode;

return [
    // 是否开启配置中心
    'enable' => (bool) env('CONFIG_CENTER_ENABLE', true),
    // 使用的驱动类型，对应同级别配置 drivers 下的 key
    'driver' => env('CONFIG_CENTER_DRIVER', 'apollo'),
    // 配置中心的运行模式，多进程模型推荐使用 PROCESS 模式，单进程模型推荐使用 COROUTINE 模式
    'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'apollo' => [
            'driver' => Hyperf\ConfigApollo\ApolloDriver::class,
            // Apollo Server
            'server' => 'http://127.0.0.1:9080',
            // 您的 AppId
            'appid' => 'test',
            // 当前应用所在的集群
            'cluster' => 'default',
            // 当前应用需要接入的 Namespace，可配置多个
            'namespaces' => [
                'application',
            ],
            // 配置更新间隔（秒）
            'interval' => 5,
            // 严格模式，当为 false 时，拉取的配置值均为 string 类型，当为 true 时，拉取的配置值会转化为原配置值的数据类型
            'strict_mode' => false,
            // 客户端IP
            'client_ip' => \Hyperf\Utils\Network::ip(),
            // 拉取配置超时时间
            'pullTimeout' => 10,
            // 拉取配置间隔
            'interval_timeout' => 1,
        ],
        'nacos' => [
            'driver' => Hyperf\ConfigNacos\NacosDriver::class,
            // 配置合并方式，支持覆盖和合并
            'merge_mode' => Hyperf\ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE,
            'interval' => 3,
            // 如果对应的映射 key 没有设置，则使用默认的 key
            'default_key' => 'nacos_config',
            'listener_config' => [
                // dataId, group, tenant, type, content
                // 映射后的配置 KEY => Nacos 中实际的配置
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
            // 配置更新间隔（秒）
            'interval' => 5,
            // 阿里云 ACM 断点地址，取决于您的可用区
            'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
            // 当前应用需要接入的 Namespace
            'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
            // 您的配置对应的 Data ID
            'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
            // 您的配置对应的 Group
            'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
            // 您的阿里云账号的 Access Key
            'access_key' => env('ALIYUN_ACM_AK', ''),
            // 您的阿里云账号的 Secret Key
            'secret_key' => env('ALIYUN_ACM_SK', ''),
            'ecs_ram_role' => env('ALIYUN_ACM_RAM_ROLE', ''),
        ],
        'etcd' => [
            'driver' => Hyperf\ConfigEtcd\EtcdDriver::class,
            'packer' => Hyperf\Utils\Packer\JsonPacker::class,
            // 需要同步的数据前缀
            'namespaces' => [
                '/application',
            ],
            // `Etcd` 与 `Config` 的映射关系。映射中不存在的 `key`，则不会被同步到 `Config` 中
            'mapping' => [
                // etcd key => config key
                '/application/test' => 'test',
            ],
            // 配置更新间隔（秒）
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

如配置文件不存在可执行 `php bin/hyperf.php vendor:publish hyperf/config-center` 命令来生成。

## 配置更新的作用范围

在默认的功能实现下，是由一个 `ConfigFetcherProcess` 进程根据配置的 `interval` 来向 配置中心 Server 拉取对应 `namespace` 的配置，并通过 IPC 通讯将拉取到的新配置传递到各个 Worker 中，并更新到 `Hyperf\Contract\ConfigInterface` 对应的对象内。   
需要注意的是，更新的配置只会更新 `Config` 对象，故仅限应用层或业务层的配置，不涉及框架层的配置改动，因为框架层的配置改动需要重启服务，如果您有这样的需求，也可以通过自行实现 `ConfigFetcherProcess` 来达到目的。

## 配置更新事件

配置中心运行期间，但配置发生变化会对应触发 `Hyperf\ConfigCenter\Event\ConfigChanged` 事件，您可以进行对这些事件进行监听以满足您的需求。

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
