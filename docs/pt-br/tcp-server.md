# Serviço TCP/UDP

O framework fornece por padrão a capacidade de criar serviços `TCP/UDP`. Com uma configuração simples, você já consegue utilizá-los.

## Usando serviço TCP

### Criar a classe TcpServer

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

### Criar a configuração correspondente

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The following has removed other irrelevant configuration items
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
                // Configure on demand
            ],
        ],
    ],
];

```

### Implementar o client

```php
<?php

$client = new \Swoole\Client(SWOOLE_SOCK_TCP);
$client->connect('127.0.0.1', 9504);
$client->send('Hello World.');
$ret = $client->recv(); // recv:Hello World.
```

## Usando serviço UDP

### Criar a classe UdpServer

> Se não existir o arquivo de interface OnPacketInterface, você não pode implementar essa interface, e o resultado em execução será consistente com o caso em que ela é implementada, desde que a configuração esteja correta.

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

### Criar a configuração correspondente

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The following has removed other irrelevant configuration items
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
                // Configure on demand
            ],
        ],
    ],
];

```

## event

| Events | Notes |
| :---------------: | :---------------: |
| Event::ON_CONNECT | Listen for connection incoming events |
| Event::ON_RECEIVE | Monitor data reception event |
| Event::ON_CLOSE | Listen for connection close events |
| Event::ON_PACKET | UDP data receiving event |
