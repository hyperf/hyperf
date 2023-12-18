# Server Register

The service number increasing with the splitting. A large number of services with a great number of cluster nodes should be managed to ensure the normal execution of the entire system. There must be a centralized component to integrate information of various services, that is, the service information that are scattered everywhere will be aggregated. The aggregated information can be the name, address, quantity, etc. of the component that provides the service. Each component has a monitoring device, and when the status of a certain service in this component changes, it reports to the centralized component to update the status. When the caller of the service requests a certain service, first go to the centralized component to obtain the component information such as IP, port, etc., and then a certain provider of the service is selected for access through default or customized strategy. And this centralized component is called, generally, the `Service Center`. In Hyperf, we implemented the service center based on the `Consul`. More service centers will be adapted in the future.

# Installation

```bash
composer require hyperf/service-governance
```

# Register Service

Service registration can be done by defining a class through the `#[RpcService]` annotation, which can be regarded as service publishing. So far, only the JSON RPC protocol has been adapted. Referring the [JSON RPC Service](en/json-rpc.md) for more details.

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implement an add method with only int type in this example.
    public function calculate(int $a, int $b): int
    {
        // Specific implementation of the service method
        return $a + $b;
    }
}
```

There are `4` params of `#[RpcService]`: 
`name` attribute is the name of this service. Just take a globally unique name here, and Hyperf will generate a corresponding ID based on this attribute and register it in the service center;
`protocol` attribute is the protocol the service exposed out. So far, only `jsonrpc` and `jsonrpc-http` are supported corresponding to the two protocols under the TCP and HTTP respectively. The default value is `jsonrpc-http`. The value here corresponds to the `key` of the protocol registered in `Hyperf\Rpc\ProtocolManager`. Both of these two are essentially JSON RPC protocols. The difference lies in data formatting, data packaging, and data transmitters. 
`server` attribute is the `Server` to be carried by the service class which should be published. The default value is `jsonrpc-http`. This attribute corresponds to the `name` under `servers` in the `config/autoload/server.php` file. This also means that we need to define a corresponding `Server`, we will elaborate on how to deal with this in the next chapter;
`publishTo` attribute defines the service center where the service is to be published. Currently, only `consul` is supported, or you can leave it as null. When it is null, it means that the service will not be published to the service center, which means that you need to manually deal with the problem of service discovery. When the value is `consul`, you need to configure the relevant configuration of the [hyperf/consul](en/consul.md) component. To use this function, you need to install [hyperf/service-governance](https://github.com/hyperf/service-governance) component;

> The `use Hyperf\RpcServer\Annotation\RpcService;` is required when the `#[RpcService]` annotation is used.
