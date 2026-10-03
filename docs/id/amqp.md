# Komponen AMQP

[hyperf/amqp](https://github.com/hyperf/amqp) adalah komponen yang mengimplementasikan standar AMQP, terutama digunakan untuk integrasi RabbitMQ.

## Instalasi

```bash
composer require hyperf/amqp
```

## Konfigurasi Default

| Konfigurasi | Tipe | Nilai Default | Keterangan |
| :--- | :--- | :--- | :--- |
| host | string | localhost | Host |
| port | int | 5672 | Port |
| user | string | guest | Username |
| password | string | guest | Password |
| vhost | string | / | vhost |
| concurrent.limit | int | 0 | Maksimal jumlah consumer bersamaan |
| pool | object | | Konfigurasi connection pool |
| pool.connections | int | 1 | Jumlah koneksi yang dipertahankan dalam satu proses |
| params | object | | Konfigurasi dasar |

```php
<?php

return [
    'enable' => true,
    'default' => [
        'host' => 'localhost',
        'port' => 5672,
        'user' => 'guest',
        'password' => 'guest',
        'vhost' => '/',
        'concurrent' => [
            'limit' => 1,
        ],
        'pool' => [
            'connections' => 1,
        ],
        'params' => [
            'insist' => false,
            'login_method' => 'AMQPLAIN',
            'login_response' => null,
            'locale' => 'en_US',
            'connection_timeout' => 3.0,
            // Usahakan untuk menjaganya dua kali lipat dari nilai heartbeat
            'read_write_timeout' => 6.0,
            'context' => null,
            'keepalive' => false,
            // Usahakan pastikan waktu konsumsi setiap pesan kurang dari waktu heartbeat
            'heartbeat' => 3,
            'close_on_destruct' => false,
        ],
    ],
    'pool2' => [
        ...
    ]
];
```

Anda dapat mengatur `pool` yang berbeda di fungsi `__construct` dari `producer` atau `consumer`, seperti `default` dan `pool2` yang disebutkan di atas.

## Producing Messages

Gunakan perintah `gen:producer` untuk membuat `producer`.

```bash
php bin/hyperf.php gen:amqp-producer DemoProducer
```

Di file DemoProducer, kita dapat mengubah field yang sesuai dengan annotation `#[Producer]` untuk mengganti `exchange` dan `routingKey` yang sesuai.
`payload` adalah data yang pada akhirnya akan dikirim ke message queue, jadi kita bisa menulis ulang method `__construct` sesuai kebutuhan, selama pada akhirnya kita menetapkan `payload`.
Contohnya sebagai berikut.

> Saat menggunakan annotation `#[Producer]`, Anda perlu `use Hyperf\Amqp\Annotation\Producer;` namespace;

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Producers;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;
use App\Models\User;

#[Producer(exchange: "hyperf", routingKey: "hyperf")]
class DemoProducer extends ProducerMessage
{
    public function __construct($id)
    {
        // Mengatur pool yang berbeda
        $this->poolName = 'pool2';

        $user = User::where('id', $id)->first();
        $this->payload = [
            'id' => $id,
            'data' => $user->toArray()
        ];
    }
}
```

Dapatkan instance `Hyperf\Amqp\Producer` melalui DI Container untuk menghasilkan pesan. Contoh berikut langsung menggunakan `ApplicationContext` untuk mendapatkan `Hyperf\Amqp\Producer`, cara ini sebenarnya kurang ideal. Untuk penggunaan DI Container yang lebih tepat, lihat bab [Dependency Injection](id/di.md).

```php
<?php
use Hyperf\Amqp\Producer;
use App\Amqp\Producers\DemoProducer;
use Hyperf\Context\ApplicationContext;

$message = new DemoProducer(1);
$producer = ApplicationContext::getContainer()->get(Producer::class);
$result = $producer->produce($message);
```

## Consuming Messages

Gunakan perintah `gen:amqp-consumer` untuk membuat `consumer`.

```bash
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

Di file DemoConsumer, kita dapat mengubah field yang sesuai dengan annotation `#[Consumer]` untuk mengganti `exchange`, `routingKey`, dan `queue` yang sesuai.
`$data` adalah data pesan yang telah diurai.
Contohnya sebagai berikut.

> Saat menggunakan annotation `#[Consumer]`, Anda perlu `use Hyperf\Amqp\Annotation\Consumer;` namespace;

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumers;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(exchange: "hyperf", routingKey: "hyperf", queue: "hyperf", nums: 1)]
class DemoConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        print_r($data);
        return Result::ACK;
    }
}
```

### Menonaktifkan Proses Consumer agar Tidak Otomatis Menyala

Secara default, setelah menggunakan annotation `#[Consumer]`, framework akan secara otomatis membuat sub-proses untuk menjalankan consumer, dan akan merestartnya setelah sub-proses keluar secara tidak normal.
Jika Anda sedang di tahap pengembangan dan melakukan debugging consumer, konsumsi pesan lain bisa mengganggu proses debugging.

Dalam kasus ini, Anda hanya perlu mengonfigurasi `enable=false` (default adalah `true` untuk mulai dengan service) di annotation `#[Consumer]`, atau override method kelas `isEnable()` di consumer yang sesuai untuk mengembalikan `false`.

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumers;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(exchange: "hyperf", routingKey: "hyperf", queue: "hyperf", nums: 1, enable: false)]
class DemoConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        print_r($data);
        return Result::ACK;
    }

    public function isEnable(): bool
    {
        return parent::isEnable();
    }
}
```

### Mengatur Jumlah Maksimum Konsumsi

Anda dapat mengubah properti `maxConsumption` di annotation `#[Consumer]` untuk mengatur jumlah maksimum pesan yang diproses oleh consumer ini. Setelah mencapai jumlah konsumsi yang ditentukan, proses consumer akan restart.

### Mengatur Konsumsi Bersamaan (Concurrent)

Ada tiga tempat yang memengaruhi tingkat konsumsi:

- Anda dapat mengubah atribut `nums` dari annotation `#[Consumer]` untuk menjalankan banyak consumer
- Ada atribut `$qos` di bawah class dasar `ConsumerMessage`. Anda dapat mengontrol jumlah pesan yang diambil dari server setiap kali dengan mengoverride nilai `prefetch_size` atau `prefetch_count` di `$qos`
- Parameter `concurrent.limit` di file konfigurasi mengontrol jumlah maksimum coroutine konsumsi

### Hasil Konsumsi

Framework menentukan perilaku respons pesan berdasarkan hasil yang dikembalikan oleh method `consume` di `Consumer`. Ada 4 jenis hasil respons: `\Hyperf\Amqp\Result::ACK`, `\Hyperf\Amqp\Result::NACK`, `\Hyperf\Amqp\Result::REQUEUE`, `\Hyperf\Amqp\Result::DROP`. Setiap nilai kembalian mewakili perilaku berikut:

| Nilai Kembalian | Perilaku |
| :--- | :--- |
| \Hyperf\Amqp\Result::ACK | Konfirmasi bahwa pesan dikonsumsi dengan benar |
| \Hyperf\Amqp\Result::NACK | Pesan tidak dikonsumsi dengan benar, respons menggunakan method `basic_nack` |
| \Hyperf\Amqp\Result::REQUEUE | Pesan tidak dikonsumsi dengan benar, respons menggunakan method `basic_reject`, dan masukkan kembali pesan ke antrean |
| \Hyperf\Amqp\Result::DROP | Pesan tidak dikonsumsi dengan benar, respons menggunakan method `basic_reject` |

### Konfigurasi QOS

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumers;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(exchange: "hyperf", routingKey: "hyperf", queue: "hyperf", nums: 1)]
class DemoConsumer extends ConsumerMessage
{
    protected ?array $qos = [
        // AMQP tidak mengimplementasikan konfigurasi ini secara default.
        'prefetch_size' => 0,
        // Jumlah maksimum pesan yang dapat diproses oleh consumer yang sama secara bersamaan.
        'prefetch_count' => 30,
        // Karena Hyperf secara default mengonsumsi satu queue per Channel, efek pengaturan global ke true/false sama saja.
        'global' => false,
    ];
    
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        print_r($data);
        return Result::ACK;
    }
}
```

### Menyesuaikan Jumlah Proses Consumer Berdasarkan Environment

Di annotation `#[Consumer]`, Anda dapat mengatur jumlah proses consumer melalui atribut `nums`. Jika Anda perlu mengatur jumlah proses consumer yang berbeda berdasarkan environment yang berbeda, Anda dapat mengoverride method `getNums` untuk mencapainya, seperti berikut:

```php
#[Consumer(
    exchange: 'hyperf',
    routingKey: 'hyperf',
    queue: 'hyperf',
    name: 'hyperf',
    nums: 1
)]
final class DemoConsumer extends ConsumerMessage
{
    public function getNums(): int
    {
        if (is_debug()) {
            return 10;
        }
        return parent::getNums();
    }
}
```

## Delayed Queue

Delayed queue AMQP tidak mengurutkan berdasarkan waktu tunda. Jadi, jika Anda mengirim task dengan tunda 10 detik, lalu mengirim task dengan tunda 5 detik ke queue yang sama, task 5 detik akan tetap dikonsumsi hanya setelah task 10 detik selesai.
Oleh karena itu, Anda perlu mengatur queue yang berbeda berdasarkan waktu. Jika Anda menginginkan delayed queue yang lebih fleksibel, Anda dapat mencoba menggunakan `async-queue` yang dikombinasikan dengan AMQP.

Selain itu, AMQP perlu mengunduh [delayed plugin](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases) dan mengaktifkannya untuk dapat digunakan secara normal.

```shell
wget https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases/download/3.9.0/rabbitmq_delayed_message_exchange-3.9.0.ez
cp rabbitmq_delayed_message_exchange-3.9.0.ez /opt/rabbitmq/plugins/
rabbitmq-plugins enable rabbitmq_delayed_message_exchange
```

### Producer

Gunakan perintah `gen:amqp-producer` untuk membuat `producer`. Berikut adalah contoh tipe `direct`. Untuk tipe lain seperti `fanout`, `topic`, cukup ubah `type` di producer dan consumer.

```bash
php bin/hyperf.php gen:amqp-producer DelayDirectProducer
```

Di file DelayDirectProducer, tambahkan `use ProducerDelayedMessageTrait;`, seperti berikut:

```php
<?php

namespace App\Amqp\Producer;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerDelayedMessageTrait;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Message\Type;

#[Producer]
class DelayDirectProducer extends ProducerMessage
{
    use ProducerDelayedMessageTrait;

    protected string $exchange = 'ext.hyperf.delay';

    protected Type|string $type = Type::DIRECT;

    protected array|string $routingKey = '';

    public function __construct($data)
    {
        $this->payload = $data;
    }
}
```

### Consumer

Gunakan perintah `gen:amqp-consumer` untuk membuat `consumer`.

```bash
php bin/hyperf.php gen:amqp-consumer DelayDirectConsumer
```

Di file `DelayDirectConsumer`, tambahkan penggunaan `use ProducerDelayedMessageTrait, ConsumerDelayedMessageTrait;`, seperti berikut:

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerDelayedMessageTrait;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Message\ProducerDelayedMessageTrait;
use Hyperf\Amqp\Message\Type;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(nums: 1)]
class DelayDirectConsumer extends ConsumerMessage
{
    use ProducerDelayedMessageTrait;
    use ConsumerDelayedMessageTrait;

    protected string $exchange = 'ext.hyperf.delay';
    
    protected string $queue = 'queue.hyperf.delay';
    
    protected Type|string $type = Type::DIRECT; //Type::FANOUT;
    
    protected array|string $routingKey = '';

    public function consumeMessage($data, AMQPMessage $message): Result
    {
        var_dump($data, 'delay+direct consumeTime:' . (microtime(true)));
        return Result::ACK;
    }
}
```

### Menghasilkan Pesan Tertunda

> Berikut adalah demonstrasi cara menggunakannya di Command, silakan lihat penggunaan sebenarnya untuk penggunaan spesifik.

Gunakan perintah `gen:command DelayCommand` untuk membuat `DelayCommand`. Sebagai berikut:

```php
<?php

declare(strict_types=1);

namespace App\Command;

use App\Amqp\Producer\DelayDirectProducer;
//use App\Amqp\Producer\DelayFanoutProducer;
//use App\Amqp\Producer\DelayTopicProducer;
use Hyperf\Amqp\Producer;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerInterface;

#[Command]
class DelayCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('demo:command');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        //1.delayed + direct
        $message = new DelayDirectProducer('delay+direct produceTime:'.(microtime(true)));
        //2.delayed + fanout
        //$message = new DelayFanoutProducer('delay+fanout produceTime:'.(microtime(true)));
        //3.delayed + topic
        //$message = new DelayTopicProducer('delay+topic produceTime:' . (microtime(true)));
        $message->setDelayMs(5000);
        $producer = ApplicationContext::getContainer()->get(Producer::class);
        $producer->produce($message);
    }
}
```

Jalankan baris perintah untuk menghasilkan pesan:

```
php bin/hyperf.php demo:command
```

## RPC Remote Procedure Call

Selain skenario message queue yang umum, kita juga dapat mengimplementasikan RPC remote procedure call melalui AMQP. Komponen ini juga menyediakan dukungan yang sesuai untuk implementasi ini.

### Membuat Consumer

Consumer yang digunakan oleh RPC pada dasarnya sama dengan implementasi consumer di skenario message queue biasa. Satu-satunya perbedaan adalah Anda perlu mengembalikan data ke producer dengan memanggil method `reply`.

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(exchange: "hyperf", routingKey: "hyperf", queue: "rpc.reply", name: "ReplyConsumer", nums: 1, enable: true)]
class ReplyConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        $data['message'] .= 'Reply:' . $data['message'];

        $this->reply($data, $message);

        return Result::ACK;
    }
}
```

### Memulai Panggilan RPC

Sebagai producer yang memulai panggilan RPC, caranya juga sangat sederhana. Dapatkan object `Hyperf\Amqp\RpcClient` melalui dependency injection container dan panggil method `call`. Hasil yang dikembalikan adalah data yang dibalas oleh consumer, seperti berikut:

```php
<?php
use Hyperf\Amqp\Message\DynamicRpcMessage;
use Hyperf\Amqp\RpcClient;
use Hyperf\Context\ApplicationContext;

$rpcClient = ApplicationContext::getContainer()->get(RpcClient::class);
// Atur Exchange dan RoutingKey sesuai dengan Consumer di DynamicRpcMessage
$result = $rpcClient->call(new DynamicRpcMessage('hyperf', 'hyperf', ['message' => 'Hello Hyperf'])); 

// $result:
// array(1) {
//     ["message"]=>
//     string(18) "Reply:Hello Hyperf"
// }
```

### RpcMessage Abstrak

Proses pemanggilan RPC di atas langsung menyelesaikan definisi Exchange dan RoutingKey melalui class `Hyperf\Amqp\Message\DynamicRpcMessage` dan mengirimkan data pesan. Dalam desain proyek produksi, kita dapat mengabstraksi RpcMessage untuk menyatukan definisi Exchange dan RoutingKey.

Kita dapat membuat class RpcMessage yang sesuai seperti `App\Amqp\FooRpcMessage` sebagai berikut:

```php
<?php
use Hyperf\Amqp\Message\RpcMessage;

class FooRpcMessage extends RpcMessage
{

    protected string $exchange = 'hyperf';

    protected array|string $routingKey = 'hyperf';
    
    public function __construct($data)
    {
        // Data yang akan diteruskan
        $this->payload = $data;
    }

}
```

Dengan cara ini, ketika kita melakukan panggilan RPC, kita hanya perlu meneruskan instance `FooRpcMessage` langsung ke method `call`, tanpa perlu mendefinisikan Exchange dan RoutingKey setiap kali memanggilnya.
