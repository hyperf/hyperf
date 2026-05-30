# Jet

Jet is a unified-model RPC client with built-in JSONRPC protocol adaptation. This component is applicable to all PHP environments, including PHP-FPM, Swoole, or Hyperf. (In the Hyperf environment, it is currently still recommended to directly use the `hyperf/json-rpc` component as the client).

> In the future, gRPC and Tars protocols will also be built-in.

# Installation

```bash
composer require hyperf/jet
```

# Quick Start

## Registering Protocols

> Registering a protocol is not a mandatory step, but you can manage all protocols through the ProtocolManager.

You can use the `Hyperf\Jet\ProtocolManager` class to register and manage any protocol. Each protocol will contain several basic components such as Transporter, Packer, DataFormatter, and PathGenerator. You can register a JSONRPC protocol as follows:

```php
<?php

use Hyperf\Jet\DataFormatter\DataFormatter;
use Hyperf\Jet\Packer\JsonEofPacker;
use Hyperf\Jet\PathGenerator\PathGenerator;
use Hyperf\Jet\ProtocolManager;
use Hyperf\Jet\Transporter\StreamSocketTransporter;

ProtocolManager::register($protocol = 'jsonrpc', [
    ProtocolManager::TRANSPORTER => new StreamSocketTransporter(),
    ProtocolManager::PACKER => new JsonEofPacker(),
    ProtocolManager::PATH_GENERATOR => new PathGenerator(),
    ProtocolManager::DATA_FORMATTER => new DataFormatter(),
]);
```

## Registering Services

> Registering a service is not a mandatory step, but you can manage all services through the ServiceManager.

After you have registered a protocol to `Hyperf\Jet\ProtocolManager`, you can bind the protocol to any service through `Hyperf\Jet\ServiceManager`, as follows:

```php
<?php
use Hyperf\Jet\ServiceManager;

// Bind CalculatorService with jsonrpc protocol and set static node information
ServiceManager::register($service = 'CalculatorService', $protocol = 'jsonrpc', [
    ServiceManager::NODES => [
        [$host = '127.0.0.1', $port = 9503],
    ],
]);
```

## Calling RPC Methods

### Calling via ClientFactory

After you have registered the protocols and services, you can obtain a client for your service through `Hyperf/Jet/ClientFactory`, as shown below:

```php
<?php
use Hyperf\Jet\ClientFactory;

$clientFactory = new ClientFactory();
$client = $clientFactory->create($service = 'CalculatorService', $protocol = 'jsonrpc');
```

Once you have a client object, you can use it to call any remote method, as follows:

```php
// Call the remote method `add` with arguments `1` and `2`
// $result will be the return value of the remote method
$result = $client->add(1, 2);
```

When you call a remote method that does not exist, the client will throw a `Hyperf\Jet\Exception\ServerException` exception.

### Calling via Custom Client

You can create a subclass of `Hyperf\Jet\AbstractClient` as a custom client class to complete remote method calls. For example, if you want to define a client class for the `jsonrpc` protocol of the `CalculatorService` service, you can first define a `CalculatorService` class, as shown below:

```php
<?php

use Hyperf\Jet\AbstractClient;
use Hyperf\Jet\Packer\JsonEofPacker;
use Hyperf\Jet\Transporter\StreamSocketTransporter;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\Contract\PackerInterface;
use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Rpc\Contract\TransporterInterface;

/**
 * @method int add(int $a, int $b);
 */
class CalculatorService extends AbstractClient
{
    // Define `CalculatorService` as the default value for the $service parameter
    public function __construct(
        string $service = 'CalculatorService',
        TransporterInterface $transporter = null,
        PackerInterface $packer = null,
        ?DataFormatterInterface $dataFormatter = null,
        ?PathGeneratorInterface $pathGenerator = null
    ) {
        // Specify transporter here; you can still get the transporter through ProtocolManager or pass it from the constructor
        $transporter = new StreamSocketTransporter('127.0.0.1', 9503);
        // Specify packer here; you can still get the packer through ProtocolManager or pass it from the constructor
        $packer = new JsonEofPacker();
        parent::__construct($service, $transporter, $packer, $dataFormatter, $pathGenerator);
    }
}
```

Now, you can use this class to directly call remote methods, as shown below:

```php
// Call the remote method `add` with arguments `1` and `2`
// $result will be the return value of the remote method
$client = new CalculatorService();
$result = $client->add(1, 2);
```
