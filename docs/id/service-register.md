# Registrasi Layanan

Setelah membagi layanan, jumlah layanan akan menjadi sangat besar, dan setiap layanan mungkin memiliki banyak node kluster untuk menyediakannya. Maka untuk memastikan jalannya sistem secara normal, pasti diperlukan komponen terpusat untuk mengintegrasikan berbagai layanan, yaitu mengumpulkan informasi layanan yang tersebar di mana-mana. Informasi yang dikumpulkan dapat berupa nama, alamat, jumlah, dll dari komponen penyedia layanan. Setiap komponen memiliki perangkat pemantau, ketika status suatu layanan di komponen ini berubah, hal itu dilaporkan ke komponen terpusat untuk memperbarui statusnya. Saat pemanggil (caller) layanan meminta suatu layanan, ia pertama-tama akan pergi ke komponen terpusat untuk mendapatkan informasi komponen yang dapat menyediakan layanan tersebut (IP, port, dll), dan melalui strategi default atau custom, memilih salah satu penyedia layanan untuk diakses, sehingga mewujudkan pemanggilan layanan. Komponen terpusat ini umumnya kita sebut sebagai `Service Center`. Di dalam Hyperf, kami telah mengimplementasikan dukungan komponen dengan `Consul` dan `Nacos` sebagai Service Center, dan di masa mendatang akan mendukung lebih banyak Service Center.

# Instalasi

## Instalasi Unified Access Layer (Lapisan Akses Terpadu)

```bash
composer require hyperf/service-governance
```

## Memilih dan Menginstal Adapter yang Sesuai

Registrasi layanan mendukung `Consul` dan `Nacos`. Silakan mengimpor komponen adapter yang sesuai dengan kebutuhan.

- Consul

```shell
composer require hyperf/service-governance-consul
```

- Nacos

```shell
composer require hyperf/service-governance-nacos
```

# File Konfigurasi

Komponen ini dikendalikan oleh file konfigurasi `config/autoload/services.php`. File konfigurasinya adalah sebagai berikut:

```php
return [
    'enable' => [
        // Mengaktifkan service discovery
        'discovery' => true,
        // Mengaktifkan registrasi layanan
        'register' => true,
    ],
    // Konfigurasi terkait consumer layanan
    'consumers' => [],
    // Konfigurasi terkait provider layanan
    'providers' => [],
    // Konfigurasi terkait driver layanan
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
            // url server nacos seperti https://nacos.hyperf.io, Prioritasnya lebih tinggi daripada host:port
            // 'url' => '',
            // Info host nacos
            'host' => '127.0.0.1',
            'port' => 8848,
            // Info akun nacos
            'username' => null,
            'password' => null,
            'guzzle' => [
                'config' => null,
            ],
            'group_name' => 'api',
            'namespace_id' => 'namespace_id',
            'heartbeat' => 5,
            'ephemeral' => false, // Apakah akan mendaftarkan instance sementara (ephemeral)
        ],
    ],
];
```

# Registrasi Layanan

Registrasi layanan dapat mendefinisikan sebuah class melalui annotation `#[RpcService]`, yang berarti mempublikasikan layanan ini. Saat ini Hyperf hanya mengadaptasi protokol JSON RPC. Detail spesifiknya dapat dilihat pada bab [Layanan JSON RPC](id/json-rpc.md).

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Mengimplementasikan method penambahan, di sini secara sederhana dianggap bahwa parameter bertipe int
    public function calculate(int $a, int $b): int
    {
        // Di sini adalah implementasi spesifik dari method layanan
        return $a + $b;
    }
}
```

Terdapat `4` parameter pada `#[RpcService]`:   
Atribut `name` mendefinisikan nama dari layanan ini. Cukup tentukan nama unik secara global di sini, Hyperf akan membuat ID yang sesuai berdasarkan atribut ini untuk didaftarkan ke Service Center;   
Atribut `protocol` mendefinisikan protokol yang terekspos dari layanan tersebut. Saat ini hanya mendukung `jsonrpc` dan `jsonrpc-http`, yang masing-masing sesuai dengan protokol di bawah protokol TCP dan protokol HTTP. Nilai default-nya adalah `jsonrpc-http`, dan nilainya di sini sesuai dengan `key` dari protokol yang terdaftar dalam `Hyperf\Rpc\ProtocolManager`. Keduanya secara esensial adalah protokol JSON RPC, perbedaannya terletak pada pemformatan data, pengemasan data, pengiriman data, dll.   
Atribut `server` mengikat layanan ini untuk dipublikasikan pada `Server` yang dituju. Nilai default-nya adalah `jsonrpc-http`, dan atribut ini sesuai dengan `name` di bawah `servers` dalam file `config/autoload/server.php`, yang berarti kita perlu mendefinisikan `Server` yang sesuai;   
Atribut `publishTo` mendefinisikan Service Center tujuan layanan ini akan dipublikasikan. Saat ini hanya mendukung `consul`, `nacos`, atau kosong. Bila kosong, layanan tidak akan dipublikasikan ke Service Center, yang berarti Anda perlu menangani masalah service discovery secara manual. Untuk menggunakan fitur ini, Anda perlu menginstal komponen [hyperf/service-governance](https://github.com/hyperf/service-governance) dan dependensi driver yang sesuai;

> Penggunaan annotation `#[RpcService]` memerlukan namespace `use Hyperf\RpcServer\Annotation\RpcService;`.

## Adapter Service Governance Kustom

Selain secara default mendukung `Consul` dan `Nacos`, pengguna juga dapat mendaftarkan adapter kustom (custom adapter) sesuai dengan kebutuhannya.

Kita dapat membuat `FooService` yang mengimplementasikan `Hyperf\ServiceGovernance\DriverInterface`

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

Kemudian buat sebuah listener (pendengar) dan daftarkan ke `DriverManager`.

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
