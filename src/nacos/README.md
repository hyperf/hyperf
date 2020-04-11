### hyperf-nacos
> Hyperf 框架下关于 Nacos 微服务的 php SDK

#### 安装
```shell
composer require daodao97/hyperf-nacos
```

#### 发布配置文件

```shell
php bin/hyperf.php vendor:publish daodao97/hyperf-nacos
```

#### 目录结构
```shell
./src
├── Config   配置的自动更新
│   ├── FetchConfigProcess.php
│   ├── OnPipeMessageListener.php
│   └── PipeMessage.php
├── ConfigProvider.php   Hyperf扩展配置
├── Helper   辅助函数
│   └── func.php
├── Lib   Nacos Api 封装
│   ├── AbstractNacos.php
│   ├── NacosConfig.php
│   ├── NacosInstance.php
│   ├── NacosOperator.php
│   └── NacosService.php
├── Listener  
│   ├── BootAppConfListener.php   启动时自动注册
│   └── OnShutdownListener.php   关闭服务时自动注销
├── Model   领域模型
│   ├── AbstractModel.php
│   ├── ConfigModel.php
│   ├── InstanceModel.php
│   └── ServiceModel.php
├── Process   心跳
│   └── InstanceBeatProcess.php
├── ThisInstance.php   当前节点
├── ThisService.php   当前服务
└── Util
    ├── Guzzle.php
    └── RemoteConfig.php
```

### 服务与实例

`BootAppConfListener.php` 将在系统启动完成时自动完成`实例注册`, `服务注册` 

如果需要在服务下线时自动注销服务, 请增加如下配置, 以监听 `Shutdown` 事件

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

#### 获取当前实例

```php
$instance = new ThisInstance();
```

#### 获取当前服务

```php
$service = new ThisService();
```

#### 获取一个服务的最优节点

```php
$instance = make(NacosInstance::class);

$service = new ServiceModel([
    'serviceName' => 'hyperf',
    'groupName' => 'api',
    'namespaceId' => '5ce9d1c1-6732-4ccc-ae1f-5139af86a845'
]);

$optimal = $instance->getOptimal($service);

```

### 配置中心

`BootAppConfListener.php` 系统启动时将拉取远程配置, 并合入`hyperf` 的 `Config`

`FetchConfigProcess.php` 自定义进程将监听配置, 若有更新将发送`PipeMessage` 到各服务`worker`进程, 并合入当前进程的 `Config`

如果服务如下配置
```php
// config/autoload/nacos.php

return [
    // ...other
    'configReloadInterval' => 3,
    // 远程配置合并节点, 默认 config 根节点
    'configAppendNode' => 'nacos_conf',
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
];
```
系统将自动监听`listenerConfig` 中的配置, 并将其合并入`hyperf Config` 对象的指定(`configAppendNode`) 节点, 可以用`config('nacos_conf.***')` 获取, 若没有配置 `configAppendNode` 项, 将会并入 `Config` 对象根节点. 

#### 依赖扩展

`ext-json`, `ext-yaml`, `ext-simplexml`
