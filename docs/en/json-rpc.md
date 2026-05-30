# JSON-RPC Service

JSON-RPC is a lightweight RPC protocol standard based on JSON, easy to use and read. In Hyperf, it is implemented by the [hyperf/json-rpc](https://github.com/hyperf/json-rpc) component, which allows for custom transmission based on the HTTP protocol or directly based on the TCP protocol.

## Installation

```bash
composer require hyperf/json-rpc
```

This component is only for protocol handling in JSON-RPC. Generally, you still need to combine it with [hyperf/rpc-server](https://github.com/hyperf/rpc-server) or [hyperf/rpc-client](https://github.com/hyperf/rpc-client) to satisfy server-side and client-side scenarios. If using both, both need to be installed:

To use the JSON-RPC server:

```bash
composer require hyperf/rpc-server
```

To use the JSON-RPC client:

```bash
composer require hyperf/rpc-client
```

## Usage

There are two roles for services: `Service Provider`, which provides services to other services, and `Service Consumer`, which depends on other services. A service can be both a `Service Provider` and a `Service Consumer` simultaneously. The two can define and constrain interface calls through a `Service Contract`. In Hyperf, this can be directly understood as an `Interface`. Generally, this interface class will appear under both the provider and the consumer.

### Defining a Service Provider

Currently, defining a `Service Provider` is only supported via annotations; support for configuration-based definitions will be added in future iterations.
We can directly define a class using the `#[RpcService]` annotation to publish this service:

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * Note: If you wish to manage services through a service center, you need to add the publishTo attribute in the annotation.
 */
#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implement an addition method, simply assuming parameters are of type int
    public function add(int $a, int $b): int
    {
        // Implementation of the service method
        return $a + $b;
    }
}
```

`#[RpcService]` has `4` parameters:
`name`: Defines the name of the service. Simply define a globally unique name here, and Hyperf will generate the corresponding ID based on this attribute to register to the service center.
`protocol`: Defines the protocol exposed by the service. Currently, only `jsonrpc-http`, `jsonrpc`, and `jsonrpc-tcp-length-check` are supported, corresponding to the two protocols under the HTTP protocol and TCP protocol respectively. The default value is `jsonrpc-http`. The values here correspond to the `key` of the protocol registered in `Hyperf\Rpc\ProtocolManager`. They are essentially JSON-RPC protocols, differing in data formatting, data packaging, data transporters, etc.
`server`: Binds the `Server` that will host the published service class. The default value is `jsonrpc-http`. This attribute corresponds to the `name` under `servers` in the `config/autoload/server.php` file, which means we need to define a corresponding `Server`.
`publishTo`: Defines the service center to which the service is published. Currently, only `consul`, `nacos` or empty is supported. Empty means the service is not published to a service center, which means you need to manually handle service discovery. To use this feature, you need to install the [hyperf/service-governance](https://github.com/hyperf/service-governance) component and corresponding driver dependencies. For details, please refer to the [Service Registration](service-register.md) chapter.

> To use the `#[RpcService]` annotation, you need to `use Hyperf\RpcServer\Annotation\RpcService;`.

#### Defining JSON-RPC Server

HTTP Server (compatible with `jsonrpc-http` protocol)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Other configurations for this file are omitted
    'servers' => [
        [
            'name' => 'jsonrpc-http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9504,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [\Hyperf\JsonRpc\HttpServer::class, 'onRequest'],
            ],
        ],
    ],
];
```

TCP Server (compatible with `jsonrpc` protocol)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Other configurations for this file are omitted
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_eof_split' => true,
                'package_eof' => "\r\n",
                'package_max_length' => 1024 * 1024 * 2,
            ],
        ],
    ],
];
```

TCP Server (compatible with `jsonrpc-tcp-length-check` protocol)

The current protocol is an extension of `jsonrpc`. Users can easily modify the corresponding `settings` to use this protocol. An example is as follows.

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Other configurations for this file are omitted
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
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

### Publishing to Service Center

Currently, only publishing services to `consul` and `nacos` is supported. Others will be added later.
Publishing services to `consul` in Hyperf is also very easy. Reference the component via `composer require hyperf/service-governance-consul` (skip this step if already installed), then configure `drivers.consul` in the `config/autoload/services.php` configuration file.
Publishing services to `nacos` is similar. Reference the component via `composer require hyperf/service-governance-nacos` (skip this step if already installed), then configure `drivers.nacos` in the `config/autoload/services.php` configuration file. An example is as follows:

```php
<?php
return [
    'enable' => [
        'discovery' => true,
        'register' => true,
    ],
    'consumers' => [],
    'providers' => [],
    'drivers' => [
        'consul' => [
            'uri' => 'http://127.0.0.1:8500',
            'token' => '',
        ],
        'nacos' => [
            // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
            // 'url' => '',
            // The nacos host info
            'host' => '127.0.0.1',
            'port' => 8848,
            // The nacos account info
            'username' => null,
            'password' => null,
            'guzzle' => [
                'config' => null,
            ],
            'group_name' => 'api',
            'namespace_id' => 'namespace_id',
            'heartbeat' => 5,
        ],
    ],
];
```

After configuration, when the service starts, Hyperf will automatically register services with the `publishTo` attribute defined as `consul` or `nacos` in `#[RpcService]` to the corresponding service center.

> Currently, only `jsonrpc` and `jsonrpc-http` protocols are supported for publishing to service centers. Other protocols have not implemented service registration yet.

### Defining a Service Consumer

A `Service Consumer` can be understood as a client class, but in Hyperf, you do not need to handle connection and request-related matters. You only need to perform some identification configurations.

#### Automatically Creating Proxy Consumer Classes

You can automatically create consumer classes via dynamic proxy by making some simple configurations in the `config/autoload/services.php` configuration file.

```php
<?php
return [
    // Other configurations at the same level are omitted
    'consumers' => [
        [
            // name must be the same as the name attribute of the service provider
            'name' => 'CalculatorService',
            // Service interface name, optional. The default value is equal to the configured value of name. If name is directly defined as the interface class, this configuration line can be ignored. If name is a string, then service needs to be configured to correspond to the interface class.
            'service' => \App\JsonRpc\CalculatorServiceInterface::class,
            // Corresponding container object ID, optional. The default value is equal to the configured value of service, used to define the key for dependency injection.
            'id' => \App\JsonRpc\CalculatorServiceInterface::class,
            // Service protocol of the service provider, optional. The default value is jsonrpc-http
            // Optional jsonrpc-http jsonrpc jsonrpc-tcp-length-check
            'protocol' => 'jsonrpc-http',
            // Load balancing algorithm, optional. The default value is random
            'load_balancer' => 'random',
            // From which service center does this consumer get node information? If not configured, it will not get node information from the service center.
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // If the above registry configuration is not specified, it means direct consumption of the specified node. Configure the service provider's node information through the nodes parameter below.
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
            // Configuration items, will affect Packer and Transporter
            'options' => [
                'connect_timeout' => 5.0,
                'recv_timeout' => 5.0,
                'settings' => [
                    // Configure differently according to the protocol
                    'open_eof_split' => true,
                    'package_eof' => "\r\n",
                    // 'open_length_check' => true,
                    // 'package_length_type' => 'N',
                    // 'package_length_offset' => 0,
                    // 'package_body_offset' => 4,
                ],
                // Retry count, default value is 2. No retry for packet timeout. Temporarily only supports JsonRpcPoolTransporter
                'retry_count' => 2,
                // Retry interval, milliseconds
                'retry_interval' => 100,
                // Heartbeat interval when using multiplexed RPC, null means no heartbeat is triggered
                'heartbeat' => 30,
                // The following configuration is used when using JsonRpcPoolTransporter
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 32,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 3.0,
                    'heartbeat' => -1,
                    'max_idle_time' => 60.0,
                ],
            ],
        ]
    ],
];
```

When the application starts, it will automatically create a proxy object for the client class and add a binding relationship in the container using the value of the configuration item `id` (if not set, the value of the configuration item `service` will be used instead). This is just like a manually written client class: you can directly use the client by injecting the `CalculatorServiceInterface` interface.

> When the service provider uses the interface class name to publish the service name, on the service consumer side, you only need to set the configuration item `name` to the interface class name, without repeatedly setting the configuration items `id` and `service`.

#### Manually Creating Consumer Classes

If you have more requirements for the consumer class, you can implement it by manually creating a consumer class, simply by defining a class and its related attributes.

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcClient\AbstractServiceClient;

class CalculatorServiceConsumer extends AbstractServiceClient implements CalculatorServiceInterface
{
    /**
     * Define the service name corresponding to the service provider
     */
    protected string $serviceName = 'CalculatorService';
    
    /**
     * Define the service protocol corresponding to the service provider
     */
    protected string $protocol = 'jsonrpc-http';

    public function add(int $a, int $b): int
    {
        return $this->__request(__FUNCTION__, compact('a', 'b'));
    }
}
```

Then you also need to define a configuration in the configuration file to mark from which service center to obtain node information, located in `config/autoload/services.php` (create it yourself if it does not exist)

```php
<?php
return [
    // Other configurations at the same level are omitted
    'consumers' => [
        [
            // Corresponds to $serviceName of the consumer class
            'name' => 'CalculatorService',
            // From which service center does this consumer get node information? If not configured, it will not get node information from the service center.
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // If the above registry configuration is not specified, it means direct consumption of the specified node. Configure the service provider's node information through the nodes parameter below.
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
        ]
    ],
];
```

This way, we can realize the consumption of services through the `CalculatorService` class. To make the relationship logic here more reasonable, the relationship between `CalculatorServiceInterface` and `CalculatorServiceConsumer` should also be defined in `config/autoload/dependencies.php`. An example is as follows:

```php
return [
    App\JsonRpc\CalculatorServiceInterface::class => App\JsonRpc\CalculatorServiceConsumer::class,
];
```

This allows you to use the client by injecting the `CalculatorServiceInterface` interface.

#### Reusing Configuration

Usually, a service consumer will consume multiple service providers at the same time. When we discover service providers through the service center, the `registry` configuration might be repeatedly configured many times in the `config/autoload/services.php` configuration file. But usually, our service center might be unified, which means multiple service consumer configurations are fetching node information from the same service center. At this time, we can realize the generation of configuration files through `PHP variables` or `loops` and other PHP code.

##### Generating configuration via PHP variables

```php
<?php
$registry = [
   'protocol' => 'consul',
   'address' => 'http://127.0.0.1:8500',
];
return [
    // FooService and BarService below are only examples of multiple services, and they do not actually exist in the documentation examples.
    'consumers' => [
        [
            'name' => 'FooService',
            'registry' => $registry,
        ],
        [
            'name' => 'BarService',
            'registry' => $registry,
        ]
    ],
];
```

##### Generating configuration via loops

```php
<?php
return [
    // Other configurations at the same level are omitted
    'consumers' => value(function () {
        $consumers = [];
        // Here illustrates the configuration form of automatically creating proxy consumer classes. Therefore, there are two configuration items: name and service. The approach here is not unique, only illustrating that configuration can be generated through PHP code.
        // FooServiceInterface and BarServiceInterface below are only examples of multiple services, and they do not actually exist in the documentation examples.
        $services = [
            'FooService' => App\JsonRpc\FooServiceInterface::class,
            'BarService' => App\JsonRpc\BarServiceInterface::class,
        ];
        foreach ($services as $name => $interface) {
            $consumers[] = [
                'name' => $name,
                'service' => $interface,
                'registry' => [
                   'protocol' => 'consul',
                   'address' => 'http://127.0.0.1:8500',
                ]
            ];
        }
        return $consumers;
    }),
];
```

### Returning PHP Objects

When the framework imports `symfony/serializer (^5.0)` and `symfony/property-access (^5.0)`, and configures the mapping relationship in `dependencies.php`

```php
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

`NormalizerInterface` will support the serialization and deserialization of objects. Such `MathValue[]` object arrays are temporarily not supported.

Define return object

```php
<?php

declare(strict_types=1);

namespace App\JsonRpc;

class MathValue
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
```

Rewrite interface file

```php
<?php

declare(strict_types=1);

namespace App\JsonRpc;

interface CalculatorServiceInterface
{
    public function sum(MathValue $v1, MathValue $v2): MathValue;
}
```

Calling in the controller

```php
<?php

use Hyperf\Context\ApplicationContext;
use App\JsonRpc\CalculatorServiceInterface;
use App\JsonRpc\MathValue;

$client = ApplicationContext::getContainer()->get(CalculatorServiceInterface::class);

/** @var MathValue $result */
$result = $client->sum(new MathValue(1), new MathValue(2));

var_dump($result->value);
```

### Using JsonRpcPoolTransporter

The framework provides a `Transporter` based on a connection pool, which can effectively avoid the problem of establishing too many connections during high concurrency. Here, you can replace `JsonRpcTransporter` with `JsonRpcPoolTransporter`.

Modify `dependencies.php` file

```php
<?php

declare(strict_types=1);

use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\JsonRpcTransporter;

return [
    JsonRpcTransporter::class => JsonRpcPoolTransporter::class,
];
```
