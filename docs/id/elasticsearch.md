# Elasticsearch

[hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) menyediakan factory class untuk membuat objek client bagi [elasticsearch-php](https://github.com/elastic/elasticsearch-php). [elasticsearch-php](https://github.com/elastic/elasticsearch-php) memakai `Guzzle Ring` client secara default. Dalam [hyperf/guzzle](https://github.com/hyperf/guzzle), kami mengimplementasikan versi coroutine dari `Handler`, sehingga Anda bisa langsung menggunakan `Hyperf\Elasticsearch\ClientBuilderFactory` untuk membuat `Builder` baru.

## Instalasi

```bash
composer require hyperf/elasticsearch
```

## Penggunaan

### Menggunakan `ClientBuilderFactory` untuk membuat client

```php
<?php

use Hyperf\Elasticsearch\ClientBuilderFactory;

// Jika dibuat di lingkungan coroutine, versi coroutine dari Handler akan otomatis digunakan. Tidak ada perubahan di lingkungan non-coroutine.
$builder = $this->container->get(ClientBuilderFactory::class)->create();

$client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

$info = $client->info();
```

### Membuat client sendiri

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

### Cara mengatur username dan password

Ketika search engine memerlukan username dan password, misalnya jika Anda membeli `Elasticsearch` Enterprise Edition, kita dapat menggunakan `host` berikut untuk mengakses search engine.

```
http://username:password@xxxx.aliyuncs.com:9200
```
