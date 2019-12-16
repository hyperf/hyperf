# Elasticsearch

[hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) 主要为 [elasticsearch-php](https://github.com/elastic/elasticsearch-php) 进行了客户端对象创建的工厂类封装，[elasticsearch-php](https://github.com/elastic/elasticsearch-php) 默认使用 `Guzzle Ring` 客户端，在 [hyperf/guzzle](https://github.com/hyperf/guzzle) 中我们实现了协程版本的 `Handler`，所以可以直接使用 `Hyperf\Elasticsearch\ClientBuilderFactory` 创建一个新的 `Builder`。

## 安装

```bash
composer require hyperf/elasticsearch
```
## 使用

### 使用 `ClientBuilderFactory` 创建客户端

```php
<?php

use Hyperf\Elasticsearch\ClientBuilderFactory;

// 如果在协程环境下创建，则会自动使用协程版的 Handler，非协程环境下无改变
$builder = $this->container->get(ClientBuilderFactory::class)->create();

$client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

$info = $client->info();
```

### 自行创建客户端

```php
<?php

use Elasticsearch\ClientBuilder;
use Hyperf\Guzzle\RingPHP\PoolHandler;
use Swoole\Coroutine;

$builder = ClientBuilder::create();
if (Coroutine::getCid() > 0) {
    $handler = make(PoolHandler::class, [
        'option' => [
            'max_connections' => 50,
        ],
    ]);
    $builder->setHandler($handler);
}

$client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

$info = $client->info();
```

