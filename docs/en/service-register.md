# Service Registration

After service splitting, the number of services becomes very large, and each service may have a large number of cluster nodes to provide services. To ensure the normal operation of the system, it is inevitable that there must be a centralized component to complete the integration of various services, that is, to aggregate the services scattered in various places. The aggregated information can be the component name, address, quantity, etc., that provide services. Each component has a monitoring device that reports to the centralized component for status updates when the status of a service within this component changes. When requesting a service, the service caller first goes to the centralized component to obtain the component information (IP, port, etc.) that can provide the service, and accesses a provider of the service through a default or custom strategy to achieve service invocation. This centralized component is generally called a `Service Center`. In Hyperf, we have implemented support for components with `Consul` and `Nacos` as the service center, and will adapt to more service centers later.

# Installation

## Install Unified Access Layer

```bash
composer require hyperf/service-governance
```

## Choose to Install the Corresponding Adapter

Service registration supports `Consul` and `Nacos`. Introduce the corresponding adapter component as needed

- Consul

```shell
composer require hyperf/service-governance-consul
```

- Nacos

```shell
composer require hyperf/service-governance-nacos
```

# Configuration File

The component is driven by the `config/autoload/services.php` configuration file. The configuration file is as follows:

```php
return [
    'enable' => [
        // Enable service discovery
        'discovery' => true,
        // Enable service registration
        'register' => true,
    ],
    // Service consumer related configuration
    'consumers' => [],
    // Service provider related configuration
    'providers' => [],
    // Service driver related configuration
    'drivers' => [
        'consul' => [
            'uri' => 'http://127.0.0.1:8500',
            'token' => '',
            'check' => [
                'deregister_critical_service_after' => '90m',
                'interval' => '1s',
            ],
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
            'ephemeral' => false, // Whether to register ephemeral instances
        ],
    ],
];
```

# Register Service

Registering a service can be done by defining a class using the `#[RpcService]` annotation, which publishes the service. Currently, Hyperf only adapts to the JSON-RPC protocol. For more details, please refer to the [JSON-RPC Service](json-rpc.md) chapter.

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implement an addition method, simply assuming parameters are of type int
    public function calculate(int $a, int $b): int
    {
        // Implementation of the service method
        return $a + $b;
    }
}
```

`#[RpcService]` has `4` parameters:
`name`: Defines the name of the service. Simply define a globally unique name here, and Hyperf will generate the corresponding ID based on this attribute to register to the service center.
`protocol`: Defines the protocol exposed by the service. Currently, only `jsonrpc` and `jsonrpc-http` are supported, corresponding to the two protocols under the TCP protocol and HTTP protocol respectively. The default value is `jsonrpc-http`. The values here correspond to the `key` of the protocol registered in `Hyperf\Rpc\ProtocolManager`. Both are essentially JSON-RPC protocols, differing in data formatting, data packaging, data transporters, etc.
`server`: Binds the `Server` that will host the published service class. The default value is `jsonrpc-http`. This attribute corresponds to the `name` under `servers` in the `config/autoload/server.php` file, which means we need to define a corresponding `Server`.
`publishTo`: Defines the service center to which the service is published. Currently, only `consul`, `nacos` or empty is supported. Empty means the service is not published to a service center, which means you need to manually handle service discovery. To use this feature, you need to install the [hyperf/service-governance](https://github.com/hyperf/service-governance) component and corresponding driver dependencies.

> To use the `#[RpcService]` annotation, you need to `use Hyperf\RpcServer\Annotation\RpcService;`.

## Custom Service Governance Adapter

In addition to default support for `Consul` and `Nacos`, users can also register custom adapters according to their own needs.

We can create a FooService that implements `Hyperf\ServiceGovernance\DriverInterface`

```php
<?php

declare(strict_types=1);

namespace App\ServiceGovernance;

use Hyperf\ServiceGovernance\DriverInterface;

class FooDriver implements DriverInterface
{
    public function getNodes(string $uri, string $name, array $metadata): array
    {
        return [];
    }

    public function register(string $name, string $host, int $port, array $metadata): void
    {
    }

    public function isRegistered(string $name, string $address, int $port, array $metadata): bool
    {
        return true;
    }
}
```

Then create a listener and register it to `DriverManager`.

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\ServiceGovernance\Listener;

use App\ServiceGovernance\FooDriver;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ServiceGovernance\DriverManager;

#[Listener]
class RegisterDriverListener implements ListenerInterface
{
    protected DriverManager $driverManager;

    public function __construct(DriverManager $manager)
    {
        $this->driverManager = $manager;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        $this->driverManager->register('foo', make(FooDriver::class));
    }
}
```
