# JSON RPC Service

JSON RPC is a lightweight RPC protocol standard based on the JSON format, which is easy to use and read. In Hyperf, it is implemented by the [hyperf/json-rpc](https://github.com/hyperf/json-rpc) component, which can be customized for transmission based on the HTTP protocol, or directly based on the TCP protocol for transmission.

## Installation

```bash
composer require hyperf/json-rpc
```
  
This is just a protoco processing component for JSON RPC, generally, you still need [hyperf/rpc-server](https://github.com/hyperf/rpc-server) or [hyperf/rpc-client](https://github.com/hyperf/rpc-client) component to satisfy scenarios for client and server. Both need to be installed if used at the same time: 

For JSON RPC server:

```bash
composer require hyperf/rpc-server
```

For JSON RPC client:

```bash
composer require hyperf/rpc-client
```

## Instruction for use

Services have two roles, one is `ServiceProvider`, which is a service that provides services for other services, and the other is `ServiceConsumer`, which is a service that depends on other services. A service may plays `ServiceProvider` and `ServiceConsumer` role at the same time. And these two can directly define and restrict the call of the interface through the `Service Contract`. In Hyperf, it can be directly understood as an interface class `Interface`. Generally speaking, this interface class will appear under both the provider and the consumer.

### Define service provider

So far, only the form of annotations is supported to define `ServiceProvider`, and subsequent editions will add more form of configuration.
We can directly define a class through the `#[RpcService]` annotation and publish this service:

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * Note that if you want to manage the service through the service center, you need to add the publishTo attribute in the annotation
 */
#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implement an addition method, simply consider that the parameters are int type
    public function add(int $a, int $b): int
    {
        // The specific implementation of the service method
        return $a + $b;
    }
}
```
 
`#[RpcService]` has `4` parameters:  
The `name` attribute is the name that defines the service. Just define a globally unique name here. Hyperf will generate a corresponding ID based on this attribute and register it to the service center;
The `protocol` attribute defines the protocol exposed by the service. Currently, only `jsonrpc-http`, `jsonrpc`, and `jsonrpc-tcp-length-check` are supported, which correspond to the HTTP protocol and two protocols under the TCP protocol respectively. The default value is `jsonrpc-http`, the value here corresponds to the `key` of the protocol registered in `Hyperf\Rpc\ProtocolManager`. They are essentially JSON RPC protocol, the difference lies in data formatting, data packaging, data transmitter.
The `server` attribute is the `Server` carried by the binded publishing service class, the default value is `jsonrpc-http`. This attribute corresponds to the `name` under `servers` in the `config/autoload/server.php` file, which also means that we need to define a corresponding `Server`, we will elaborate on how to deal with this in the next chapter;
The `publishTo` attribute defines the service center to be published. Currently only supports `consul` or null. When it is null, it means that the service will not be published to the service center, which also means that you need to manually deal with the service discovery. When the value is `consul`, you need to configure the relevant configuration of the [hyperf/consul](zh-cn/consul.md) component. To use this function, you need to install [hyperf/service-governance](https://github. com/hyperf/service-governance) component, please refer to [Service Registration](zh-cn/service-register.md) section for details.

> To use the `#[RpcService]` annotation, the `use Hyperf\RpcServer\Annotation\RpcService;` namespace is required.

#### Define JSON RPC Server

HTTP Server (`jsonrpc-http` protocol is adapted)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The other configuration of the file is omitted here
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

TCP Server (`jsonrpc` protocol is adapted)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The other configuration of the file is omitted here
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

TCP Server (`jsonrpc-tcp-length-check` protocol is adapted)

The current protocol is an extended protocol of `jsonrpc`, and users can easily modify the corresponding `settings` to use this protocol. The example is as follows:

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The other configuration of the file is omitted here
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

### Publish to service center
   
Currently, only supports publishing services to `consul`, and other service centers will be added in the future.
Publishing services to `consul` is also very easy in Hyperf. Load the Consul component through `composer require hyperf/consul` (if it is already installed, you can ignore this step), and then configure your `Consul` configuration in the `config/autoload/consul.php` configuration file, an example is as follows:

```php
<?php

return [
    'uri' => 'http://127.0.0.1:8500',
];
```

After the configuration is completed, when the service is started, Hyperf will automatically register the service, which defined with `publishTo` attribute as `consul` by the `#[RpcService]`, to the service center.

> Currently, only the `jsonrpc` and `jsonrpc-http` protocols are supported to publish to the service center, other protocols have not yet implemented service registration

### Define service consumers

A `ServiceConsumer` can be considered as a client class. In Hyperf, you don't need to deal with connection and request-related things, you only need to perform some authentication configuration.

#### Automatically create proxy consumer class

You can automatically create consumer classes through dynamic proxy by doing some simple configuration in the `config/autoload/services.php` configuration file.

```php
<?php
return [
    'consumers' => [
        [
            // name must be the same as the name attribute of the service provider
            'name' => 'CalculatorService',
            // Service interface name. It's optional and the default value is equal to the value configured by name. If name is directly defined as an interface class, you can ignore this configuration. If name is a string, you need to configure service to correspond to the interface class
            'service' => \App\JsonRpc\CalculatorServiceInterface::class,
            // Corresponding container object. It's optional and the default value is equal to the value of the service configuration. To define the key of dependency injection.
            'id' => \App\JsonRpc\CalculatorServiceInterface::class,
            // The service agreement of the service provider. It's optional and the default value is jsonrpc-http
            // jsonrpc-http, jsonrpc, and jsonrpc-tcp-length-check are available
            'protocol' => 'jsonrpc-http',
            // Load balancing algorithm, optional, the default value is random
            'load_balancer' => 'random',
            // From which service center the consumer will obtain node information, if it is not configured, the node information will not be obtained from the service center
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // If the registry configuration above is not specified, it means to directly consume the specified node. Configure the node information of the service provider through the nodes parameter below
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
            // Configuration, this may affect Packer and Transporter
            'options' => [
                'connect_timeout' => 5.0,
                'recv_timeout' => 5.0,
                'settings' => [
                    // Different protocol, different configuration
                    'open_eof_split' => true,
                    'package_eof' => "\r\n",
                    // 'open_length_check' => true,
                    // 'package_length_type' => 'N',
                    // 'package_length_offset' => 0,
                    // 'package_body_offset' => 4,
                ],
                // Retrie count, the default value is 2, no retry will be performed when the packet is received over time. Only supports JsonRpcPoolTransporter, currently.
                'retry_count' => 2,
                // Retry interval, in milliseconds
                'retry_interval' => 100,
                // The following configuration will be used when using JsonRpcPoolTransporter
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

The proxy object of the client class is automatically created when the application starts, and the value of the configuration item `id` is used in the container (if not set, the value of the configuration item `service` will be used instead) to add the binding relationship. Like the hand-written client class, the client can be directly used by injecting the `CalculatorServiceInterface` interface.

> When the service provider uses the interface class name to publish the service name, only the configuration item `name` needs to be set as the interface class name on the service consumer, and there is no need to set the configuration items `id` and `service` repeatedly.

#### Manually create consumer classes

If you have more requirements for consumer classes, you can manually create a consumer class to achieve it. You only need to define a class and related attributes.

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcClient\AbstractServiceClient;

class CalculatorServiceConsumer extends AbstractServiceClient implements CalculatorServiceInterface
{
    /**
     * Define the service name of the corresponding service provider
     * @var string 
     */
    protected $serviceName = 'CalculatorService';
    
    /**
     * Define the protocol of the corresponding service provider
     * @var string 
     */
    protected $protocol = 'jsonrpc-http';

    public function add(int $a, int $b): int
    {
        return $this->__request(__FUNCTION__, compact('a', 'b'));
    }
}
```

Then you need to define a tag in the configuration file for obtaining node information from which service center. The file located in `config/autoload/services.php` (if it does not exist, you can create it yourself)

```php
<?php
return [
    'consumers' => [
        [
            // $serviceName corresponding to the consumer class
            'name' => 'CalculatorService',
            // From which service center the consumer will obtain node information. If it is not configured, the node information will not be obtained from the service center
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // If the registry configuration above is not specified, it means to directly consume the specified node. Configure the node information of the service provider through the nodes parameter below
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
        ]
    ],
];
```


In this way, we can use the `CalculatorService` class to achieve service consumption. In order to make the relationship logic here more reasonable, the relationship between `CalculatorServiceInterface` and `CalculatorServiceConsumer` should also be defined in `config/autoload/dependencies.php`. Examples are as follow:

```php
return [
    App\JsonRpc\CalculatorServiceInterface::class => App\JsonRpc\CalculatorServiceConsumer::class,
];
```

In this way, the client can be used by injecting the `CalculatorServiceInterface` interface.

#### Configuration reuse

Generally, a service consumer will consume multiple service providers at the same time. When we discover service providers through the service center, the `registry` configuration in `config/autoload/services.php` file may be repeatedly configured, however, our service center may be unified, which means that multiple service consumers are configured to pull node information from the same service center. At this time, we can implement it through PHP codes such as `PHP variables` or `loops` to generate configuration file.

##### Generate configuration by PHP variables

```php
<?php
$registry = [
   'protocol' => 'consul',
   'address' => 'http://127.0.0.1:8500',
];
return [
    // The following FooService and BarService are only examples of multi-services, and they do not actually exist in the document examples
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

##### Generate configuration by loop

```php
<?php
return [
    'consumers' => value(function () {
        $consumers = [];
        // This example automatically creates the configuration form of the proxy consumer class. There are two configuration items - name and service. This is not the only method. Just to explain that the configuration can be generated through PHP code
        // The following FooServiceInterface and BarServiceInterface are only examples of multi-services, and they do not actually exist in the document examples
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

### Return PHP object

When the framework imports `symfony/serializer (^5.0)` and `symfony/property-access (^5.0)`, configure the mapping relationship in `dependencies.php`

```php
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

`NormalizerInterface` will support serialization and deserialization of objects. This type of `MathValue[]` object array is not supported, currently.

Define the return object

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

Rewrite the interface file

```php
<?php

declare(strict_types=1);

namespace App\JsonRpc;

interface CalculatorServiceInterface
{
    public function sum(MathValue $v1, MathValue $v2): MathValue;
}
```

Call in the controller

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

### Use JsonRpcPoolTransporter

The framework provides a `Transporter` based on the connection pool, which can effectively avoid the problem of establishing too many connections during high concurrency. Here you can use `JsonRpcPoolTransporter` to replace `JsonRpcTransporter`.

Modify the `dependencies.php` file

```php
<?php

declare(strict_types=1);

use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\JsonRpcTransporter;

return [
    JsonRpcTransporter::class => JsonRpcPoolTransporter::class,
];

```


