# Jet, by Hyperf

Jet is a unification model RPC Client, built-in JSON RPC protocol, available to running in ALL PHP environments, including PHP-FPM and Swoole/Hyperf environments. 

> Also will built-in gRPC and Tars protocols in future.

# Installation

```bash
composer require hyperf/jet
```

# Quickstart

## Register protocol

You cloud register any protocol by `Hyperf\Jet\ProtocolManager`, per protocol basically including Transporter, Packer, DataFormatter and PathGenerator, you could register a JSONRPC protocol like below: 

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

## Register service

After you registered a protocol to ProtocolManager, you could bind the protocol with any services by `Hyperf\Jet\ServiceManager`, like below:

```php
<?php
use Hyperf\Jet\ServiceManager;

// Bind CalculatorService with jsonrpc protocol, and set the static nodes info.
ServiceManager::register($service = 'CalculatorService', $protocol = 'jsonrpc', [
    ServiceManager::NODES => [
        [$host = '127.0.0.1', $port = 9503],
    ],
]);
```

## Create client

After you registered the protocol and service, you could get your service via `Hyperf/Jet/ClientFactory`, like below:

```php
<?php
use Hyperf\Jet\ClientFactory;

$clientFactory = new ClientFactory();
$client = $clientFactory->create($service = 'CalculatorService', $protocol = 'jsonrpc');
```

## Call RPC method

When you have a client object, you could call any remote methods via the object, like below: 

```php
// Call the remote method `add` with arguments `1` and `2`.
// The $result is the result of the remote method.
$result = $client->add(1, 2);
```

If you call a not exist remote method, the client will throw an `Hyperf\Jet\Exception\ServerException` exception.