# Guzzle HTTP 客户端

[hyperf/guzzle](https://github.com/hyperf/guzzle) 组件基于 Guzzle 进行协程处理，通过 Swoole HTTP 客户端作为协程驱动替换到 Guzzle 内，以达到 HTTP 客户端的协程化。

## 安装

```bash
composer require hyperf/guzzle
```

## 使用

只需要该组件内的 `Hyperf\Guzzle\CoroutineHandler` 作为处理器设置到 Guzzle 客户端内即可转为协程化运行，为了方便创建协程的 Guzzle 对象，我们提供了一个工厂类 `Hyperf\Guzzle\ClientFactory` 来便捷的创建客户端，代码示例如下：

```php
<?php 
use Hyperf\Guzzle\ClientFactory;

class Foo
{
    private ClientFactory $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    
    public function bar()
    {
        // $options 等同于 GuzzleHttp\Client 构造函数的 $config 参数
        $options = [];
        // $client 为协程化的 GuzzleHttp\Client 对象
        $client = $this->clientFactory->create($options);
    }
}
```

### 使用 ^7.0 版本

组件对 `Guzzle` 的依赖已经由 `^6.3` 改为 `^6.3 | ^7.0`，默认情况下已经可以安装 `^7.0` 版本，但以下组件会与 `^7.0` 冲突。

- hyperf/metric

可以主动执行以下操作，解决冲突

```
composer require "promphp/prometheus_client_php:2.2.1"
```

- overtrue/flysystem-cos

因为依赖库依赖了 `guzzlehttp/guzzle-services`，而其不支持 `^7.0`，故暂时无法解决。

## 使用 Swoole 配置

有时候我们想直接修改 `Swoole` 配置，所以我们也提供了相关配置项，不过这项配置在 `Curl Guzzle 客户端` 中是无法生效的，所以谨慎使用。

> 这项配置会替换原来的配置，比如以下 timeout 会被 10 替换。

```php
<?php
use GuzzleHttp\Client;
use Hyperf\Guzzle\CoroutineHandler;
use GuzzleHttp\HandlerStack;

$client = new Client([
    'base_uri' => 'http://127.0.0.1:8080',
    'handler' => HandlerStack::create(new CoroutineHandler()),
    'timeout' => 5,
    'swoole' => [
        'timeout' => 10,
        'socket_buffer_size' => 1024 * 1024 * 2,
    ],
]);

$response = $client->get('/');

```

## 连接池

Hyperf 除了实现了 `Hyperf\Guzzle\CoroutineHandler` 外，还基于 `Hyperf\Pool\SimplePool` 实现了 `Hyperf\Guzzle\PoolHandler`。

### 原因

简单来说，主机 TCP 连接数 是有上限的，当我们并发大到超过这个上限值时，就导致请求无法正常建立。另外，TCP 连接结束后还会有一个 TIME-WAIT 阶段，所以也无法实时释放连接。这就导致了实际并发可能远低于 TCP 上限值。所以，我们需要一个连接池来维持这个阶段，尽量减少 TIME-WAIT 造成的影响，让 TCP 连接进行复用。

### 使用

```php
<?php
use GuzzleHttp\Client;
use Hyperf\Coroutine\Coroutine;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;

$handler = null;
if (Coroutine::inCoroutine()) {
    $handler = make(PoolHandler::class, [
        'option' => [
            'max_connections' => 50,
        ],
    ]);
}

// 默认的重试Middleware
$retry = make(RetryMiddleware::class, [
    'retries' => 1,
    'delay' => 10,
]);

$stack = HandlerStack::create($handler);
$stack->push($retry->getMiddleware(), 'retry');

$client = make(Client::class, [
    'config' => [
        'handler' => $stack,
    ],
]);
```

另外，框架还提供了 `HandlerStackFactory` 来方便创建上述的 `$stack`。

```php
<?php
use Hyperf\Guzzle\HandlerStackFactory;
use GuzzleHttp\Client;

$factory = new HandlerStackFactory();
$stack = $factory->create();

$client = make(Client::class, [
    'config' => [
        'handler' => $stack,
    ],
]);
```

## 使用 `ClassMap` 替换 `GuzzleHttp\Client`

如果第三方组件并没有提供可以替换 `Handler` 的接口，我们也可以通过 `ClassMap` 功能，直接替换 `Client` 来达到将客户端协程化的目的。

> 当然，也可以使用 SWOOLE_HOOK 达到相同的目的。

代码示例如下：

class_map/GuzzleHttp/Client.php

```php
<?php
namespace GuzzleHttp;

use GuzzleHttp\Psr7;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Coroutine\Coroutine;

class Client implements ClientInterface
{
    // 代码省略其他不变的代码

    public function __construct(array $config = [])
    {
        $inCoroutine = Coroutine::inCoroutine();
        if (!isset($config['handler'])) {
            // 对应的 Handler 可以按需选择 CoroutineHandler 或 PoolHandler
            $config['handler'] = HandlerStack::create($inCoroutine ? new CoroutineHandler() : null);
        } elseif ($inCoroutine && $config['handler'] instanceof HandlerStack) {
            $config['handler']->setHandler(new CoroutineHandler());
        } elseif (!is_callable($config['handler'])) {
            throw new \InvalidArgumentException('handler must be a callable');
        }

        // Convert the base_uri to a UriInterface
        if (isset($config['base_uri'])) {
            $config['base_uri'] = Psr7\uri_for($config['base_uri']);
        }

        $this->configureDefaults($config);
    }
}

```

config/autoload/annotations.php

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client;

return [
    'scan' => [
        // ...
        'class_map' => [
            Client::class => BASE_PATH . '/class_map/GuzzleHttp/Client.php',
        ],
    ],
];
```
