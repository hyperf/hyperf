# Elasticsearch

[elasticsearch-php](https://github.com/elastic/elasticsearch-php) 默认使用 Guzzle Ring 客户端，所以 [hyperf/guzzle](https://github.com/hyperf-cloud/guzzle) 中实现了协程版本的 Handler.

## 安装

```bash
composer require hyperf/elasticsearch
```
## 使用

```php
<?php

use Hyperf\Elasticsearch\ClientBuilderFactory;

$builder = $this->container->get(ClientBuilderFactory::class)->create()->setHosts(['http://127.0.0.1:9200']);

$client = $builder->build();

var_dump($client->info());
```
