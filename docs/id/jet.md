# Jet

Jet adalah RPC client model terpadu dengan adaptasi protokol JSONRPC bawaan. Komponen ini berlaku untuk semua lingkungan PHP, termasuk PHP-FPM, Swoole, atau Hyperf. (Di lingkungan Hyperf, saat ini masih direkomendasikan untuk langsung menggunakan komponen `hyperf/json-rpc` sebagai client).

> Ke depannya, protokol gRPC dan Tars juga akan tersedia secara bawaan.

# Instalasi

```bash
composer require hyperf/jet
```

# Memulai Cepat

## Mendaftarkan Protokol

> Mendaftarkan protokol bukanlah langkah wajib, tetapi Anda dapat mengelola semua protokol melalui ProtocolManager.

Anda dapat menggunakan kelas `Hyperf\Jet\ProtocolManager` untuk mendaftarkan dan mengelola protokol apa pun. Setiap protokol akan berisi beberapa komponen dasar seperti Transporter, Packer, DataFormatter, dan PathGenerator. Anda dapat mendaftarkan protokol JSONRPC sebagai berikut:

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

## Mendaftarkan Service

> Mendaftarkan service bukanlah langkah wajib, tetapi Anda dapat mengelola semua service melalui ServiceManager.

Setelah Anda mendaftarkan protokol ke `Hyperf\Jet\ProtocolManager`, Anda dapat mengikat protokol tersebut ke service mana pun melalui `Hyperf\Jet\ServiceManager`, sebagai berikut:

```php
<?php
use Hyperf\Jet\ServiceManager;

// Bind CalculatorService dengan protokol jsonrpc dan set informasi node statis
ServiceManager::register($service = 'CalculatorService', $protocol = 'jsonrpc', [
    ServiceManager::NODES => [
        [$host = '127.0.0.1', $port = 9503],
    ],
]);
```

## Memanggil Method RPC

### Memanggil melalui ClientFactory

Setelah Anda mendaftarkan protokol dan service, Anda dapat memperoleh client untuk service Anda melalui `Hyperf/Jet/ClientFactory`, seperti yang ditunjukkan di bawah ini:

```php
<?php
use Hyperf\Jet\ClientFactory;

$clientFactory = new ClientFactory();
$client = $clientFactory->create($service = 'CalculatorService', $protocol = 'jsonrpc');
```

Setelah Anda memiliki objek client, Anda dapat menggunakannya untuk memanggil method remote apa pun, sebagai berikut:

```php
// Panggil method remote `add` dengan argumen `1` dan `2`
// $result akan menjadi nilai kembalian dari method remote
$result = $client->add(1, 2);
```

Saat Anda memanggil method remote yang tidak ada, client akan melemparkan exception `Hyperf\Jet\Exception\ServerException`.

### Memanggil melalui Custom Client

Anda dapat membuat subclass dari `Hyperf\Jet\AbstractClient` sebagai kelas client kustom untuk menyelesaikan pemanggilan method remote. Sebagai contoh, jika Anda ingin mendefinisikan kelas client untuk protokol `jsonrpc` dari service `CalculatorService`, Anda dapat terlebih dahulu mendefinisikan kelas `CalculatorService`, seperti yang ditunjukkan di bawah ini:

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
    // Definisikan `CalculatorService` sebagai nilai default untuk parameter $service
    public function __construct(
        string $service = 'CalculatorService',
        TransporterInterface $transporter = null,
        PackerInterface $packer = null,
        ?DataFormatterInterface $dataFormatter = null,
        ?PathGeneratorInterface $pathGenerator = null
    ) {
        // Tentukan transporter di sini; Anda masih bisa mendapatkan transporter melalui ProtocolManager atau melewatinya dari konstruktor
        $transporter = new StreamSocketTransporter('127.0.0.1', 9503);
        // Tentukan packer di sini; Anda masih bisa mendapatkan packer melalui ProtocolManager atau melewatinya dari konstruktor
        $packer = new JsonEofPacker();
        parent::__construct($service, $transporter, $packer, $dataFormatter, $pathGenerator);
    }
}
```

Sekarang, Anda dapat menggunakan kelas ini untuk langsung memanggil method remote, seperti yang ditunjukkan di bawah ini:

```php
// Panggil method remote `add` dengan argumen `1` dan `2`
// $result akan menjadi nilai kembalian dari method remote
$client = new CalculatorService();
$result = $client->add(1, 2);
```
