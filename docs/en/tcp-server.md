# TCP/UDP Server

The framework provides the capability to create `TCP/UDP` services by default. You can use it with simple configuration.

## Using TCP Server

### Create TcpServer Class

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
    // Irrelevant configuration items removed
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
                // Configure as needed
            ],
        ],
    ],
];
```

### Implement Client

```php
<?php

$client = new \Swoole\Client(SWOOLE_SOCK_TCP);
$client->connect('127.0.0.1', 9504);
$client->send('Hello World.');
$ret = $client->recv(); // recv:Hello World.
```

## Using UDP Service

> Docker uses the TCP protocol for communication by default. If you need to use the UDP protocol, you need to configure the Docker network.
```shell
docker run -p 9502:9502/udp <image-name>
```

### Create UdpServer Class

> If the `OnPacketInterface` interface file is not available, you do not need to implement this interface. The execution result will be the same as implementing the interface, as long as the configuration is correct.

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

### Create corresponding configuration

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Irrelevant configuration items removed
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
                // Configure as needed
            ],
        ],
    ],
];
```

## Events

|       Event       |         Description         |
| :---------------: | :-------------------------: |
| Event::ON_CONNECT | Listen for connection establishment event |
| Event::ON_RECEIVE | Listen for data reception event |
|  Event::ON_CLOSE  | Listen for connection closure event |
| Event::ON_PACKET  | UDP data reception event |
