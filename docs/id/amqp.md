# AMQP

[hyperf/amqp](https://github.com/hyperf/amqp)

## Installation

```bash
composer require hyperf/amqp
```

## Konfigurasi Default

|   Konfigurasi  |  Tipe  |  Nilai default   |                        Catatan                       |
|:----------------:|:------:|:----------------:|:---------------------------------------------------:|
|       host       | string |     localhost    |                         Host                        |
|       port       |  int   |       5672       |                      Nomor port                      |
|       user       | string |       guest      |                       Username                      |
|     password     | string |       guest      |                       Password                      |
|      vhost       | string |         /        |                        vhost                        |
| concurrent.limit |  int   |         0        |      Jumlah maksimum yang dikonsumsi secara bersamaan      |
|       pool       | object |                  |            Konfigurasi connection pool              |
| pool.connections |  int   |         1        |    Jumlah koneksi yang dipelihara di dalam proses   |
|      params      | object |                  |                  Konfigurasi dasar                  |

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
            // Try to maintain twice value heartbeat as much as possible
            'read_write_timeout' => 3.0,
            'context' => null,
            'keepalive' => false,
            // Try to ensure that the consumption time of each message is less than the heartbeat time as much as possible
            'heartbeat' => 0,
            'close_on_destruct' => false,
        ],
    ],
    'pool2' => [
        ...
    ]
];
```

## Mengirim Pesan

Gunakan perintah generator untuk membuat producer.
```bash
php bin/hyperf.php gen:amqp-producer DemoProducer
```

Kita dapat memodifikasi anotasi Producer untuk mengganti exchange dan routingKey.
Payload adalah data yang akhirnya dikirim ke message queue, sehingga kita
dapat menulis ulang metode `__construct` dengan mudah, cukup pastikan payload
telah diisi.

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Producers;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;
use App\Models\User;

#[Producer(exchange: 'hyperf', routingKey: 'hyperf')]
class DemoProducer extends ProducerMessage
{
    public function __construct($id)
    {
        $user = User::where('id', $id)->first();
        $this->payload = [
            'id' => $id,
            'data' => $user->toArray()
        ];
    }
}

```

Dapatkan instance Producer melalui container, dan Anda dapat mengirim pesan.
Contoh berikut yang menggunakan Application Context secara langsung untuk
mendapatkan Producer bukanlah praktik yang ideal. Untuk penggunaan container
yang lebih spesifik, silakan merujuk ke modul di.

```php
<?php
use Hyperf\Amqp\Producer;
use App\Amqp\Producers\DemoProducer;
use Hyperf\Context\ApplicationContext;

$message = new DemoProducer(1);
$producer = ApplicationContext::getContainer()->get(Producer::class);
$result = $producer->produce($message);

```

## Mengonsumsi Pesan

Gunakan perintah generator untuk membuat consumer.
```bash
php bin/hyperf.php gen:amqp-consumer DemoConsumer
```

Kita dapat memodifikasi anotasi Consumer untuk mengganti exchange, routingKey,
dan queue. Dan `$data` adalah metadata yang telah di-parse.

```php
<?php

declare(strict_types=1);

namespace App\Amqp\Consumers;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;

#[Consumer(exchange: 'hyperf', routingKey: 'hyperf', queue: 'hyperf', nums: 1)]
class DemoConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        print_r($data);
        return Result::ACK;
    }
}
```

Framework akan secara otomatis membuat proses sesuai dengan anotasi Consumer, dan
proses tersebut akan dijalankan kembali secara otomatis jika keluar secara tidak
terduga (unexpected exit).

### Mengatur Konsumsi Konkuren (Concurrency)

Ada tiga parameter yang memengaruhi tingkat konsumsi:

- Anda dapat memodifikasi atribut `nums` pada anotasi `#[Consumer]` untuk
  membuka beberapa consumer secara bersamaan.
- Base class `ConsumerMessage` memiliki atribut `$qos` yang mengontrol jumlah
  pesan yang diambil dari server sekaligus dengan melakukan override pada
  `prefetch_size` atau `prefetch_count` di dalam `$qos`.
- `concurrent.limit` di dalam file konfigurasi, yang mengontrol jumlah
  maksimum coroutine consumer.

### Hasil Konsumsi

Framework akan menentukan perilaku respons pesan berdasarkan hasil yang
dikembalikan oleh metode `consume` di dalam `Consumer`. Ada 4 hasil respons,
yaitu `\Hyperf\Amqp\Result::ACK`, `\Hyperf\Amqp\Result::NACK`,
`\Hyperf\Amqp\Result::REQUEUE`, dan `\Hyperf\Amqp\Result::DROP`. Masing-masing
nilai pengembalian mewakili perilaku berikut:

| Nilai Pengembalian           | Perilaku                                                                 |
|------------------------------|----------------------------------------------------------------------|
| \Hyperf\Amqp\Result::ACK     | Mengonfirmasi bahwa pesan telah dikonsumsi dengan benar                                               |
| \Hyperf\Amqp\Result::NACK    | Pesan tidak dikonsumsi dengan benar, merespons menggunakan metode `basic_nack`                     |
| \Hyperf\Amqp\Result::REQUEUE | Pesan tidak dikonsumsi dengan benar, merespons menggunakan metode `basic_reject` dan memasukkan kembali pesan ke antrean (requeue) |
| \Hyperf\Amqp\Result::DROP    | Pesan tidak dikonsumsi dengan benar, merespons menggunakan metode `basic_reject`                   |

### Menyesuaikan Jumlah Proses Consumer Berdasarkan Environment

Dalam anotasi `#[Consumer]`, Anda dapat mengatur jumlah proses consumer melalui
atribut `nums`. Jika Anda perlu menetapkan jumlah proses consumer yang berbeda
sesuai dengan environment yang berbeda, Anda dapat meng-override metode
`getNums`. Contohnya adalah sebagai berikut:

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



## Delay Queue

Delay queue pada AMQP tidak diurutkan berdasarkan waktu tunda (delay time).
Oleh karena itu, jika Anda mengirimkan tugas dengan delay 10 detik lalu
mengirimkan tugas dengan delay 5 detik ke queue ini, tugas dengan delay 10 detik
akan tetap berada di urutan pertama. Setelah tugas pertama yang berdurasi 10
detik selesai, barulah tugas kedua yang berdurasi 5 detik akan dikonsumsi.
Oleh karena itu, Anda perlu membuat queue yang berbeda berdasarkan waktu. Jika
Anda menginginkan delay queue yang lebih fleksibel, Anda dapat mencoba
menggunakan asynchronous queue (async-queue) bersama dengan AMQP.

Selain itu, Anda perlu mengunduh [plugin delay](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases)
dan mengaktifkannya agar AMQP dapat digunakan secara normal.

```shell
wget https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases/download/3.9.0/rabbitmq_delayed_message_exchange-3.9.0.ez
cp rabbitmq_delayed_message_exchange-3.9.0.ez /opt/rabbitmq/plugins/
rabbitmq-plugins enable rabbitmq_delayed_message_exchange
```

### Producer

Buatlah `producer` menggunakan perintah `gen:amqp-producer`. Berikut adalah
contoh untuk tipe `direct`. Untuk tipe lainnya seperti `fanout` dan `topic`,
cukup ubah properti `type` pada producer dan consumer.

```bash
php bin/hyperf.php gen:amqp-producer DelayDirectProducer
```

Di dalam file DelayDirectProducer, tambahkan `use ProducerDelayedMessageTrait;`,
contohnya adalah sebagai berikut:

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

    protected $exchange = 'ext.hyperf.delay';

    protected $type = Type::DIRECT;

    protected $routingKey = '';

    public function __construct($data)
    {
        $this->payload = $data;
    }
}
```
### Consumer

Buatlah `consumer` menggunakan perintah `gen:amqp-consumer`.

```bash
php bin/hyperf.php gen:amqp-consumer DelayDirectConsumer
```

Di dalam file `DelayDirectConsumer`, tambahkan dan gunakan
`use ProducerDelayedMessageTrait, ConsumerDelayedMessageTrait;`, contohnya
adalah sebagai berikut:

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

    protected $exchange = 'ext.hyperf.delay';
    
    protected $queue = 'queue.hyperf.delay';
    
    protected $type = Type::DIRECT; //Type::FANOUT;
    
    protected $routingKey = '';

    public function consumeMessage($data, AMQPMessage $message): Result
    {
        var_dump($data, 'delay+direct consumeTime:' . (microtime(true)));
        return Result::ACK;
    }
}

```

### Mengirim Pesan Delay

> Berikut adalah demonstrasi cara menggunakannya di dalam Command. Silakan
> sesuaikan dengan kebutuhan penggunaan yang sebenarnya.

Buatlah `DelayCommand` menggunakan perintah `gen:command DelayCommand` seperti
berikut:

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
Jalankan perintah berikut pada command line untuk mengirim pesan:
```
php bin/hyperf.php demo:command
```


## RPC (Remote Procedure Call)

Selain skenario message queue yang umum, kita juga dapat mengimplementasikan
RPC (remote procedure call) melalui AMQP. Komponen ini juga menyediakan
dukungan yang sesuai untuk implementasi tersebut.

### Membuat Consumer

Consumer yang digunakan oleh RPC pada dasarnya sama dengan implementasi
consumer pada skenario message queue biasa. Perbedaannya hanya terletak pada
kebutuhan untuk mengembalikan data ke producer dengan memanggil metode `reply`.

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

### Melakukan Panggilan RPC

Memulai panggilan RPC juga sangat mudah. Anda hanya perlu mengambil objek
`Hyperf\Amqp\RpcClient` melalui dependency injection container dan memanggil
metode `call` di dalamnya. Hasil yang dikembalikan adalah data balasan dari
consumer. Contohnya sebagai berikut:

```php
<?php
use Hyperf\Amqp\Message\DynamicRpcMessage;
use Hyperf\Amqp\RpcClient;
use Hyperf\Context\ApplicationContext;

$rpcClient = ApplicationContext::getContainer()->get(RpcClient::class);
//Set Exchange and RoutingKey consistent with Consumer on DynamicRpcMessage
$result = $rpcClient->call(new DynamicRpcMessage('hyperf', 'hyperf', ['message' => 'Hello Hyperf'])); 

// $result:
// array(1) {
//     ["message"]=>
//     string(18) "Reply:Hello Hyperf"
// }
```

### Abstraksi RpcMessage

Proses panggilan RPC di atas secara langsung mendefinisikan Exchange dan
RoutingKey melalui kelas `Hyperf\Amqp\Message\DynamicRpcMessage` dan mengirimkan
data pesan. Dalam perancangan proyek production, kita dapat membuat abstraksi
pada RpcMessage untuk menyatukan definisi Exchange dan RoutingKey.

Kita dapat membuat kelas RpcMessage yang sesuai seperti `App\Amqp\FooRpcMessage`
sebagai berikut:

```php
<?php
use Hyperf\Amqp\Message\RpcMessage;

class FooRpcMessage extends RpcMessage
{

    protected $exchange = 'hyperf';

    protected $routingKey = 'hyperf';
    
    public function __construct($data)
    {
        //To pass data
        $this->payload = $data;
    }

}
```

Dengan cara ini, saat melakukan panggilan RPC, kita hanya perlu mengirimkan
instance `FooRpcMessage` secara langsung ke metode `call` tanpa harus
mendefinisikan Exchange dan RoutingKey setiap kali panggilan dilakukan.
