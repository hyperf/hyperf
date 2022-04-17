# 服务注册

在进行服务拆分之后，服务的数量会变得非常多，而每个服务又可能会有非常多的集群节点来提供服务，那么为保障系统的正常运行，必然需要有一个中心化的组件完成对各个服务的整合，即将分散于各处的服务进行汇总，汇总的信息可以是提供服务的组件名称、地址、数量等，每个组件拥有一个监听设备，当本组件内的某个服务的状态变化时报告至中心化的组件进行状态的更新。服务的调用方在请求某项服务时首先到中心化组件获取可提供该项服务的组件信息（IP、端口等），通过默认或自定义的策略选择该服务的某一提供者进行访问，实现服务的调用。那么这个中心化的组件我们一般称之为 `服务中心`，在 Hyperf 里，我们实现了以 `Consul` 和 `Nacos` 为服务中心的组件支持，后续将适配更多的服务中心。

# 安装

## 安装统一接入层

```bash
composer require hyperf/service-governance
```

## 选择安装对应的适配器

服务注册支持 `Consul` 和 `Nacos`，按需引入对应的适配器组件

- Consul

```shell
composer require hyperf/service-governance-consul
```

- Nacos

```shell
composer require hyperf/service-governance-nacos
```

# 配置文件

组件由 `config/autoload/services.php` 配置文件来驱动，配置文件如下：

```php
return [
    'enable' => [
        // 开启服务发现
        'discovery' => true,
        // 开启服务注册
        'register' => true,
    ],
    // 服务消费者相关配置
    'consumers' => [],
    // 服务提供者相关配置
    'providers' => [],
    // 服务驱动相关配置
    'drivers' => [
        'consul' => [
            'uri' => 'http://127.0.0.1:8500',
            'token' => '',
            'check' => [
                'deregister_critical_service_after' => '90m',
                'interval' => '1s',
            ],
        ],
        'nacos' => [
            // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
            // 'url' => '',
            // The nacos host info
            'host' => '127.0.0.1',
            'port' => 8848,
            // The nacos account info
            'username' => null,
            'password' => null,
            'guzzle' => [
                'config' => null,
            ],
            'group_name' => 'api',
            'namespace_id' => 'namespace_id',
            'heartbeat' => 5,
            'ephemeral' => false, // 是否注册临时实例
        ],
    ],
];
```

# 注册服务

注册服务可通过 `@RpcService` 注解对一个类进行定义，即为发布这个服务了，目前 Hyperf 仅适配了 JSON RPC 协议，具体内容也可到 [JSON RPC 服务](zh-cn/json-rpc.md) 章节了解详情。

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // 实现一个加法方法，这里简单的认为参数都是 int 类型
    public function calculate(int $a, int $b): int
    {
        // 这里是服务方法的具体实现
        return $a + $b;
    }
}
```

`@RpcService` 共有 `4` 个参数：   
`name` 属性为定义该服务的名称，这里定义一个全局唯一的名字即可，Hyperf 会根据该属性生成对应的 ID 注册到服务中心去；   
`protocol` 属性为定义该服务暴露的协议，目前仅支持 `jsonrpc` 和 `jsonrpc-http`，分别对应于 TCP 协议和 HTTP 协议下的两种协议，默认值为 `jsonrpc-http`，这里的值对应在 `Hyperf\Rpc\ProtocolManager` 里面注册的协议的 `key`，这两个本质上都是 JSON RPC 协议，区别在于数据格式化、数据打包、数据传输器等不同。   
`server` 属性为绑定该服务类发布所要承载的 `Server`，默认值为 `jsonrpc-http`，该属性对应 `config/autoload/server.php` 文件内 `servers` 下所对应的 `name`，这里也就意味着我们需要定义一个对应的 `Server`；   
`publishTo` 属性为定义该服务所要发布的服务中心，目前仅支持 `consul`、`nacos` 或为空，为空时代表不发布该服务到服务中心去，但也就意味着您需要手动处理服务发现的问题，要使用此功能需安装 [hyperf/service-governance](https://github.com/hyperf/service-governance) 组件及对应的驱动依赖；

> 使用 `@RpcService` 注解需 `use Hyperf\RpcServer\Annotation\RpcService;` 命名空间。

## 自定义服务治理适配器

除了默认支持 `Consul`、`Nacos` 外，用户还可以根据自己的需要，注册自定义的适配器。

我们可以创建一个 FooService 实现 `Hyperf\ServiceGovernance\DriverInterface`

```php
<?php

declare(strict_types=1);

namespace App\ServiceGovernance;

use Hyperf\ServiceGovernance\DriverInterface;

class FooDriver implements DriverInterface
{
    public function getNodes(string $uri, string $name, array $metadata): array
    {
        return [];
    }

    public function register(string $name, string $host, int $port, array $metadata): void
    {
    }

    public function isRegistered(string $name, string $address, int $port, array $metadata): bool
    {
        return true;
    }
}
```

然后创建一个监听器，将其注册到 `DriverManager` 中即可。

```php
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
namespace App\ServiceGovernance\Listener;

use App\ServiceGovernance\FooDriver;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ServiceGovernance\DriverManager;

#[Listener]
class RegisterDriverListener implements ListenerInterface
{
    protected DriverManager $driverManager;

    public function __construct(DriverManager $manager)
    {
        $this->driverManager = $manager;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        $this->driverManager->register('foo', make(FooDriver::class));
    }
}

```
