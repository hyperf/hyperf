# TCP/UDP service

The framework provides the ability to create `TCP/UDP` services by default. You only need to perform a simple configuration, you can use it.

## Using TCP service

### Create TcpServer class

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

### Create corresponding configuration

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

### Implement the client

```php
<?php

$client = new \Swoole\Client(SWOOLE_SOCK_TCP);
$client->connect('127.0.0.1', 9504);
$client->send('Hello World.');
$ret = $client->recv(); // recv:Hello World.
```

## Using UDP service

### Create UdpServer class

> If there is no OnPacketInterface interface file, you can not implement this interface, and the running result is consistent with the implemented interface, as long as the configuration is correct.

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

### Create corresponding configuration

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

