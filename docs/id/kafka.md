# Kafka

`Kafka` adalah platform stream processing open source yang dikembangkan oleh
`Apache Software Foundation`, ditulis dalam `Scala` dan `Java`. Tujuan dari
proyek ini adalah untuk menyediakan platform terpadu dengan throughput tinggi
dan latency rendah untuk memproses data real-time. Persistence layer miliknya
pada dasarnya adalah "message queue publish/subscribe berskala besar yang
didasarkan pada arsitektur distributed transaction log".

Komponen [longlang/phpkafka](https://github.com/swoole/phpkafka) disediakan oleh
[Longzhiyan](http://longlang.org/) dan mendukung `PHP-FPM` dan `Swoole`. Terima
kasih kepada `Swoole Team` dan `ZenTao Team` atas kontribusi mereka kepada
komunitas.

## Instalasi

```bash
composer require hyperf/kafka
```

## Persyaratan Versi

- Kafka >= 1.0.0

## Penggunaan

### Konfigurasi

File konfigurasi untuk komponen `kafka` secara default berada di
`config/autoload/kafka.php`. Jika file tersebut tidak ada, Anda dapat
menggunakan perintah `php bin/hyperf.php vendor:publish hyperf/kafka` untuk
mempublikasikan file konfigurasi yang sesuai.

File konfigurasi default adalah sebagai berikut:

|         Konfigurasi         |    Tipe    |            Default            |                                                                                                Deskripsi                                                                                                |
|:---------------------------:| :--------: | :---------------------------: |:---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
|        connect_timeout        | int｜float |              -1               |                                                          Waktu timeout koneksi (satuan: detik, mendukung desimal), jika bernilai -1 berarti tidak ada batasan                                                           |
|         send_timeout          | int｜float |              -1               |                                                             Waktu timeout pengiriman (satuan: detik, mendukung desimal), jika bernilai -1 berarti tidak ada batasan                                                              |
|         recv_timeout          | int｜float |              -1               |                                                           Waktu timeout penerimaan (satuan: detik, mendukung desimal), jika bernilai -1 berarti tidak ada batasan                                                           |
|           client_id           |   string   |             null              |                                                                                              Client ID Kafka                                                                                              |
|      max_write_attempts       |    int     |               3               |                                                                                     Jumlah maksimum percobaan menulis                                                                                      |
|       bootstrap_servers       |   array    |       '127.0.0.1:9092'        |                                       Bootstrap server, jika nilai ini dikonfigurasi, ia akan terhubung secara otomatis ke server dan memperbarui broker secara otomatis                                        |
|             acks              |    int     |               0               | Producer meminta leader untuk mengonfirmasi nilai yang telah diterima sebelum permintaan konfirmasi selesai. Nilai yang diizinkan: 0 berarti tanpa konfirmasi, 1 berarti leader saja, -1 berarti ISR lengkap |
|          producer_id          |    int     |              -1               |                                                                                                Producer ID                                                                                                |
|        producer_epoch         |    int     |              -1               |                                                                                              Producer Epoch                                                                                               |
|    partition_leader_epoch     |    int     |              -1               |                                                                                          Partition Leader Epoch                                                                                           |
|           interval            | int｜float |               0               |                                        Berapa detik penundaan untuk mencoba lagi ketika pesan tidak diterima, defaultnya adalah 0, tanpa penundaan (satuan: detik, desimal)                                        |
|        session_timeout        | int｜float |              60               |                                Jika tidak ada sinyal heartbeat yang diterima setelah timeout, coordinator akan menganggap user telah mati. (Satuan: detik, mendukung desimal)                                 |
|       rebalance_timeout       | int｜float |              60               |                                   Waktu terlama coordinator menunggu setiap anggota untuk bergabung kembali saat melakukan rebalance group (satuan: detik, mendukung desimal).                                    |
|          replica_id           |    int     |              -1               |                                                                                                Replica ID                                                                                                 |
|            rack_id            |    int     |              -1               |                                                                                                Nomor Rack                                                                                                 |
|          group_retry          |    int     |               5               |                                                          Operasi group, jumlah percobaan ulang otomatis saat cocok dengan kode kesalahan yang ditentukan sebelumnya                                                          |
|       group_retry_sleep       |    int     |               1               |                                                                                 Penundaan percobaan ulang operasi group, satuan: detik                                                                                 |
|        group_heartbeat        |    int     |               3               |                                                                                  Interval heartbeat group, satuan: detik                                                                                   |
|         offset_retry          |    int     |               5               |                                                           Operasi offset, jumlah percobaan ulang otomatis saat cocok dengan kode kesalahan yang ditentukan sebelumnya                                                           |
|       auto_create_topic       |    bool    |             true              |                                                                                   Apakah akan membuat topic secara otomatis                                                                                   |
| partition_assignment_strategy |   string   | KafkaStrategy::RANGE_ASSIGNOR |                     Strategi alokasi partisi consumer, opsi: alokasi range (`KafkaStrategy::RANGE_ASSIGNOR`), alokasi polling (`KafkaStrategy::ROUND_ROBIN_ASSIGNOR`)                      |

```php
<?php

declare(strict_types=1);

use Hyperf\Kafka\Constants\KafkaStrategy;

return [
    'default' => [
        'connect_timeout' => -1,
        'send_timeout' => -1,
        'recv_timeout' => -1,
        'client_id' => '',
        'max_write_attempts' => 3,
        'bootstrap_servers' => '127.0.0.1:9092',
        'acks' => 0,
        'producer_id' => -1,
        'producer_epoch' => -1,
        'partition_leader_epoch' => -1,
        'interval' => 0,
        'session_timeout' => 60,
        'rebalance_timeout' => 60,
        'replica_id' => -1,
        'rack_id' => '',
        'group_retry' => 5,
        'group_retry_sleep' => 1,
        'group_heartbeat' => 3,
        'offset_retry' => 5,
        'auto_create_topic' => true,
        'partition_assignment_strategy' => KafkaStrategy::RANGE_ASSIGNOR,
        'sasl' => [],
        'ssl' => [],
    ],
];
```

### Membuat consumer

Perintah `gen:kafka-consumer` dapat menghasilkan consumer secara cepat untuk
mengonsumsi pesan.

```bash
php bin/hyperf.php gen:kafka-consumer KafkaConsumer
```

Anda juga dapat menggunakan anotasi `Hyperf\Kafka\Annotation\Consumer` untuk
mendeklarasikan subclass dari abstract class `Hyperf\Kafka\AbstractConsumer`
guna menyelesaikan definisi `Consumer`, di mana baik anotasi
`Hyperf\Kafka\Annotation\Consumer` maupun abstract class tersebut berisi
atribut berikut:

| Konfigurasi |        Tipe        |    Default    |                                           Deskripsi                                            |
| :---------: | :----------------: | :-----------: | :--------------------------------------------------------------------------------------------: |
|    topic    | string or string[] |      ''       |                                     topic yang akan dimonitor                                  |
|   groupId   |       string       |      ''       |                                    groupId yang akan dimonitor                                 |
|  memberId   |       string       |      ''       |                                   memberId yang akan dimonitor                                 |
| autoCommit  |       string       |      ''       |                                Apakah akan melakukan commit secara otomatis                    |
|    name     |       string       | KafkaConsumer |                                         Nama consumer                                          |
|    nums     |        int         |       1       |                                  Jumlah proses consumer                                        |
|    pool     |       string       |    default    | Koneksi yang sesuai dengan consumer, sesuai dengan key pada file konfigurasi                  |

```php
<?php

declare(strict_types=1);

namespace App\kafka;

use Hyperf\Kafka\AbstractConsumer;
use Hyperf\Kafka\Annotation\Consumer;
use longlang\phpkafka\Consumer\ConsumeMessage;

#[Consumer(topic: "hyperf", nums: 5, groupId: "hyperf", autoCommit: true)]
class KafkaConsumer extends AbstractConsumer
{
    public function consume(ConsumeMessage $message): string
    {
        var_dump($message->getTopic() . ':' . $message->getKey() . ':' . $message->getValue());
    }
}
```

### Memproduksi pesan

Anda dapat memanggil `Hyperf\Kafka\Producer::send(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null)` untuk mengirimkan pesan, berikut adalah contoh pengiriman pesan di dalam `Controller`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Kafka\Producer;

#[AutoController]
class IndexController extends AbstractController
{
    public function index(Producer $producer)
    {
        $producer->send('hyperf', 'value', 'key');
    }
}
```

Metode `Hyperf\Kafka\Producer::send()` akan menunggu ACK. Jika Anda tidak
perlu menunggu ACK, Anda dapat menggunakan metode
`Hyperf\Kafka\Producer::sendAsync()` untuk mengirimkan pesan.

### Mengirim beberapa pesan sekaligus

Metode `Hyperf\Kafka\Producer::sendBatch(array $messages)` digunakan untuk
mengirimkan pesan secara batch (massal) ke `kafka`, berikut adalah contoh
pengiriman pesan di dalam `Controller`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Kafka\Producer;
use longlang\phpkafka\Producer\ProduceMessage;

#[AutoController]
class IndexController extends AbstractController
{
    public function index(Producer $producer)
    {
        $producer->sendBatch([
            new ProduceMessage('hyperf1', 'hyperf1_value', 'hyperf1_key'),
            new ProduceMessage('hyperf2', 'hyperf2_value', 'hyperf2_key'),
            new ProduceMessage('hyperf3', 'hyperf3_value', 'hyperf3_key'),
        ]);

    }
}
```
