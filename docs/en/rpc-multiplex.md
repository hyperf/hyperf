# RPC Component Based on Multiplexing

This component is based on the `TCP` protocol, and the design of multiplexing is inspired by the `AMQP` component.

## Installation

```
composer require hyperf/rpc-multiplex
```

## Server Configuration

Modify the `config/autoload/server.php` configuration file. The following configuration has deleted irrelevant settings.

In the `settings` configuration, the packet splitting rules cannot be modified. Only `package_max_length` can be modified, and this configuration needs to be consistent between `Server` and `Client`.

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
                // In multiplexing, avoid errors from cross-coroutine socket multiple writes
                'send_channel_capacity' => 65535,
            ],
        ],
    ],
];
```

Create `RpcService`

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

## Client Configuration

Modify the `config/autoload/services.php` configuration file

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
            // From which service center does this consumer get node information? If not configured, it will not get node information from the service center.
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
                    // Maximum packet body value. If it is smaller than the data size returned by Server, an exception will be thrown, so try to control the packet body size
                    'package_max_length' => 1024 * 1024 * 2,
                ],
                // Retry count, default value is 2
                'retry_count' => 2,
                // Retry interval, milliseconds
                'retry_interval' => 100,
                // Number of multiplexing clients
                'client_count' => 4,
                // Heartbeat interval, non-numeric means heartbeat is not enabled
                'heartbeat' => 30,
            ],
        ],
    ],
];
```

### Registry Center

If you need to use a registry center, you need to manually add the following listener

```php
<?php
return [
    Hyperf\RpcMultiplex\Listener\RegisterServiceListener::class,
];
```

## Usage

- Define Interface

For example, we need to design an RPC service for sending SMS

```php
<?php

declare(strict_types=1);

namespace RPC\Push;

interface PushInterface
{
    public function sendSmsCode(string $mobile, string $code): bool;
}
```

- Server implements Interface

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
        // Actual processing logic
        return true;
    }
}
```

- Client calls

```php
<?php

use Hyperf\Context\ApplicationContext;
use RPC\Push\PushInterface;

ApplicationContext::getContainer()->get(PushInterface::class)->sendSmsCode('18600000001', '6666');
```
