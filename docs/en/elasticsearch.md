# Elasticsearch

[hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) is a factory for client object creation for [elasticsearch-php](https://github.com/elastic/elasticsearch-php), defaults handler is `Guzzle Ring` client, at [hyperf/guzzle](https://github.com/hyperf/guzzle) we implemented the `Handler` of the coroutine version, so we can create a new `Builder` directly via `Hyperf\Elasticsearch\ClientBuilderFactory`.

## Installation

```bash
composer require hyperf/elasticsearch
```
## Usage

### Create a Client

```php
<?php

use Hyperf\Elasticsearch\ClientBuilderFactory;

// If created in coroutine environment will use coroutine handler, if created in non-coroutine environment will not change.
$builder = $this->container->get(ClientBuilderFactory::class)->create();

$client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

$info = $client->info();
```
