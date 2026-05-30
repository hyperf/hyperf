# Jet, oleh Hyperf

Jet adalah RPC Client dengan model penyatuan (unification model), dilengkapi
dengan protokol JSONRPC bawaan, dan dapat dijalankan di SEMUA lingkungan PHP,
termasuk lingkungan PHP-FPM dan Swoole/Hyperf.

> Protokol gRPC dan Tars bawaan juga akan didukung di masa mendatang.

# Instalasi

```bash
composer require hyperf/jet
```

# Mulai Cepat

## Registrasi protokol

> Mendaftarkan protokol tidak wajib dilakukan, tetapi Anda dapat mengelola
> protokol dengan lebih mudah menggunakan ProtocolManager.

Anda dapat mendaftarkan protokol apa pun melalui `Hyperf\Jet\ProtocolManager`.
Setiap protokol pada dasarnya mencakup Transporter, Packer, DataFormatter, dan
PathGenerator. Anda dapat mendaftarkan protokol JSONRPC seperti di bawah ini:

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

## Registrasi layanan

> Mendaftarkan layanan tidak wajib dilakukan, tetapi Anda dapat mengelola
> layanan dengan lebih mudah menggunakan ServiceManager.

Setelah mendaftarkan protokol ke `Hyperf\Jet\ProtocolManager`, Anda dapat
menghubungkan protokol tersebut dengan layanan apa pun menggunakan
`Hyperf\Jet\ServiceManager` seperti di bawah ini:

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

## Memanggil metode RPC

### Memanggil via ClientFactory

Setelah mendaftarkan protokol dan layanan, Anda dapat mendapatkan client
layanan Anda melalui `Hyperf\Jet\ClientFactory` seperti di bawah ini:

```php
<?php
use Hyperf\Jet\ClientFactory;

$clientFactory = new ClientFactory();
$client = $clientFactory->create($service = 'CalculatorService', $protocol = 'jsonrpc');
```

Setelah memiliki objek client, Anda dapat memanggil metode remote apa pun
melalui objek tersebut seperti di bawah ini:

```php
// Call the remote method `add` with arguments `1` and `2`.
// The $result is the result of the remote method.
$result = $client->add(1, 2);
```

Jika Anda memanggil metode remote yang tidak ada, client akan melemparkan
exception `Hyperf\Jet\Exception\ServerException`.

### Memanggil via custom client

Anda juga dapat membuat class client sendiri (custom client) yang mewarisi
(extends) `Hyperf\Jet\AbstractClient` untuk memanggil metode remote
melalui objek client tersebut.
Sebagai contoh, jika Anda ingin mendefinisikan RPC client untuk
`CalculatorService` dengan protokol `jsonrpc`, Anda dapat membuat class
`CalculatorService` terlebih dahulu seperti di bawah ini:

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

Sekarang, Anda dapat menggunakan class ini untuk memanggil metode remote
secara langsung seperti di bawah ini:

```php
// Call the remote method `add` with arguments `1` and `2`.
// The $result is the result of the remote method.
$client = new CalculatorService();
$result = $client->add(1, 2);
```
