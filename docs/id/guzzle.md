# Guzzle HTTP Client

Komponen [hyperf/guzzle](https://github.com/hyperf/guzzle) berbasis Guzzle
untuk pemrosesan coroutine, dan digantikan ke dalam Guzzle melalui Swoole HTTP
client sebagai coroutine driver untuk mencapai coroutineization dari HTTP client.

## Instalasi

```bash
composer require hyperf/guzzle
```

## Penggunaan

Cukup atur `Hyperf\Guzzle\CoroutineHandler` di dalam komponen ini ke dalam
Guzzle client sebagai handler untuk mengubahnya menjadi operasi coroutine.
Untuk memudahkan pembuatan objek Guzzle dari coroutine, kami menyediakan class
factory `Hyperf\Guzzle\ClientFactory` untuk membuat client dengan mudah.
Berikut adalah contohnya:

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
        // $options is equivalent to the $config parameter of the GuzzleHttp\Client constructor
        $options = [];
        // $client is a coroutineized GuzzleHttp\Client object
        $client = $this->clientFactory->create($options);
    }
}
```

### Menggunakan versi ^7.0

Dependensi komponen pada `Guzzle` telah diubah dari `^6.3` menjadi `^6.3 |
^7.0`. Versi `^7.0` dapat diinstal secara default, tetapi komponen berikut
akan berkonflik dengan `^7.0`:

- hyperf/metric

Anda dapat melakukan tindakan berikut secara aktif untuk menyelesaikan konflik:

```
composer require "promphp/prometheus_client_php:2.2.1"
```

- overtrue/flysystem-cos

Karena library dependen bergantung pada `guzzlehttp/guzzle-services`, dan tidak
mendukung `^7.0`, masalah ini tidak dapat diselesaikan untuk sementara waktu.

## Menggunakan Konfigurasi Swoole

Terkadang kita ingin memodifikasi konfigurasi `Swoole` secara langsung, sehingga
kami juga menyediakan item konfigurasi terkait. Namun, konfigurasi ini tidak
dapat berfungsi di dalam `Curl Guzzle client`, jadi gunakan dengan hati-hati.

> Konfigurasi ini akan menggantikan konfigurasi asli. Sebagai contoh, timeout
> di bawah ini akan digantikan menjadi 10.

```php
<?php
use GuzzleHttp\Client;
use Hyperf\Guzzle\CoroutineHandler;
use GuzzleHttp\HandlerStack;

$client = new Client([
    'base_uri' => 'http://127.0.0.1:8080',
    'handler' => HandlerStack::create(new CoroutineHandler()),
    'timeout' => 5,
    'swoole' => [
        'timeout' => 10,
        'socket_buffer_size' => 1024 * 1024 * 2,
    ],
]);

$response = $client->get('/');

```

## Connection Pool

Hyperf tidak hanya mengimplementasikan `Hyperf\Guzzle\CoroutineHandler`, tetapi
juga mengimplementasikan `Hyperf\Guzzle\PoolHandler` berbasis
`Hyperf\Pool\SimplePool`.

### Mengapa?

Terdapat batas atas pada jumlah koneksi TCP host. Ketika konkurensi kita
melebihi batas atas ini, request tidak dapat dibuat secara normal. Selain itu,
akan ada TIME-WAIT setelah koneksi TCP berakhir, sehingga koneksi tidak dapat
dilepaskan tepat waktu. Oleh karena itu, kita memerlukan connection pool untuk
mempertahankan tahap ini, meminimalkan dampak TIME-WAIT, dan memungkinkan
koneksi TCP digunakan kembali.

### Penggunaan

```php
<?php
use GuzzleHttp\Client;
use Hyperf\Coroutine\Coroutine;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;

$handler = null;
if (Coroutine::inCoroutine()) {
    $handler = make(PoolHandler::class, [
        'option' => [
            'max_connections' => 50,
        ],
    ]);
}

// Default retry middleware
$retry = make(RetryMiddleware::class, [
    'retries' => 1,
    'delay' => 10,
]);

$stack = HandlerStack::create($handler);
$stack->push($retry->getMiddleware(), 'retry');

$client = make(Client::class, [
    'config' => [
        'handler' => $stack,
    ],
]);
```

Selain itu, framework juga menyediakan `HandlerStackFactory` untuk memudahkan
pembuatan `$stack` di atas.

```php
<?php
use Hyperf\Guzzle\HandlerStackFactory;
use GuzzleHttp\Client;

$factory = new HandlerStackFactory();
$stack = $factory->create();

$client = make(Client::class, [
    'config' => [
        'handler' => $stack,
    ],
]);
```

## Menggunakan `ClassMap` untuk Menggantikan `GuzzleHttp\Client`

Jika komponen pihak ketiga tidak menyediakan interface yang dapat menggantikan
`Handler`, kita juga dapat menggunakan `ClassMap` untuk langsung menggantikan
`Client` guna mencapai tujuan coroutineization dari client.

> Tentu saja, Anda juga dapat menggunakan SWOOLE_HOOK untuk mencapai tujuan
> yang sama.

Berikut adalah contohnya:

class_map/GuzzleHttp/Client.php

```php
<?php
namespace GuzzleHttp;

use GuzzleHttp\Psr7;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Coroutine\Coroutine;

class Client implements ClientInterface
{
    // Omitted other unchanged codes

    public function __construct(array $config = [])
    {
        $inCoroutine = Coroutine::inCoroutine();
        if (!isset($config['handler'])) {
            // The corresponding Handler can choose CoroutineHandler or PoolHandler as needed
            $config['handler'] = HandlerStack::create($inCoroutine ? new CoroutineHandler() : null);
        } elseif ($inCoroutine && $config['handler'] instanceof HandlerStack) {
            $config['handler']->setHandler(new CoroutineHandler());
        } elseif (!is_callable($config['handler'])) {
            throw new \InvalidArgumentException('handler must be a callable');
        }

        // Convert the base_uri to a UriInterface
        if (isset($config['base_uri'])) {
            $config['base_uri'] = Psr7\uri_for($config['base_uri']);
        }

        $this->configureDefaults($config);
    }
}

```

config/autoload/annotations.php

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client;

return [
    'scan' => [
        // ...
        'class_map' => [
            Client::class => BASE_PATH . '/class_map/GuzzleHttp/Client.php',
        ],
    ],
];
```
