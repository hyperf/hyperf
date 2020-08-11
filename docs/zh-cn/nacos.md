# Nacos

一个 `Nacos` 的 `PHP` 协程客户端，与 `Hyperf` 的配置中心、微服务治理完美结合。

## 安装

```shell
composer require hyperf/nacos
```

### 发布配置文件

```shell
php bin/hyperf.php vendor:publish hyperf/nacos
```

## 完整配置
```php
// config/autoload/nacos.php

return [
    // The nacos host info
    'host' => '127.0.0.1',
    'port' => 8848,

    // 服务注册，新服务会自动创建后再注册实例
    'service' => [
        'enable' => true, // 是否启用服务注册
        'namespace_id' => 'namespace_id', // 命名空间ID
        'group_name' => 'api', // 服务组
        'service_name' => 'hyperf', // 服务名
        'protect_threshold' => 0.5, // 服务保护阈值
        'cluster' => 'DEFAULT', // 实例所处虚拟集群
        'weight' => 80, // 实例权重
        'ephemeral' => true, // 是否临时实例
        'beat_enable' => true, // 是否发送实例心跳，临时实例如果不发送心跳，会被检测为非健康实例
        'beat_interval' => 5, // 心跳周期
        'remove_node_when_server_shutdown' => true, // 关机是否注销实例
        'load_balancer' => 'random', // 负载均衡策略
    ],

    // 配置中心
    'config' => [
        'enable' => true, // 是否启用配置中心
        // 是否使用独立进程来拉取config，如果否则将在worker内以协程方式拉取
        'use_standalone_process' => true,
        'reload_interval' => 3, //配置刷新周期
        'listener_config' => [
            [
                'tenant' => 'namespace_id', // 命名空间ID
                'group' => 'DEFAULT_GROUP', // 配置分组
                'data_id' => 'hyperf-service-config', // 配置ID
                'mapping_path' => 'xxx.yyy', // 使用config('xxx.yyy')获取配置；为空则使用data_id作为配置路径，config('hyperf-service-config')
            ],
            [
                'tenant' => 'namespace_id',
                'group' => 'DEFAULT_GROUP',
                'data_id' => 'hyperf-service-config-yml',
                'type' => 'yml', // 配置类型
            ]
        ]
    ]
];
```

## 服务与实例

`MainWorkerStartListener.php` 将在系统启动完成时自动完成 `实例注册`，`服务注册` 

如果需要在服务下线时自动注销服务，请增加如下配置，以监听 `Shutdown` 事件

```php
// config/autoload/server.php

return [
    // ...other
    'callbacks' => [
        // ...other
        SwooleEvent::ON_SHUTDOWN => [Hyperf\Framework\Bootstrap\ShutdownCallback::class, 'onShutdown']
    ]
];
```

### 获取当前实例

```php
use Hyperf\Utils\ApplicationContext;
use Hyperf\Nacos\Service\Instance;

$container = ApplicationContext::getContainer();
$instance = $container->get(Instance::class);
```

### 获取当前服务

```php
use Hyperf\Utils\ApplicationContext;
use Hyperf\Nacos\Service\Service;

$container = ApplicationContext::getContainer();
$service = $container->get(Service::class);
```

### 获取一个服务的最优节点

```php
use Hyperf\Utils\ApplicationContext;
use Hyperf\Nacos\Api\NacosInstance;

$container = ApplicationContext::getContainer();
$nacosInstance = $container->get(NacosInstance::class);

$service = new ServiceModel([
    'service_name' => 'hyperf',
    'group_name' => 'api',
    'namespace_id' => '5ce9d1c1-6732-4ccc-ae1f-5139af86a845'
]);

$optimal = $nacosInstance->getOptimal($service);

```

## 配置中心

`MainWorkerStartListener.php` 系统启动时将拉取远程配置, 并合入`hyperf` 的 `Config`

`FetchConfigProcess.php` 自定义进程将监听配置, 若有更新将发送 `PipeMessage` 到各服务`worker` 进程, 并合入当前进程的 `Config`

如果服务如下配置
```php
// config/autoload/nacos.php

return [
    // ...other
    // 配置中心
    'config' => [
        'enable' => true, // 是否启用配置中心
        'reload_interval' => 3, //配置刷新周期
        'listener_config' => [
            [
                'tenant' => 'namespace_id', // 命名空间ID
                'group' => 'DEFAULT_GROUP', // 配置分组
                'data_id' => 'hyperf-service-config', // 配置ID
                'mapping_path' => 'xxx.yyy', // 使用config('xxx.yyy')获取配置；为空则使用data_id作为配置路径，config('hyperf-service-config')
            ],
            [
                'tenant' => 'namespace_id',
                'group' => 'DEFAULT_GROUP',
                'data_id' => 'hyperf-service-config-yml',
                'type' => 'yml', // 配置类型
            ]
        ]
    ]
];
```

第一项配置通过 `config('xxx.yyy')` 获取，第二项配置通过 `config('hyperf-service-config-yml')` 获取。

