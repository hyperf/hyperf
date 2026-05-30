# Layanan TCP/UDP

Secara bawaan, framework menyediakan kemampuan untuk membuat layanan `TCP/UDP`.
Anda hanya perlu melakukan konfigurasi sederhana untuk dapat menggunakannya.

## Menggunakan layanan TCP

### Membuat kelas TcpServer

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
    // Konfigurasi berikut telah menghapus item konfigurasi lain yang tidak relevan
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

### Mengimplementasikan klien

```php
<?php

$client = new \Swoole\Client(SWOOLE_SOCK_TCP);
$client->connect('127.0.0.1', 9504);
$client->send('Hello World.');
$ret = $client->recv(); // recv:Hello World.
```

## Menggunakan layanan UDP

### Membuat kelas UdpServer

> Jika tidak ada file interface OnPacketInterface, Anda tidak harus
> mengimplementasikan interface ini, dan hasil jalannya akan konsisten dengan
> interface yang diimplementasikan, asalkan konfigurasinya benar.

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
        $server->sendto($clientInfo['address'], $clientInfo['port'], 'Server:' . $data);
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
    // Konfigurasi berikut telah menghapus item konfigurasi lain yang tidak relevan
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

## Event

| Event | Catatan |
| :---------------: | :---------------: |
| Event::ON_CONNECT | Memantau event koneksi masuk |
| Event::ON_RECEIVE | Memantau event penerimaan data |
| Event::ON_CLOSE | Memantau event koneksi ditutup |
| Event::ON_PACKET | Event penerimaan data UDP |
