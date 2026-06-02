# RPC Component Berbasis Multiplexing

Komponen ini didasarkan pada protokol `TCP`, dan desain multiplexing terinspirasi oleh komponen `AMQP`.

## Instalasi

```
composer require hyperf/rpc-multiplex
```

## Konfigurasi Server

Modifikasi file konfigurasi `config/autoload/server.php`. Konfigurasi berikut telah menghapus pengaturan yang tidak relevan.

Dalam konfigurasi `settings`, aturan pemecahan paket tidak dapat dimodifikasi. Hanya `package_max_length` yang dapat dimodifikasi, dan konfigurasi ini harus konsisten antara `Server` dan `Client`.

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;
use Hyperf\Server\Server;

return [
    'servers' => [
        [
            'name' => 'rpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [Hyperf\RpcMultiplex\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
                'package_max_length' => 1024 * 1024 * 2,
            ],
            'options' => [
                // Dalam multiplexing, hindari error dari penulisan socket multiple yang melintasi coroutine
                'send_channel_capacity' => 65535,
            ],
        ],
    ],
];
```

Membuat `RpcService`

```php
<?php

namespace App\RPC;

use App\JsonRpc\CalculatorServiceInterface;
use Hyperf\RpcMultiplex\Constant;
use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "CalculatorService", server: "rpc", protocol: Constant::PROTOCOL_DEFAULT)]
class CalculatorService implements CalculatorServiceInterface
{
}
```

## Konfigurasi Client

Modifikasi file konfigurasi `config/autoload/services.php`

```php
<?php

declare(strict_types=1);

return [
    'consumers' => [
        [
            'name' => 'CalculatorService',
            'service' => App\JsonRpc\CalculatorServiceInterface::class,
            'id' => App\JsonRpc\CalculatorServiceInterface::class,
            'protocol' => Hyperf\RpcMultiplex\Constant::PROTOCOL_DEFAULT,
            'load_balancer' => 'random',
            // Dari service center mana consumer ini mendapatkan node information? Jika tidak dikonfigurasi, node information tidak akan diambil dari service center.
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9502],
            ],
            'options' => [
                'connect_timeout' => 5.0,
                'recv_timeout' => 5.0,
                'settings' => [
                    // Nilai maksimum paket body. Jika lebih kecil dari ukuran data yang dikembalikan oleh Server, exception akan dilempar, jadi usahakan untuk mengontrol ukuran paket body
                    'package_max_length' => 1024 * 1024 * 2,
                ],
                // Jumlah retry, nilai default adalah 2
                'retry_count' => 2,
                // Interval retry, milidetik
                'retry_interval' => 100,
                // Jumlah multiplexing clients
                'client_count' => 4,
                // Interval heartbeat, non-numerik berarti heartbeat tidak diaktifkan
                'heartbeat' => 30,
            ],
        ],
    ],
];
```

### Registry Center

Jika Anda perlu menggunakan registry center, Anda perlu menambahkan listener berikut secara manual

```php
<?php
return [
    Hyperf\RpcMultiplex\Listener\RegisterServiceListener::class,
];
```

## Penggunaan

- Mendefinisikan Interface

Sebagai contoh, kita perlu mendesain layanan RPC untuk mengirim SMS

```php
<?php

declare(strict_types=1);

namespace RPC\Push;

interface PushInterface
{
    public function sendSmsCode(string $mobile, string $code): bool;
}
```

- Server mengimplementasikan Interface

```php
<?php

declare(strict_types=1);

namespace App\RPC;

use RPC\Push\PushInterface;
use Hyperf\RpcMultiplex\Constant;
use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: PushInterface::class, server: 'rpc', protocol: Constant::PROTOCOL_DEFAULT)]
class PushService implements PushInterface
{
    public function sendSmsCode(string $mobile, string $code): bool
    {
        // Logika pemrosesan aktual
        return true;
    }
}
```

- Client melakukan pemanggilan

```php
<?php

use Hyperf\Context\ApplicationContext;
use RPC\Push\PushInterface;

ApplicationContext::getContainer()->get(PushInterface::class)->sendSmsCode('18600000001', '6666');
```
