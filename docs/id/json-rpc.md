# Layanan JSON RPC

JSON RPC adalah standar protokol RPC ringan berbasis format JSON, yang mudah
digunakan dan dibaca. Di Hyperf, hal ini diimplementasikan oleh komponen
[hyperf/json-rpc](https://github.com/hyperf/json-rpc), yang dapat disesuaikan
untuk transmisi berbasis protokol HTTP, atau langsung berbasis protokol TCP
untuk transmisi.

## Instalasi

```bash
composer require hyperf/json-rpc
```
  
Ini hanyalah komponen pemrosesan protokol untuk JSON RPC, umumnya, Anda masih
memerlukan komponen [hyperf/rpc-server](https://github.com/hyperf/rpc-server)
atau [hyperf/rpc-client](https://github.com/hyperf/rpc-client) untuk memenuhi
skenario client dan server. Keduanya perlu diinstal jika digunakan secara
bersamaan: 

Untuk server JSON RPC:

```bash
composer require hyperf/rpc-server
```

Untuk client JSON RPC:

```bash
composer require hyperf/rpc-client
```

## Petunjuk Penggunaan

Layanan memiliki dua peran, salah satunya adalah `ServiceProvider`, yaitu
layanan yang menyediakan layanan untuk layanan lain, dan yang lainnya adalah
`ServiceConsumer`, yaitu layanan yang bergantung pada layanan lain. Sebuah
layanan dapat memainkan peran `ServiceProvider` dan `ServiceConsumer` secara
bersamaan. Kedua peran ini dapat secara langsung mendefinisikan dan membatasi
pemanggilan interface melalui `Service Contract`. Di Hyperf, ini dapat langsung
dipahami sebagai interface class `Interface`. Secara umum, interface class ini
akan ada di sisi provider maupun consumer.

### Mendefinisikan service provider

Sejauh ini, hanya bentuk annotation yang didukung untuk mendefinisikan
`ServiceProvider`, dan edisi berikutnya akan menambahkan lebih banyak bentuk
konfigurasi.
Kita dapat langsung mendefinisikan class melalui annotation `#[RpcService]` dan
mempublikasikan layanan ini:

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * Note that if you want to manage the service through the service center, you need to add the publishTo attribute in the annotation
 */
#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implement an addition method, simply consider that the parameters are int type
    public function add(int $a, int $b): int
    {
        // The specific implementation of the service method
        return $a + $b;
    }
}
```
 
`#[RpcService]` memiliki `4` parameter:  
Atribut `name` adalah nama yang mendefinisikan layanan. Cukup definisikan nama
yang unik secara global di sini. Hyperf akan menghasilkan ID yang sesuai
berdasarkan atribut ini dan mendaftarkannya ke service center;
Atribut `protocol` mendefinisikan protokol yang diekspos oleh layanan. Saat ini,
hanya `jsonrpc-http`, `jsonrpc`, dan `jsonrpc-tcp-length-check` yang didukung,
yang masing-masing sesuai dengan protokol HTTP dan dua protokol di bawah
protokol TCP. Nilai default-nya adalah `jsonrpc-http`, nilai di sini sesuai
dengan `key` dari protokol yang terdaftar di `Hyperf\Rpc\ProtocolManager`. Mereka
pada dasarnya adalah protokol JSON RPC, perbedaannya terletak pada format data,
pengemasan data, dan pengirim data.
Atribut `server` adalah `Server` yang dibawa oleh class layanan penerbitan yang
terikat, nilai default-nya adalah `jsonrpc-http`. Atribut ini sesuai dengan
`name` di bawah `servers` dalam file `config/autoload/server.php`, yang juga
berarti bahwa kita perlu mendefinisikan `Server` yang sesuai, kita akan
membahas cara menanganinya di bab berikutnya;
Atribut `publishTo` mendefinisikan service center tempat layanan akan
dipublikasikan. Saat ini hanya mendukung `consul` atau null. Jika bernilai null,
artinya layanan tidak akan dipublikasikan ke service center, yang juga berarti
Anda harus menangani service discovery secara manual. Jika bernilai `consul`,
Anda perlu mengonfigurasi konfigurasi terkait dari komponen
[hyperf/consul](zh-cn/consul.md). Untuk menggunakan fungsi ini, Anda perlu
menginstal komponen [hyperf/service-governance](https://github.com/hyperf/service-governance),
silakan lihat bagian [Pendaftaran Layanan](zh-cn/service-register.md) untuk detailnya.

> Untuk menggunakan annotation `#[RpcService]`, diperlukan namespace `use Hyperf\RpcServer\Annotation\RpcService;`."

#### Mendefinisikan JSON RPC Server

HTTP Server (protokol `jsonrpc-http` disesuaikan)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The other configuration of the file is omitted here
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

TCP Server (protokol `jsonrpc` disesuaikan)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The other configuration of the file is omitted here
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

TCP Server (protokol `jsonrpc-tcp-length-check` disesuaikan)

Protokol saat ini adalah protokol ekstensi dari `jsonrpc`, dan pengguna dapat
dengan mudah memodifikasi `settings` yang sesuai untuk menggunakan protokol ini.
Contohnya adalah sebagai berikut:

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The other configuration of the file is omitted here
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

### Publikasikan ke service center
   
Saat ini, hanya mendukung publikasi layanan ke `consul`, dan service center
lainnya akan ditambahkan di masa mendatang.
Mempublikasikan layanan ke `consul` juga sangat mudah di Hyperf. Muat komponen
Consul melalui `composer require hyperf/consul` (jika sudah terinstal, Anda dapat
mengabaikan langkah ini), lalu konfigurasikan konfigurasi `Consul` Anda di file
konfigurasi `config/autoload/consul.php`, contohnya adalah sebagai berikut:

```php
<?php

return [
    'uri' => 'http://127.0.0.1:8500',
];
```

Setelah konfigurasi selesai, saat layanan dijalankan, Hyperf akan secara
otomatis mendaftarkan layanan tersebut (yang didefinisikan dengan atribut
`publishTo` bernilai `consul` oleh `#[RpcService]`) ke service center.

> Saat ini, hanya protokol `jsonrpc` dan `jsonrpc-http` yang didukung untuk
dipublikasikan ke service center, protokol lain belum mengimplementasikan
pendaftaran layanan.

### Mendefinisikan service consumer

Sebuah `ServiceConsumer` dapat dianggap sebagai client class. Di Hyperf, Anda
tidak perlu berurusan dengan hal-hal yang berkaitan dengan koneksi dan request,
Anda hanya perlu melakukan beberapa konfigurasi autentikasi.

#### Membuat proxy consumer class secara otomatis

Anda dapat secara otomatis membuat consumer class melalui dynamic proxy dengan
melakukan beberapa konfigurasi sederhana di file konfigurasi
`config/autoload/services.php`.

```php
<?php
return [
    'consumers' => [
        [
            // name must be the same as the name attribute of the service provider
            'name' => 'CalculatorService',
            // Service interface name. It's optional and the default value is equal to the value configured by name. If name is directly defined as an interface class, you can ignore this configuration. If name is a string, you need to configure service to correspond to the interface class
            'service' => \App\JsonRpc\CalculatorServiceInterface::class,
            // Corresponding container object. It's optional and the default value is equal to the value of the service configuration. To define the key of dependency injection.
            'id' => \App\JsonRpc\CalculatorServiceInterface::class,
            // The service agreement of the service provider. It's optional and the default value is jsonrpc-http
            // jsonrpc-http, jsonrpc, and jsonrpc-tcp-length-check are available
            'protocol' => 'jsonrpc-http',
            // Load balancing algorithm, optional, the default value is random
            'load_balancer' => 'random',
            // From which service center the consumer will obtain node information, if it is not configured, the node information will not be obtained from the service center
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // If the registry configuration above is not specified, it means to directly consume the specified node. Configure the node information of the service provider through the nodes parameter below
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
            // Configuration, this may affect Packer and Transporter
            'options' => [
                'connect_timeout' => 5.0,
                'recv_timeout' => 5.0,
                'settings' => [
                    // Different protocol, different configuration
                    'open_eof_split' => true,
                    'package_eof' => "\r\n",
                    // 'open_length_check' => true,
                    // 'package_length_type' => 'N',
                    // 'package_length_offset' => 0,
                    // 'package_body_offset' => 4,
                ],
                // Retrie count, the default value is 2, no retry will be performed when the packet is received over time. Only supports JsonRpcPoolTransporter, currently.
                'retry_count' => 2,
                // Retry interval, in milliseconds
                'retry_interval' => 100,
                // The following configuration will be used when using JsonRpcPoolTransporter
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

Proxy object dari client class dibuat secara otomatis saat aplikasi dimulai,
dan nilai dari item konfigurasi `id` digunakan di dalam container (jika tidak
diatur, nilai dari item konfigurasi `service` yang akan digunakan sebagai
gantinya) untuk menambahkan hubungan binding. Seperti client class yang ditulis
secara manual, client dapat digunakan secara langsung dengan menginjeksikan
interface `CalculatorServiceInterface`.

> Ketika service provider menggunakan nama interface class untuk
mempublikasikan nama layanan, hanya item konfigurasi `name` yang perlu diatur
sebagai nama interface class pada service consumer, dan tidak perlu mengatur
item konfigurasi `id` dan `service` berulang kali.

#### Membuat consumer class secara manual

Jika Anda memiliki lebih banyak kebutuhan untuk consumer class, Anda dapat
membuat consumer class secara manual untuk mencapainya. Anda hanya perlu
mendefinisikan class dan atribut terkait.

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcClient\AbstractServiceClient;

class CalculatorServiceConsumer extends AbstractServiceClient implements CalculatorServiceInterface
{
    /**
     * Define the service name of the corresponding service provider
     * @var string 
     */
    protected $serviceName = 'CalculatorService';
    
    /**
     * Define the protocol of the corresponding service provider
     * @var string 
     */
    protected $protocol = 'jsonrpc-http';

    public function add(int $a, int $b): int
    {
        return $this->__request(__FUNCTION__, compact('a', 'b'));
    }
}
```

Kemudian Anda perlu mendefinisikan tag di file konfigurasi untuk mendapatkan
informasi node dari service center mana. File tersebut berada di
`config/autoload/services.php` (jika belum ada, Anda dapat membuatnya sendiri)

```php
<?php
return [
    'consumers' => [
        [
            // $serviceName corresponding to the consumer class
            'name' => 'CalculatorService',
            // From which service center the consumer will obtain node information. If it is not configured, the node information will not be obtained from the service center
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // If the registry configuration above is not specified, it means to directly consume the specified node. Configure the node information of the service provider through the nodes parameter below
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
        ]
    ],
];
```


Dengan cara ini, kita dapat menggunakan class `CalculatorService` untuk
mencapai konsumsi layanan. Agar logika hubungan di sini lebih masuk akal,
hubungan antara `CalculatorServiceInterface` dan `CalculatorServiceConsumer`
juga harus didefinisikan dalam `config/autoload/dependencies.php`. Contohnya
adalah sebagai berikut:

```php
return [
    App\JsonRpc\CalculatorServiceInterface::class => App\JsonRpc\CalculatorServiceConsumer::class,
];
```

Dengan cara ini, client dapat digunakan dengan menginjeksikan interface
`CalculatorServiceInterface`.

#### Penggunaan kembali konfigurasi

Umumnya, sebuah service consumer akan mengonsumsi beberapa service provider
secara bersamaan. Ketika kita menemukan service provider melalui service center,
konfigurasi `registry` di file `config/autoload/services.php` mungkin dikonfigurasi
berulang kali. Namun, service center kita mungkin disatukan, yang berarti beberapa
service consumer dikonfigurasi untuk menarik informasi node dari service center
yang sama. Pada saat ini, kita dapat mengimplementasikannya melalui kode PHP
seperti `variabel PHP` atau `loop` untuk menghasilkan file konfigurasi.

##### Menghasilkan konfigurasi dengan variabel PHP

```php
<?php
$registry = [
   'protocol' => 'consul',
   'address' => 'http://127.0.0.1:8500',
];
return [
    // The following FooService and BarService are only examples of multi-services, and they do not actually exist in the document examples
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

##### Menghasilkan konfigurasi dengan loop

```php
<?php
return [
    'consumers' => value(function () {
        $consumers = [];
        // This example automatically creates the configuration form of the proxy consumer class. There are two configuration items - name and service. This is not the only method. Just to explain that the configuration can be generated through PHP code
        // The following FooServiceInterface and BarServiceInterface are only examples of multi-services, and they do not actually exist in the document examples
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

### Mengembalikan object PHP

Ketika framework mengimpor `symfony/serializer (^5.0)` dan
`symfony/property-access (^5.0)`, konfigurasikan hubungan pemetaan di
`dependencies.php`

```php
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

`NormalizerInterface` akan mendukung serialisasi dan deserialisasi object. Array
object tipe `MathValue[]` seperti ini belum didukung saat ini.

Mendefinisikan object kembalian (return object)

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

Memanggil di controller

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

Framework menyediakan `Transporter` berbasis connection pool, yang secara efektif
dapat menghindari masalah pembuatan terlalu banyak koneksi selama konkurensi tinggi.
Di sini Anda dapat menggunakan `JsonRpcPoolTransporter` untuk menggantikan
`JsonRpcTransporter`.

Modifikasi file `dependencies.php`

```php
<?php

declare(strict_types=1);

use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\JsonRpcTransporter;

return [
    JsonRpcTransporter::class => JsonRpcPoolTransporter::class,
];

```
