# 连接池

## 安装

```bash
composer require hyperf/pool
```

## 为什么需要连接池？

当并发量很低的时候，连接可以临时建立，但当服务吞吐达到几百、几千的时候，频繁 `建立连接 Connect` 和 `销毁连接 Close` 就有可能会成为服务的一个瓶颈，那么当服务启动的时候，先建立好若干个连接并存放于一个队列中，当需要使用时从队列中取出一个并使用，使用完后再反还到队列去，而对这个队列数据结构进行维护的，就是连接池。

## 使用连接池

对于 Hyperf 官方提供的组件，都是已经对接好连接池的，在使用上无任何的感知，底层自动完成连接的取用和归还。

## 自定义连接池

定义一个连接池首先需要实现一个继承了 `Hyperf\Pool\Pool` 的子类并实现抽象方法 `createConnection`，并返回一个实现了 `Hyperf\Contract\ConnectionInterface` 接口的对象，这样您创建的连接池对象就已经完成了，如下示例：
```php
<?php
namespace App\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;

class MyConnectionPool extends Pool
{
    public function createConnection(): ConnectionInterface
    {
        return new MyConnection();
    }
}
``` 
这样便可以通过对实例化后的 `MyConnectionPool` 对象调用 `get(): ConnectionInterface` 和 `release(ConnectionInterface $connection): void` 方法执行连接的取用和归还了。   

## SimplePool

这里框架提供了一个非常简单的连接池实现。

```php
<?php

use Hyperf\Pool\SimplePool\PoolFactory;
use Swoole\Coroutine\Http\Client;

$factory = $container->get(PoolFactory::class);

$pool = $factory->get('your pool name', function () use ($host, $port, $ssl) {
    return new Client($host, $port, $ssl);
}, [
    'max_connections' => 50
]);

$connection = $pool->get();

$client = $connection->getConnection(); // 即上述 Client.

// Do something.

$connection->release();

```
