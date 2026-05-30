# Elasticsearch

[hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) adalah sebuah
factory untuk pembuatan objek client untuk
[elasticsearch-php](https://github.com/elastic/elasticsearch-php). Secara
default, handler yang digunakan adalah client `Guzzle Ring`. Pada
[hyperf/guzzle](https://github.com/hyperf/guzzle), kami mengimplementasikan
`Handler` versi coroutine, sehingga kita dapat membuat `Builder` baru secara
langsung melalui `Hyperf\Elasticsearch\ClientBuilderFactory`.

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
