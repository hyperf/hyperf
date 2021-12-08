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

```php
<?php

declare(strict_types=1);

return [
    // 无法使用 IP 端口形式的开发者，直接配置 url 即可
    // 'url' => '',
    'host' => '127.0.0.1',
    'port' => 8848,
    'username' => null,
    'password' => null,
    'guzzle' => [
        'config' => null,
    ],
];

```

## 服务与实例

当前组件仍然保留了之前提供的服务注册功能。

只需要安装 `hyperf/service-governance-nacos` 组件，然后配置以下监听器和自定义进程即可。

`Hyperf\ServiceGovernanceNacos\Listener\MainWorkerStartListener`
`Hyperf\ServiceGovernanceNacos\Listener\OnShutdownListener`
`Hyperf\ServiceGovernanceNacos\Process\InstanceBeatProcess`

然后增加如下配置，以监听 `Shutdown` 事件

- config/autoload/server.php

```php
<?php
use Hyperf\Server\Event;
return [
    // ...other
    'callbacks' => [
        // ...other
        Event::ON_SHUTDOWN => [Hyperf\Framework\Bootstrap\ShutdownCallback::class, 'onShutdown']
    ]
];
```

