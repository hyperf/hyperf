# Kafka

`Kafka` adalah platform stream processing open-source yang dikembangkan oleh `Apache Software Foundation`, ditulis dalam `Scala` dan `Java`. Tujuan proyek ini adalah menyediakan platform terpadu, throughput tinggi, dan latensi rendah untuk memproses data real-time. Lapisan persistensinya pada dasarnya adalah "message queue publish/subscribe skala besar berdasarkan arsitektur distributed transaction log".

Komponen [longlang/phpkafka](https://github.com/swoole/phpkafka) disediakan oleh [Longzhiyan](http://longlang.org/), mendukung `PHP-FPM` dan `Swoole`. Terima kasih kepada `Swoole team` dan `ZenTao team` atas kontribusi mereka kepada komunitas.

## Instalasi

```bash
composer require hyperf/kafka
```

## Persyaratan Versi

- Kafka >= 1.0.0

## Penggunaan

### Konfigurasi

File konfigurasi untuk komponen `kafka` berada di `config/autoload/kafka.php` secara default. Jika file ini belum ada, Anda dapat menerbitkannya menggunakan perintah `php bin/hyperf.php vendor:publish hyperf/kafka`.

File konfigurasi default adalah sebagai berikut:

| Konfigurasi | Tipe | Nilai Default | Keterangan |
| :--- | :--- | :--- | :--- |
| connect_timeout | int\|float | -1 | Timeout koneksi (satuan: detik, mendukung desimal), -1 berarti tidak ada batas |
| send_timeout | int\|float | -1 | Timeout pengiriman (satuan: detik, mendukung desimal), -1 berarti tidak ada batas |
| recv_timeout | int\|float | -1 | Timeout penerimaan (satuan: detik, mendukung desimal), -1 berarti tidak ada batas |
| client_id | string | null | Identifier klien Kafka |
| max_write_attempts | int | 3 | Maksimum percobaan penulisan |
| bootstrap_servers | array | '127.0.0.1:9092' | Bootstrap servers. Jika nilai ini dikonfigurasi, akan otomatis terhubung ke server ini dan memperbarui broker secara otomatis |
| acks | int | 0 | Jumlah acknowledgment yang diperlukan producer dari leader sebelum menganggap request selesai. Nilai yang diizinkan: 0 untuk tanpa acknowledgment, 1 untuk leader saja, -1 untuk full ISR |
| producer_id | int | -1 | ID Producer |
| producer_epoch | int | -1 | Producer Epoch |
| partition_leader_epoch | int | -1 | Partition Leader Epoch |
| interval | int\|float | 0 | Tunda dalam detik untuk mencoba ulang ketika tidak ada pesan yang diperoleh, default 0 untuk tanpa tunda (satuan: detik, mendukung desimal) |
| session_timeout | int\|float | 60 | Jika tidak ada heartbeat yang diterima setelah timeout, coordinator akan menganggap pengguna mati. (satuan: detik, mendukung desimal) |
| rebalance_timeout | int\|float | 60 | Waktu maksimum coordinator menunggu setiap anggota untuk bergabung kembali ketika melakukan rebalance grup (satuan: detik, mendukung desimal) |
| replica_id | int | -1 | Replica ID |
| rack_id | int | -1 | Rack ID |
| group_retry | int | 5 | Jumlah retry otomatis untuk operasi grup ketika cocok dengan preset error codes |
| group_retry_sleep | int | 1 | Tunda untuk retry operasi grup, satuan: detik |
| group_heartbeat | int | 3 | Interval heartbeat untuk grup, satuan: detik |
| offset_retry | int | 5 | Jumlah retry otomatis untuk operasi offset ketika cocok dengan preset error codes |
| auto_create_topic | bool | true | Apakah akan membuat topic secara otomatis |
| partition_assignment_strategy | string | KafkaStrategy::RANGE_ASSIGNOR | Strategi penugasan partisi consumer, opsi: Range assignment (`KafkaStrategy::RANGE_ASSIGNOR`), Round Robin assignment (`KafkaStrategy::ROUND_ROBIN_ASSIGNOR`) |
| sasl | array | [] | Informasi autentikasi SASL. Jika kosong, tidak ada informasi autentikasi yang dikirim. Versi phpkafka harus >= 1.2 |
| ssl | array | [] | Informasi terkait koneksi SSL. Jika kosong, SSL tidak digunakan. Versi phpkafka harus >= 1.2 |

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

### Membuat Consumer

Anda dapat dengan cepat membuat Consumer untuk mengonsumsi pesan menggunakan perintah `gen:kafka-consumer`.

```bash
php bin/hyperf.php gen:kafka-consumer KafkaConsumer
```

Anda juga dapat menggunakan annotation `Hyperf\Kafka\Annotation\Consumer` untuk mendeklarasikan subclass dari class abstrak `Hyperf/Kafka/AbstractConsumer` guna mendefinisikan `Consumer`. Baik annotation `Hyperf\Kafka\Annotation\Consumer` maupun class abstrak memiliki properti berikut:

| Konfigurasi | Tipe | Nilai Default Annotation atau Class Abstrak | Keterangan |
| :--- | :--- | :--- | :--- |
| topic | string or string[] | '' | Topic yang akan didengarkan |
| groupId | string | '' | GroupId yang akan didengarkan |
| memberId | string | '' | MemberId yang akan didengarkan |
| autoCommit | string | '' | Apakah auto-commit diperlukan |
| name | string | KafkaConsumer | Nama consumer |
| nums | int | 1 | Jumlah proses consumer |
| pool | string | default | Koneksi yang digunakan oleh consumer, sesuai dengan key di file konfigurasi |

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

### Producing Messages

Kirim pesan ke `kafka` dengan memanggil method `Hyperf\Kafka\Producer::send(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null)`. Berikut contoh produksi pesan di `Controller`:

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

Method `Hyperf\Kafka\Producer::send()` menunggu ACK. Jika Anda tidak perlu menunggu ACK, Anda dapat menggunakan method `Hyperf\Kafka\Producer::sendAsync()` untuk mengirim pesan.

### Mengirim Banyak Pesan Sekaligus

Gunakan method `Hyperf\Kafka\Producer::sendBatch(array $messages)` untuk mengirim pesan ke `kafka` secara batch. Berikut adalah contoh pengiriman pesan di `Controller`:

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

### Petunjuk Konfigurasi SASL

| Nama Parameter | Deskripsi | Nilai Default |
| :--- | :--- | :--- |
| type | Class untuk otorisasi SASL. PLAIN menggunakan `\longlang\phpkafka\Sasl\PlainSasl::class` | '' |
| username | Nama pengguna | '' |
| password | Kata sandi | '' |

### Petunjuk Konfigurasi SSL

| Nama Parameter | Deskripsi | Nilai Default |
| :--- | :--- | :--- |
| open | Apakah akan mengaktifkan enkripsi transport SSL | `false` |
| compression | Apakah akan mengaktifkan kompresi | `true` |
| certFile | Path ke sertifikat cert | `''` |
| keyFile | Path ke private key | `''` |
| passphrase | Kata sandi sertifikat cert | `''` |
| peerName | Nama host server. Defaultnya adalah host yang terhubung | `''` |
| verifyPeer | Apakah akan memverifikasi sertifikat remote | `false` |
| verifyPeerName | Apakah akan memverifikasi nama server remote | `false` |
| verifyDepth | Jika hierarki rantai sertifikat terlalu dalam dan melebihi nilai yang ditetapkan oleh opsi ini, verifikasi akan dihentikan. Hierarki tidak diverifikasi secara default | `0` |
| allowSelfSigned | Apakah mengizinkan sertifikat self-signed | `false` |
| cafile | Path sertifikat CA | `''` |
| capath | Direktori sertifikat CA. Semua file pem di path ini akan dipindai secara otomatis | `''` |
