# Service Registration

Setelah pemecahan layanan, jumlah layanan menjadi sangat banyak, dan setiap layanan mungkin memiliki sejumlah besar cluster nodes untuk menyediakan layanan. Untuk memastikan operasi normal sistem, pasti harus ada komponen terpusat untuk menyelesaikan integrasi berbagai layanan, yaitu mengagregasi layanan-layanan yang tersebar di berbagai tempat. Informasi yang diagregasi dapat berupa nama komponen, alamat, jumlah, dll., yang menyediakan layanan. Setiap komponen memiliki perangkat monitoring yang melapor ke komponen terpusat untuk pembaruan status ketika status sebuah layanan dalam komponen ini berubah. Ketika meminta sebuah layanan, pemanggil layanan pertama-tama pergi ke komponen terpusat untuk mendapatkan informasi komponen (IP, port, dll.) yang dapat menyediakan layanan tersebut, dan mengakses salah satu provider dari layanan tersebut melalui strategi default atau kustom untuk mencapai service invocation. Komponen terpusat ini umumnya disebut `Service Center`. Di Hyperf, kami telah mengimplementasikan dukungan untuk komponen dengan `Consul` dan `Nacos` sebagai service center, dan akan mengadaptasi lebih banyak service center di kemudian hari.

# Instalasi

## Menginstal Unified Access Layer

```bash
composer require hyperf/service-governance
```

## Memilih untuk Menginstal Adapter yang Sesuai

Service registration mendukung `Consul` dan `Nacos`. Perkenalkan komponen adapter yang sesuai sesuai kebutuhan

- Consul

```shell
composer require hyperf/service-governance-consul
```

- Nacos

```shell
composer require hyperf/service-governance-nacos
```

# File Konfigurasi

Komponen ini digerakkan oleh file konfigurasi `config/autoload/services.php`. File konfigurasinya adalah sebagai berikut:

```php
return [
    'enable' => [
        // Mengaktifkan service discovery
        'discovery' => true,
        // Mengaktifkan service registration
        'register' => true,
    ],
    // Konfigurasi terkait service consumer
    'consumers' => [],
    // Konfigurasi terkait service provider
    'providers' => [],
    // Konfigurasi terkait service driver
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
            // url server nacos seperti https://nacos.hyperf.io, Prioritas lebih tinggi dari host:port
            // 'url' => '',
            // Informasi host nacos
            'host' => '127.0.0.1',
            'port' => 8848,
            // Informasi akun nacos
            'username' => null,
            'password' => null,
            'guzzle' => [
                'config' => null,
            ],
            'group_name' => 'api',
            'namespace_id' => 'namespace_id',
            'heartbeat' => 5,
            'ephemeral' => false, // Apakah akan mendaftarkan instance ephemeral
        ],
    ],
];
```

# Mendaftarkan Layanan

Mendaftarkan layanan dapat dilakukan dengan mendefinisikan sebuah kelas menggunakan annotation `#[RpcService]`, yang mempublikasikan layanan tersebut. Saat ini, Hyperf hanya mengadaptasi protokol JSON-RPC. Untuk detail lebih lanjut, silakan merujuk ke bab [JSON-RPC Service](id/json-rpc.md).

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implementasi method penjumlahan, sederhananya parameter diasumsikan bertipe int
    public function calculate(int $a, int $b): int
    {
        // Implementasi method layanan
        return $a + $b;
    }
}
```

`#[RpcService]` memiliki `4` parameter:
`name`: Mendefinisikan nama layanan. Cukup definisikan nama yang unik secara global di sini, dan Hyperf akan menghasilkan ID yang sesuai berdasarkan atribut ini untuk didaftarkan ke service center.
`protocol`: Mendefinisikan protokol yang diekspos oleh layanan. Saat ini hanya mendukung `jsonrpc` dan `jsonrpc-http`, yang masing-masing sesuai dengan dua protokol di bawah protokol TCP dan protokol HTTP. Nilai default adalah `jsonrpc-http`. Nilai-nilai di sini sesuai dengan `key` dari protokol yang terdaftar di `Hyperf\Rpc\ProtocolManager`. Keduanya pada dasarnya adalah protokol JSON-RPC, perbedaannya terletak pada format data, pengemasan data, transporter data, dll.
`server`: Mengikat `Server` yang akan menampung kelas layanan yang dipublikasikan. Nilai default adalah `jsonrpc-http`. Atribut ini sesuai dengan `name` di bawah `servers` dalam file `config/autoload/server.php`, yang berarti kita perlu mendefinisikan `Server` yang sesuai.
`publishTo`: Mendefinisikan service center ke mana layanan akan dipublikasikan. Saat ini hanya mendukung `consul`, `nacos` atau kosong. Kosong berarti layanan tidak dipublikasikan ke service center, yang berarti Anda perlu menangani service discovery secara manual. Untuk menggunakan fitur ini, Anda perlu menginstal komponen [hyperf/service-governance](https://github.com/hyperf/service-governance) dan dependensi driver yang sesuai.

> Untuk menggunakan annotation `#[RpcService]`, Anda perlu `use Hyperf\RpcServer\Annotation\RpcService;`.

## Kustom Adapter Service Governance

Selain dukungan default untuk `Consul` dan `Nacos`, pengguna juga dapat mendaftarkan adapter kustom sesuai dengan kebutuhan mereka sendiri.

Kita dapat membuat FooService yang mengimplementasikan `Hyperf\ServiceGovernance\DriverInterface`

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

Kemudian buat listener dan daftarkan ke `DriverManager`.

```php
<?php

declare(strict_types=1);
/**
 * File ini adalah bagian dari Hyperf.
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
