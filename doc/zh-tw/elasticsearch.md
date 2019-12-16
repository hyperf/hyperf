# Elasticsearch

[hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) 主要為 [elasticsearch-php](https://github.com/elastic/elasticsearch-php) 進行了客戶端物件建立的工廠類封裝，[elasticsearch-php](https://github.com/elastic/elasticsearch-php) 預設使用 `Guzzle Ring` 客戶端，在 [hyperf/guzzle](https://github.com/hyperf/guzzle) 中我們實現了協程版本的 `Handler`，所以可以直接使用 `Hyperf\Elasticsearch\ClientBuilderFactory` 建立一個新的 `Builder`。

## 安裝

```bash
composer require hyperf/elasticsearch
```
## 使用

### 使用 `ClientBuilderFactory` 建立客戶端

```php
<?php

use Hyperf\Elasticsearch\ClientBuilderFactory;

// 如果在協程環境下建立，則會自動使用協程版的 Handler，非協程環境下無改變
$builder = $this->container->get(ClientBuilderFactory::class)->create();

$client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

$info = $client->info();
```

### 自行建立客戶端

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

