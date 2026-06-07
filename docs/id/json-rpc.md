# JSON-RPC Service

JSON-RPC adalah protokol RPC ringan berbasis JSON yang mudah digunakan dan dibaca. Di Hyperf, ini diimplementasikan oleh komponen [hyperf/json-rpc](https://github.com/hyperf/json-rpc), yang memungkinkan transmisi kustom berdasarkan protokol HTTP atau langsung berdasarkan protokol TCP.

## Instalasi

```bash
composer require hyperf/json-rpc
```

Komponen ini hanya untuk penanganan protokol dalam JSON-RPC. Umumnya, Anda tetap perlu mengkombinasikannya dengan [hyperf/rpc-server](https://github.com/hyperf/rpc-server) atau [hyperf/rpc-client](https://github.com/hyperf/rpc-client) untuk memenuhi skenario server-side dan client-side. Jika menggunakan keduanya, keduanya harus diinstal:

Untuk menggunakan JSON-RPC server:

```bash
composer require hyperf/rpc-server
```

Untuk menggunakan JSON-RPC client:

```bash
composer require hyperf/rpc-client
```

## Penggunaan

Ada dua peran untuk layanan: `Service Provider`, yang menyediakan layanan ke layanan lain, dan `Service Consumer`, yang bergantung pada layanan lain. Sebuah layanan dapat menjadi `Service Provider` dan `Service Consumer` secara bersamaan. Keduanya dapat mendefinisikan dan membatasi pemanggilan antarmuka melalui `Service Contract`. Di Hyperf, ini dapat langsung dipahami sebagai sebuah `Interface`. Umumnya, kelas interface ini akan muncul di sisi provider maupun consumer.

### Mendefinisikan Service Provider

Saat ini, mendefinisikan `Service Provider` hanya didukung melalui annotation; dukungan untuk definisi berbasis konfigurasi akan ditambahkan di iterasi mendatang.
Kita dapat langsung mendefinisikan sebuah kelas menggunakan annotation `#[RpcService]` untuk mempublikasikan layanan ini:

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * Catatan: Jika Anda ingin mengelola layanan melalui service center, Anda perlu menambahkan atribut publishTo di dalam annotation.
 */
#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implementasi method penjumlahan, sederhananya parameter diasumsikan bertipe int
    public function add(int $a, int $b): int
    {
        // Implementasi method layanan
        return $a + $b;
    }
}
```

`#[RpcService]` memiliki `4` parameter:
`name`: Mendefinisikan nama layanan. Cukup definisikan nama yang unik secara global di sini, dan Hyperf akan menghasilkan ID yang sesuai berdasarkan atribut ini untuk didaftarkan ke service center.
`protocol`: Mendefinisikan protokol yang diekspos oleh layanan. Saat ini hanya mendukung `jsonrpc-http`, `jsonrpc`, dan `jsonrpc-tcp-length-check`, yang masing-masing sesuai dengan dua protokol di bawah protokol HTTP dan protokol TCP. Nilai default adalah `jsonrpc-http`. Nilai-nilai di sini sesuai dengan `key` dari protokol yang terdaftar di `Hyperf\Rpc\ProtocolManager`. Pada dasarnya semuanya adalah protokol JSON-RPC, perbedaannya terletak pada format data, pengemasan data, transporter data, dll.
`server`: Mengikat `Server` yang akan menampung kelas layanan yang dipublikasikan. Nilai default adalah `jsonrpc-http`. Atribut ini sesuai dengan `name` di bawah `servers` dalam file `config/autoload/server.php`, yang berarti kita perlu mendefinisikan `Server` yang sesuai.
`publishTo`: Mendefinisikan service center ke mana layanan akan dipublikasikan. Saat ini hanya mendukung `consul`, `nacos` atau kosong. Kosong berarti layanan tidak dipublikasikan ke service center, yang berarti Anda perlu menangani service discovery secara manual. Untuk menggunakan fitur ini, Anda perlu menginstal komponen [hyperf/service-governance](https://github.com/hyperf/service-governance) dan dependensi driver yang sesuai. Untuk detailnya, silakan merujuk ke bab [Service Registration](id/service-register.md).

> Untuk menggunakan annotation `#[RpcService]`, Anda perlu `use Hyperf\RpcServer\Annotation\RpcService;`.

#### Mendefinisikan JSON-RPC Server

HTTP Server (kompatibel dengan protokol `jsonrpc-http`)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Konfigurasi lain dari file ini diabaikan
    'servers' => [
        [
            'name' => 'jsonrpc-http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9504,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [\Hyperf\JsonRpc\HttpServer::class, 'onRequest'],
            ],
        ],
    ],
];
```

TCP Server (kompatibel dengan protokol `jsonrpc`)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Konfigurasi lain dari file ini diabaikan
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_eof_split' => true,
                'package_eof' => "\r\n",
                'package_max_length' => 1024 * 1024 * 2,
            ],
        ],
    ],
];
```

TCP Server (kompatibel dengan protokol `jsonrpc-tcp-length-check`)

Protokol saat ini adalah ekstensi dari `jsonrpc`. Pengguna dapat dengan mudah memodifikasi `settings` yang sesuai untuk menggunakan protokol ini. Contohnya sebagai berikut.

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Konfigurasi lain dari file ini diabaikan
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
                'package_max_length' => 1024 * 1024 * 2,
            ],
        ],
    ],
];
```

### Mempublikasikan ke Service Center

Saat ini, hanya mendukung publikasi layanan ke `consul` dan `nacos`. Lainnya akan ditambahkan kemudian.
Mempublikasikan layanan ke `consul` di Hyperf juga sangat mudah. Referensi komponen melalui `composer require hyperf/service-governance-consul` (lewati langkah ini jika sudah terinstal), kemudian konfigurasikan `drivers.consul` di file konfigurasi `config/autoload/services.php`.
Mempublikasikan layanan ke `nacos` serupa. Referensi komponen melalui `composer require hyperf/service-governance-nacos` (lewati langkah ini jika sudah terinstal), kemudian konfigurasikan `drivers.nacos` di file konfigurasi `config/autoload/services.php`. Contohnya sebagai berikut:

```php
<?php
return [
    'enable' => [
        'discovery' => true,
        'register' => true,
    ],
    'consumers' => [],
    'providers' => [],
    'drivers' => [
        'consul' => [
            'uri' => 'http://127.0.0.1:8500',
            'token' => '',
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
        ],
    ],
];
```

Setelah konfigurasi, ketika layanan dimulai, Hyperf akan secara otomatis mendaftarkan layanan dengan atribut `publishTo` yang didefinisikan sebagai `consul` atau `nacos` di `#[RpcService]` ke service center yang sesuai.

> Saat ini, hanya protokol `jsonrpc` dan `jsonrpc-http` yang didukung untuk dipublikasikan ke service center. Protokol lain belum mengimplementasikan service registration.

### Mendefinisikan Service Consumer

`Service Consumer` dapat dipahami sebagai kelas client, tetapi di Hyperf, Anda tidak perlu menangani masalah koneksi dan request. Anda hanya perlu melakukan beberapa konfigurasi identifikasi.

#### Membuat Proxy Consumer Classes Secara Otomatis

Anda dapat secara otomatis membuat consumer classes melalui dynamic proxy dengan melakukan beberapa konfigurasi sederhana di file konfigurasi `config/autoload/services.php`.

```php
<?php
return [
    // Konfigurasi lain di level yang sama diabaikan
    'consumers' => [
        [
            // name harus sama dengan atribut name dari service provider
            'name' => 'CalculatorService',
            // Nama interface layanan, opsional. Nilai default sama dengan nilai yang dikonfigurasi dari name. Jika name didefinisikan langsung sebagai kelas interface, baris konfigurasi ini dapat diabaikan. Jika name adalah string, maka service perlu dikonfigurasi untuk sesuai dengan kelas interface.
            'service' => \App\JsonRpc\CalculatorServiceInterface::class,
            // ID objek container yang sesuai, opsional. Nilai default sama dengan nilai yang dikonfigurasi dari service, digunakan untuk mendefinisikan key untuk dependency injection.
            'id' => \App\JsonRpc\CalculatorServiceInterface::class,
            // Protokol layanan dari service provider, opsional. Nilai default adalah jsonrpc-http
            // Opsional: jsonrpc-http jsonrpc jsonrpc-tcp-length-check
            'protocol' => 'jsonrpc-http',
            // Algoritma load balancing, opsional. Nilai default adalah random
            'load_balancer' => 'random',
            // Dari service center mana consumer ini mendapatkan node information? Jika tidak dikonfigurasi, node information tidak akan diambil dari service center.
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // Jika konfigurasi registry di atas tidak ditentukan, itu berarti konsumsi langsung ke node yang ditentukan. Konfigurasikan node information service provider melalui parameter nodes di bawah.
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
            // Item konfigurasi, akan mempengaruhi Packer dan Transporter
            'options' => [
                'connect_timeout' => 5.0,
                'recv_timeout' => 5.0,
                'settings' => [
                    // Konfigurasikan berbeda sesuai dengan protokol
                    'open_eof_split' => true,
                    'package_eof' => "\r\n",
                    // 'open_length_check' => true,
                    // 'package_length_type' => 'N',
                    // 'package_length_offset' => 0,
                    // 'package_body_offset' => 4,
                ],
                // Jumlah retry, nilai default adalah 2. Tidak ada retry untuk packet timeout. Sementara hanya mendukung JsonRpcPoolTransporter
                'retry_count' => 2,
                // Interval retry, milidetik
                'retry_interval' => 100,
                // Interval heartbeat saat menggunakan multiplexed RPC, null berarti tidak ada heartbeat yang dipicu
                'heartbeat' => 30,
                // Konfigurasi berikut digunakan saat menggunakan JsonRpcPoolTransporter
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 32,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 3.0,
                    'heartbeat' => -1,
                    'max_idle_time' => 60.0,
                ],
            ],
        ]
    ],
];
```

Ketika aplikasi dimulai, ia akan secara otomatis membuat proxy object untuk kelas client dan menambahkan hubungan binding di dalam container menggunakan nilai dari item konfigurasi `id` (jika tidak disetel, nilai dari item konfigurasi `service` akan digunakan sebagai gantinya). Ini sama seperti kelas client yang ditulis secara manual: Anda dapat langsung menggunakan client dengan menginjeksi interface `CalculatorServiceInterface`.

> Ketika service provider menggunakan nama kelas interface untuk mempublikasikan nama layanan, di sisi service consumer, Anda hanya perlu mengatur item konfigurasi `name` ke nama kelas interface, tanpa perlu mengatur item konfigurasi `id` dan `service` secara berulang.

#### Membuat Consumer Classes Secara Manual

Jika Anda memiliki lebih banyak kebutuhan untuk consumer class, Anda dapat mengimplementasikannya dengan membuat consumer class secara manual, cukup dengan mendefinisikan sebuah kelas dan atribut-atribut terkaitnya.

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcClient\AbstractServiceClient;

class CalculatorServiceConsumer extends AbstractServiceClient implements CalculatorServiceInterface
{
    /**
     * Mendefinisikan nama layanan yang sesuai dengan service provider
     */
    protected string $serviceName = 'CalculatorService';
    
    /**
     * Mendefinisikan protokol layanan yang sesuai dengan service provider
     */
    protected string $protocol = 'jsonrpc-http';

    public function add(int $a, int $b): int
    {
        return $this->__request(__FUNCTION__, compact('a', 'b'));
    }
}
```

Kemudian Anda juga perlu mendefinisikan konfigurasi di file konfigurasi untuk menandai dari service center mana mendapatkan node information, terletak di `config/autoload/services.php` (buat sendiri jika belum ada)

```php
<?php
return [
    // Konfigurasi lain di level yang sama diabaikan
    'consumers' => [
        [
            // Sesuai dengan $serviceName dari consumer class
            'name' => 'CalculatorService',
            // Dari service center mana consumer ini mendapatkan node information? Jika tidak dikonfigurasi, node information tidak akan diambil dari service center.
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // Jika konfigurasi registry di atas tidak ditentukan, itu berarti konsumsi langsung ke node yang ditentukan. Konfigurasikan node information service provider melalui parameter nodes di bawah.
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
        ]
    ],
];
```

Dengan cara ini, kita dapat merealisasikan konsumsi layanan melalui kelas `CalculatorService`. Untuk membuat hubungan logika di sini lebih masuk akal, hubungan antara `CalculatorServiceInterface` dan `CalculatorServiceConsumer` juga harus didefinisikan di `config/autoload/dependencies.php`. Contohnya sebagai berikut:

```php
return [
    App\JsonRpc\CalculatorServiceInterface::class => App\JsonRpc\CalculatorServiceConsumer::class,
];
```

Ini memungkinkan Anda menggunakan client dengan menginjeksi interface `CalculatorServiceInterface`.

#### Menggunakan Kembali Konfigurasi

Biasanya, sebuah service consumer akan mengonsumsi beberapa service provider secara bersamaan. Ketika kita menemukan service provider melalui service center, konfigurasi `registry` mungkin akan diulang berkali-kali di file konfigurasi `config/autoload/services.php`. Namun biasanya, service center kita mungkin terpusat, yang berarti beberapa konfigurasi service consumer mengambil node information dari service center yang sama. Pada saat ini, kita dapat merealisasikan pembuatan file konfigurasi melalui `PHP variables` atau `loops` dan kode PHP lainnya.

##### Membuat konfigurasi melalui PHP variables

```php
<?php
$registry = [
   'protocol' => 'consul',
   'address' => 'http://127.0.0.1:8500',
];
return [
    // FooService dan BarService di bawah ini hanya contoh beberapa layanan, dan sebenarnya tidak ada dalam contoh dokumentasi.
    'consumers' => [
        [
            'name' => 'FooService',
            'registry' => $registry,
        ],
        [
            'name' => 'BarService',
            'registry' => $registry,
        ]
    ],
];
```

##### Membuat konfigurasi melalui loops

```php
<?php
return [
    // Konfigurasi lain di level yang sama diabaikan
    'consumers' => value(function () {
        $consumers = [];
        // Di sini mengilustrasikan bentuk konfigurasi untuk membuat proxy consumer classes secara otomatis. Oleh karena itu, ada dua item konfigurasi: name dan service. Pendekatan di sini tidak unik, hanya mengilustrasikan bahwa konfigurasi dapat dibuat melalui kode PHP.
        // FooServiceInterface dan BarServiceInterface di bawah ini hanya contoh beberapa layanan, dan sebenarnya tidak ada dalam contoh dokumentasi.
        $services = [
            'FooService' => App\JsonRpc\FooServiceInterface::class,
            'BarService' => App\JsonRpc\BarServiceInterface::class,
        ];
        foreach ($services as $name => $interface) {
            $consumers[] = [
                'name' => $name,
                'service' => $interface,
                'registry' => [
                   'protocol' => 'consul',
                   'address' => 'http://127.0.0.1:8500',
                ]
            ];
        }
        return $consumers;
    }),
];
```

### Mengembalikan PHP Objects

Ketika framework mengimpor `symfony/serializer (^5.0)` dan `symfony/property-access (^5.0)`, serta mengonfigurasi hubungan mapping di `dependencies.php`

```php
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

`NormalizerInterface` akan mendukung serialization dan deserialization objek. Array objek seperti `MathValue[]` sementara tidak didukung.

Mendefinisikan return object

```php
<?php

declare(strict_types=1);

namespace App\JsonRpc;

class MathValue
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
```

Menulis ulang file interface

```php
<?php

declare(strict_types=1);

namespace App\JsonRpc;

interface CalculatorServiceInterface
{
    public function sum(MathValue $v1, MathValue $v2): MathValue;
}
```

Pemanggilan di controller

```php
<?php

use Hyperf\Context\ApplicationContext;
use App\JsonRpc\CalculatorServiceInterface;
use App\JsonRpc\MathValue;

$client = ApplicationContext::getContainer()->get(CalculatorServiceInterface::class);

/** @var MathValue $result */
$result = $client->sum(new MathValue(1), new MathValue(2));

var_dump($result->value);
```

### Menggunakan JsonRpcPoolTransporter

Framework menyediakan `Transporter` berbasis connection pool, yang dapat secara efektif menghindari masalah pembuatan terlalu banyak koneksi saat konkurensi tinggi. Di sini, Anda dapat mengganti `JsonRpcTransporter` dengan `JsonRpcPoolTransporter`.

Memodifikasi file `dependencies.php`

```php
<?php

declare(strict_types=1);

use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\JsonRpcTransporter;

return [
    JsonRpcTransporter::class => JsonRpcPoolTransporter::class,
];
```
