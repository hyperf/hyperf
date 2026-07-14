# Jet, by Hyperf

Jet é um modelo unificado de RPC Client, com protocolo JSONRPC embutido, disponível para execução em TODOS os ambientes PHP, incluindo ambientes PHP-FPM e Swoole/Hyperf.

> Também terá os protocolos gRPC e Tars embutidos no futuro.

# Instalação

```bash
composer require hyperf/jet
```

# Início rápido

## Registrar protocolo

> Registrar o protocolo não é obrigatório, mas você pode gerenciar os protocolos mais facilmente usando o ProtocolManager.

Você pode registrar qualquer protocolo através do `Hyperf\Jet\ProtocolManager`; cada protocolo basicamente inclui Transporter, Packer, DataFormatter e PathGenerator. Você pode registrar um protocolo JSONRPC como abaixo:

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

> Registrar o serviço não é obrigatório, mas você pode gerenciar os serviços mais facilmente usando o ServiceManager.

Depois de registrar um protocolo no `Hyperf\Jet\ProtocolManager`, você pode vincular o protocolo a qualquer serviço através do `Hyperf\Jet\ServiceManager`, como abaixo:

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

### Chamada através do ClientFactory

Depois de registrar o protocolo e o serviço, você pode obter o client do seu serviço através do `Hyperf/Jet/ClientFactory`, como abaixo:

```php
<?php
use Hyperf\Jet\ClientFactory;

$clientFactory = new ClientFactory();
$client = $clientFactory->create($service = 'CalculatorService', $protocol = 'jsonrpc');
```

Quando você tiver o objeto client, você pode chamar qualquer método remoto através do objeto, como abaixo:

```php
// Call the remote method `add` with arguments `1` and `2`.
// The $result is the result of the remote method.
$result = $client->add(1, 2);
```

Se você chamar um método remoto que não existe, o client lançará uma exceção `Hyperf\Jet\Exception\ServerException`.

### Chamada através de um client customizado

Você também pode criar uma classe de client customizada que estenda `Hyperf\Jet\AbstractClient`, para chamar os métodos remotos através do objeto client.
Por exemplo, você quer definir um client RPC para `CalculatorService` com o protocolo `jsonrpc`; você pode primeiro criar uma classe `CalculatorService`, como abaixo:

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
    // Define `CalculatorService` as the default value of $service.
    public function __construct(
        string $service = 'CalculatorService',
        TransporterInterface $transporter = null,
        PackerInterface $packer = null,
        ?DataFormatterInterface $dataFormatter = null,
        ?PathGeneratorInterface $pathGenerator = null
    ) {
        // Specific the transporter here, you could also retrieve the transporter from ProtocolManager or passing by constructor.
        $transporter = new StreamSocketTransporter('127.0.0.1', 9503);
        // Specific the packer here, you could also retrieve the packer from ProtocolManager or passing by constructor.
        $packer = new JsonEofPacker();
        parent::__construct($service, $transporter, $packer, $dataFormatter, $pathGenerator);
    }
}
```

Agora, você pode usar essa classe para chamar o método remoto diretamente, como abaixo:

```php
// Call the remote method `add` with arguments `1` and `2`.
// The $result is the result of the remote method.
$client = new CalculatorService();
$result = $client->add(1, 2);
```
