# Elasticsearch

[elasticsearch-php](https://github.com/elastic/elasticsearch-php) 默认使用 Guzzle Ring 客户端，但在扩展设计中，又非完全解耦，所以我们重写了 `Hyperf\Elasticsearch\ClientBuilder` 和 `Hyperf\Elasticsearch\Connections\Connection`, 让其支持协程。

## 使用

[hyperf\elasticsearch](https://github.com/hyperf-cloud/elasticsearch) 的使用方法与官方 [elasticsearch-php](https://github.com/elastic/elasticsearch-php) 组件一致，只需要使用 `Hyperf\Elasticsearch\ClientBuilder` 来构造客户端即可。

```php
<?php

use Hyperf\Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()->setHosts(['http://127.0.0.1:9200'])->build();

var_dump($client->info());

```
