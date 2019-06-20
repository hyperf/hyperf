# JSON RPC 服务

JSON RPC 是一种基于 JSON 格式的轻量级的 RPC 协议标准，易于使用和阅读。在 Hyperf 里由 [hyperf/json-rpc](https://github.com/hyperf-cloud/json-rpc) 组件来实现，可自定义基于 HTTP 协议来传输，或直接基于 TCP 协议来传输。

# 安装

```bash
composer require hyperf/json-rpc
```

# 使用

服务有两种角色，一种是 `服务提供者(ServiceProvider)`，即为其它服务提供服务的服务，另一种是 `服务消费者(ServiceConsumer)`，即依赖其它服务的服务，一个服务既可能是 `服务提供者(ServiceProvider)`，同时又是 `服务消费者(ServiceConsumer)`。而两者直接可以通过 `服务契约` 来定义和约束接口的调用，在 Hyperf 里，可直接理解为就是一个 `接口类(Interface)`，通常来说这个接口类会同时出现在提供者和消费者下。

## 定义服务提供者

目前仅支持通过注解的形式来定义 `服务提供者(ServiceProvider)`，后续迭代会增加配置的形式。   
我们可以直接通过 `@RpcService` 注解对一个类进行定义即可发布这个服务了：

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * @RpcService(name="CaculatorService", protocol="jsonrpc-http", server="jsonrpc-http")
 */
class CaculatorService implements CalculatorServiceInterface
{
    // 实现一个加法方法，这里简单的认为参数都是 int 类型
    public function caculate(int $a, int $b): int
    {
        // 这里是服务方法的具体实现
        return $a + $b;
    }
}
```

`@RpcService` 共有 `4` 个参数：   
`name` 属性为定义该服务的名称，这里定义一个全局唯一的名字即可，Hyperf 会根据该属性生成对应的 ID 注册到服务中心去；   
`protocol` 属性为定义该服务暴露的协议，目前仅支持 `jsonrpc` 和 `jsonrpc-http`，分别对应于 TCP 协议和 HTTP 协议下的两种协议，默认值为 `jsonrpc-http`，这里的值对应在 `Hyperf\Rpc\ProtocolManager` 里面注册的协议的 `key`，这两个本质上都是 JSON RPC 协议，区别在于数据格式化、数据打包、数据传输器等不同。   
`server` 属性为绑定该服务类发布所要承载的 `Server`，默认值为 `jsonrpc-http`，该属性对应 `config/autoload/server.php` 文件内 `servers` 下所对应的 `name`，这里也就意味着我们需要定义一个对应的 `Server`，我们下一章节具体阐述这里应该怎样去处理；   
`publishTo` 属性为定义该服务所要发布的服务中心，目前仅支持 `consul` 或为空，为空时代表不发布该服务到服务中心去，但也就意味着您需要手动处理服务发现的问题，当值为 `consul` 时需要对应配置好 [hyperf/consul](./consul.md) 组件的相关配置；

> 使用 `@RpcService` 注解需 use Hyperf\RpcServer\Annotation\RpcService; 命名空间。

### 定义 JSON RPC Server

HTTP Server (适配 `jsonrpc-http` 协议)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\SwooleEvent;

return [
    // 这里省略了该文件的其它配置
    'servers' => [
        [
            'name' => 'jsonrpc-http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9504,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                SwooleEvent::ON_REQUEST => [\Hyperf\JsonRpc\HttpServer::class, 'onRequest'],
            ],
        ],
    ],
];
```

TCP Server (适配 `jsonrpc` 协议)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\SwooleEvent;

return [
    // 这里省略了该文件的其它配置
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_TCP,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                SwooleEvent::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_eof_split' => true,
                'package_eof' => "\r\n",
            ],
        ],
    ],
];
```

## 发布到服务中心

目前仅支持发布服务到 `consul`，后续会增加其它服务中心。   
发布服务到 `consul` 在 Hyperf 也是非常容易的一件事情，通过 `composer require hyperf/consul` 加载 Consul 组件（如果已安装则可忽略该步骤），然后再在 `config/autoload/consul.php` 配置文件内配置您的 `Consul` 配置即可，示例如下：

```php
<?php

return [
    'uri' => 'http://127.0.0.1:8500',
];
```

配置完成后，在启动 Hyperf 服务时会自动地注册到服务中心去。

## 定义服务消费者

一个 `服务消费者(ServiceConsumer)` 可以理解为就是一个客户端类，但在 Hyperf 里您无需处理连接和请求相关的事情，只需要定义一个类及相关属性即可。（v1.1会提供动态代理实现的客户端，使之更加简单便捷）

```php
<?php

namespace App\JsonRpc;


use Hyperf\RpcClient\AbstractServiceClient;

class CaculatorService extends AbstractServiceClient implements CaculatorServiceInterface
{

    /**
     * 定义对应服务提供者的服务名称
     * @var string 
     */
    protected $serviceName = 'CaculatorService';
    
    /**
     * 定义对应服务提供者的服务协议
     * @var string 
     */
    protected $protocol = 'jsonrpc-http';

    public function add(int $a, int $b): int
    {
        return $this->__request(__FUNCTION__, compact('a', 'b'));
    }
}
```

这样我们便可以通过 `CaculatorService` 类来实现对服务的消费了，为了让这里的关系逻辑更加的合理，还应该在 `config/dependencies.php` 内定义 `CaculatorServiceInterface` 和 `CaculatorService` 的关系，示例如下：

```php
return [
    'dependencies' => [
        App\JsonRpc\CaculatorServiceInterface::class => App\JsonRpc\CaculatorService::class,
    ],
];
```

这样便可以通过注入 `CaculatorServiceInterface` 接口来使用客户端了。