# Komponen RPC berbasis Multiplexing

Komponen ini berbasis protokol `TCP`, dan desain multiplexing dipinjam dari
komponen `AMQP`.

## Instalasi

```
composer require hyperf/rpc-multiplex
```

## Konfigurasi Server

Ubah file konfigurasi `config/autoload/server.php`, konfigurasi berikut
menghapus konfigurasi yang tidak relevan.

Pada pengaturan `settings`, aturan pemaketan (subcontracting) tidak boleh
diubah, hanya `package_max_length` yang dapat diubah, konfigurasi ini harus
konsisten antara `Server` dan `Client`.

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
        ],
    ],
];
```

Buat `RpcService`

```php
<?php

namespace App\RPC;

use App\JsonRpc\CalculatorServiceInterface;
use Hyperf\RpcMultiplex\Constant;
use Hyperf\RpcServer\Annotation\RpcService;

/**
 * @RpcService(name="CalculatorService", server="rpc", protocol=Constant::PROTOCOL_DEFAULT)
 */
class CalculatorService implements CalculatorServiceInterface
{
}
```

## Konfigurasi Client

Ubah file konfigurasi `config/autoload/services.php`

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
            // Which service center does the consumer want to obtain node information from, if not configured, the node information will not be obtained from the service center
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
                    // The maximum value of the package body. If it is less than the data size returned by the Server, an exception will be thrown, so try to control the package body size as much as possible.
                    'package_max_length' => 1024 * 1024 * 2,
                ],
                // number of retries, default is 2
                'retry_count' => 2,
                // retry interval, milliseconds
                'retry_interval' => 100,
                // Number of multiplexed clients
                'client_count' => 4,
                // Heartbeat interval non-numeric means no heartbeat
                'heartbeat' => 30,
            ],
        ],
    ],
];
```

### Pusat Registrasi

Jika Anda perlu menggunakan registry, Anda perlu menambahkan listener berikut
secara manual

```php
<?php
return [
    Hyperf\RpcMultiplex\Listener\RegisterServiceListener::class,
];
```
