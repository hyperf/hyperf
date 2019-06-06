# Guzzle HTTP 客户端

[hyperf/guzzle](https://github.com/hyperf-cloud/guzzle) 组件基于 Guzzle 进行协程处理，通过 Swoole HTTP 客户端作为协程驱动替换到 Guzzle 内，以达到 HTTP 客户端的协程化。

## 安装

```bash
composer require hyperf/guzzle
```

## 使用

只需要该组件内的 `Hyperf\Guzzle\CoroutineHandler` 作为处理器设置到 Guzzle 客户端内即可转为协程化运行，为了方便创建协程的 Guzzle 对象，我们提供了一个工厂类 `Hyperf\Guzzle\ClientFactory` 来便捷的创建客户端，代码示例如下：

```php
<?php 
use Hyperf\Guzzle\ClientFactory;

class Foo {
    /**
     * @var \Hyperf\Guzzle\ClientFactory
     */
    private $clientFactory;
    
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

## 连接池

Hyperf 除了实现了 `Hyperf\Guzzle\CoroutineHandler` 外，还基于 `Hyperf\Pool\SimplePool` 实现了 `Hyperf\Guzzle\PoolHandler`。

### 原因

简单来说，主机 TCP连接数 是有上限的，当我们并发大到超过这个上限值时，就导致请求无法正常建立。另外，TCP连接结束后还会有一个 TIME-WAIT 阶段，所以也无法实时释放连接。这就导致了实际并发可能远低于 TCP 上限值。所以，我们需要一个连接池来维持这个阶段，尽量减少 TIME-WAIT 造成的影响，让TCP连接进行复用。

### 使用

```php
use GuzzleHttp\Client;
use Hyperf\Utils\Coroutine;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;

function default_guzzle_handler(): HandlerStack
{
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

    return $stack;
}

$client = make(Client::class, [
    'config' => [
        'handler' => default_guzzle_handler(),
    ],
]);
```