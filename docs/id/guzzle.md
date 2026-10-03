# Guzzle HTTP Client

Komponen [hyperf/guzzle](https://github.com/hyperf/guzzle) melakukan pemrosesan coroutine di atas Guzzle. Komponen ini mengganti handler Guzzle dengan Swoole HTTP client sebagai driver coroutine, sehingga operasi HTTP client menjadi coroutine-friendly.

## Instalasi

```bash
composer require hyperf/guzzle
```

## Penggunaan

Cukup set `Hyperf\Guzzle\CoroutineHandler` sebagai handler di Guzzle client untuk mengaktifkan operasi coroutine-friendly. Untuk memudahkan pembuatan objek Guzzle yang coroutine-friendly, kami menyediakan factory class `Hyperf\Guzzle\ClientFactory` untuk pembuatan client yang mudah. Contoh kode:

```php
<?php 
use Hyperf\Guzzle\ClientFactory;

class Foo
{
    private ClientFactory $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    
    public function bar()
    {
        // $options sama dengan parameter $config dari konstruktor GuzzleHttp\Client
        $options = [];
        // $client adalah objek GuzzleHttp\Client yang coroutine-friendly
        $client = $this->clientFactory->create($options);
    }
}
```

### Menggunakan versi ^7.0

Ketergantungan komponen pada `Guzzle` telah diubah dari `^6.3` menjadi `^6.3 | ^7.0`. Secara default, dimungkinkan untuk menginstal versi `^7.0`, namun komponen berikut bertentangan dengan `^7.0`.

- hyperf/metric

Anda dapat secara aktif menjalankan operasi berikut untuk menyelesaikan konflik:

```
composer require "promphp/prometheus_client_php:2.2.1"
```

- overtrue/flysystem-cos

Karena library dependensi bergantung pada `guzzlehttp/guzzle-services`, yang tidak mendukung `^7.0`, untuk saat ini tidak dapat diselesaikan.

## Menggunakan Konfigurasi Swoole

Terkadang kita ingin langsung memodifikasi konfigurasi `Swoole`, jadi kami juga menyediakan item konfigurasi yang relevan. Namun, konfigurasi ini tidak akan berlaku di `Curl Guzzle client`, jadi gunakan dengan hati-hati.

> Konfigurasi ini akan menggantikan konfigurasi asli. Sebagai contoh, timeout berikut akan digantikan oleh 10.

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

Selain mengimplementasikan `Hyperf\Guzzle\CoroutineHandler`, Hyperf juga mengimplementasikan `Hyperf\Guzzle\PoolHandler` berdasarkan `Hyperf\Pool\SimplePool`.

### Alasan

Sederhananya, jumlah koneksi TCP pada sebuah host terbatas. Jika konkurensi melebihi batas ini, permintaan tidak bisa dibuat secara normal. Selain itu, ada fase TIME-WAIT setelah koneksi TCP berakhir, jadi koneksi tidak bisa dilepaskan secara real-time. Akibatnya, konkurensi aktual jauh lebih rendah dari batas TCP. Karena itu, kita perlu connection pool untuk mengelola fase ini, meminimalkan dampak TIME-WAIT, dan memungkinkan koneksi TCP dipakai ulang.

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

// Middleware retry default
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

Selain itu, framework juga menyediakan `HandlerStackFactory` untuk memudahkan pembuatan `$stack` di atas.

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

## Menggunakan `ClassMap` untuk mengganti `GuzzleHttp\Client`

Jika komponen pihak ketiga tidak menyediakan antarmuka untuk mengganti `Handler`, kita juga dapat menggunakan fungsi `ClassMap` untuk langsung mengganti `Client` agar client menjadi coroutine-friendly.

> Tentu saja, Anda juga dapat menggunakan `SWOOLE_HOOK` untuk mencapai tujuan yang sama.

Contoh kode:

class_map/GuzzleHttp/Client.php

```php
<?php
namespace GuzzleHttp;

use GuzzleHttp\Psr7;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Coroutine\Coroutine;

class Client implements ClientInterface
{
    // Kode untuk bagian lain yang tidak berubah diabaikan

    public function __construct(array $config = [])
    {
        $inCoroutine = Coroutine::inCoroutine();
        if (!isset($config['handler'])) {
            // Handler yang sesuai dapat dipilih sebagai CoroutineHandler atau PoolHandler sesuai kebutuhan
            $config['handler'] = HandlerStack::create($inCoroutine ? new CoroutineHandler() : null);
        } elseif ($inCoroutine && $config['handler'] instanceof HandlerStack) {
            $config['handler']->setHandler(new CoroutineHandler());
        } elseif (!is_callable($config['handler'])) {
            throw new \InvalidArgumentException('handler harus berupa callable');
        }

        // Konversi base_uri menjadi UriInterface
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
