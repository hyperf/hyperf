# Elasticsearch

[hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) mainly provides a factory class encapsulation for creating client objects for [elasticsearch-php](https://github.com/elastic/elasticsearch-php). [elasticsearch-php](https://github.com/elastic/elasticsearch-php) uses the `Guzzle Ring` client by default. In [hyperf/guzzle](https://github.com/hyperf/guzzle), we implemented a coroutine version of `Handler`, so you can directly use `Hyperf\Elasticsearch\ClientBuilderFactory` to create a new `Builder`.

## Installation

```bash
composer require hyperf/elasticsearch
```

## Usage

### Using `ClientBuilderFactory` to create a client

```php
<?php

use Hyperf\Elasticsearch\ClientBuilderFactory;

// If created in a coroutine environment, the coroutine version of Handler will be automatically used. There is no change in a non-coroutine environment.
$builder = $this->container->get(ClientBuilderFactory::class)->create();

$client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

$info = $client->info();
```

### Creating the client by yourself

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

### How to set username and password

When the search engine needs to use a username and password, for example, if you have purchased `Elasticsearch` Enterprise Edition, we can use the following `host` to access the search engine.

```
http://username:password@xxxx.aliyuncs.com:9200
```
