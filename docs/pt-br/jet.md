# Jet, por Hyperf

O Jet é um client RPC de modelo unificado, com protocolo JSONRPC embutido, disponível para rodar em TODOS os ambientes PHP, incluindo PHP-FPM e ambientes Swoole/Hyperf.

> No futuro, também serão incorporados os protocolos gRPC e Tars.

# Instalação

```bash
composer require hyperf/jet
```

# Início rápido

## Registrar protocolo

> Registrar o protocolo não é obrigatório, mas você pode gerenciar protocolos mais facilmente usando o ProtocolManager.

Você pode registrar qualquer protocolo via `Hyperf\Jet\ProtocolManager`. Cada protocolo basicamente inclui Transporter, Packer, DataFormatter e PathGenerator. Você pode registrar um protocolo JSONRPC como abaixo:

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

## Registrar serviço

> Registrar o serviço não é obrigatório, mas você pode gerenciar serviços mais facilmente usando o ServiceManager.

Depois de registrar um protocolo no `Hyperf\Jet\ProtocolManager`, você pode associá-lo a qualquer serviço via `Hyperf\Jet\ServiceManager`, como abaixo:

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

## Chamar método RPC

### Chamar via ClientFactory

Depois de registrar protocolo e serviço, você pode obter o client do serviço via `Hyperf/Jet/ClientFactory`, como abaixo:

```php
<?php
use Hyperf\Jet\ClientFactory;

$clientFactory = new ClientFactory();
$client = $clientFactory->create($service = 'CalculatorService', $protocol = 'jsonrpc');
```

Quando você tiver o objeto client, pode chamar qualquer método remoto por meio dele, como abaixo:

```php
// Call the remote method `add` with arguments `1` and `2`.
// The $result is the result of the remote method.
$result = $client->add(1, 2);
```

Se você chamar um método remoto que não existe, o client lançará uma exceção `Hyperf\Jet\Exception\ServerException`.

### Chamar via client customizado

Você também pode criar uma classe de client customizada que estenda `Hyperf\Jet\AbstractClient` para chamar métodos remotos via o objeto client.

Por exemplo, se você quiser definir um client RPC para `CalculatorService` com o protocolo `jsonrpc`, você pode criar primeiro uma classe `CalculatorService`, como abaixo:

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
     // Define `CalculatorService` como o valor padrão de $service.
     public function __construct(
         string $service = 'CalculatorService',
         TransporterInterface $transporter = null,
         PackerInterface $packer = null,
         ?DataFormatterInterface $dataFormatter = null,
         ?PathGeneratorInterface $pathGenerator = null
     ) {
         // Especifica o transporter aqui; você também pode obter o transporter do ProtocolManager ou passar pelo construtor.
         $transporter = new StreamSocketTransporter('127.0.0.1', 9503);
         // Especifica o packer aqui; você também pode obter o packer do ProtocolManager ou passar pelo construtor.
         $packer = new JsonEofPacker();
         parent::__construct($service, $transporter, $packer, $dataFormatter, $pathGenerator);
     }
 }
 ```

Agora você pode usar essa classe para chamar o método remoto diretamente, como abaixo:

```php
// Call the remote method `add` with arguments `1` and `2`.
// The $result is the result of the remote method.
$client = new CalculatorService();
$result = $client->add(1, 2);
```
