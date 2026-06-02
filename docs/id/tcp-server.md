# TCP/UDP Server

Framework menyediakan kemampuan untuk membuat layanan `TCP/UDP` secara default. Anda dapat menggunakannya dengan konfigurasi sederhana.

## Menggunakan TCP Server

### Membuat Class TcpServer

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnReceiveInterface;

class TcpServer implements OnReceiveInterface
{
    public function onReceive($server, int $fd, int $reactorId, string $data): void
    {
        $server->send($fd, 'recv:' . $data);
    }
}
```

### Membuat konfigurasi yang sesuai

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Item konfigurasi yang tidak relevan dihapus
    'servers' => [
        [
            'name' => 'tcp',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9504,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [App\Controller\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                // Konfigurasi sesuai kebutuhan
            ],
        ],
    ],
];
```

### Implementasi Client

```php
<?php

$client = new \Swoole\Client(SWOOLE_SOCK_TCP);
$client->connect('127.0.0.1', 9504);
$client->send('Hello World.');
$ret = $client->recv(); // recv:Hello World.
```

## Menggunakan UDP Service

> Docker menggunakan protokol TCP untuk komunikasi secara default. Jika Anda perlu menggunakan protokol UDP, Anda perlu mengkonfigurasi jaringan Docker.
```shell
docker run -p 9502:9502/udp <image-name>
```

### Membuat Class UdpServer

> Jika file interface `OnPacketInterface` tidak tersedia, Anda tidak perlu mengimplementasikan interface ini. Hasil eksekusi akan sama seperti mengimplementasikan interface, selama konfigurasinya benar.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnPacketInterface;

class UdpServer implements OnPacketInterface
{
    public function onPacket($server, $data, $clientInfo): void
    {
        var_dump($clientInfo);
        $server->sendto($clientInfo['address'], $clientInfo['port'], 'Server：' . $data);
    }
}
```

### Membuat konfigurasi yang sesuai

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Item konfigurasi yang tidak relevan dihapus
    'servers' => [
        [
            'name' => 'udp',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9505,
            'sock_type' => SWOOLE_SOCK_UDP,
            'callbacks' => [
                Event::ON_PACKET => [App\Controller\UdpServer::class, 'onPacket'],
            ],
            'settings' => [
                // Konfigurasi sesuai kebutuhan
            ],
        ],
    ],
];
```

## Events

|       Event       |         Deskripsi         |
| :---------------: | :------------------------: |
| Event::ON_CONNECT | Memantau event pembentukan koneksi |
| Event::ON_RECEIVE | Memantau event penerimaan data |
|  Event::ON_CLOSE  | Memantau event penutupan koneksi |
| Event::ON_PACKET  | Event penerimaan data UDP |
