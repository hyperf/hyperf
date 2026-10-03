# Task

Saat ini, `Swoole` belum bisa me-`hook` semua fungsi blocking. Artinya, beberapa fungsi masih bisa menyebabkan `process blocking` dan mengganggu penjadwalan coroutine. Dalam kasus seperti ini, kita bisa menggunakan komponen `Task` untuk mensimulasikan pemrosesan coroutine, sehingga fungsi blocking tetap bisa dipanggil tanpa mem-block process. Pada dasarnya, fungsi blocking tetap berjalan di banyak process, jadi performanya jauh lebih rendah dibanding coroutine native, tergantung jumlah process `Task Worker`.

## Instalasi

```bash
composer require hyperf/task
```

## Konfigurasi

Karena `Task` bukan komponen default, Anda perlu menambahkan konfigurasi terkait `Task` ke `server.php` saat menggunakannya.

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;

return [
    // Item konfigurasi lain yang tidak terkait dihilangkan di sini
    'settings' => [
        // Jumlah Task Worker, konfigurasikan angka yang sesuai berdasarkan konfigurasi server Anda
        'task_worker_num' => 8,
        // Karena `Task` terutama menangani method yang tidak bisa di-coroutine-kan, disarankan untuk mengatur ini ke `false` untuk menghindari kekacauan data di bawah coroutine
        'task_enable_coroutine' => false,
    ],
    'callbacks' => [
        // Task callbacks
        Event::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        Event::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'],
    ],
];

```

## Penggunaan

Komponen Task menyediakan dua metode penggunaan: `pengiriman method aktif` dan `pengiriman annotation`.

### Pengiriman method aktif

```php
<?php

use Hyperf\Coroutine\Coroutine;
use Hyperf\Context\ApplicationContext;
use Hyperf\Task\TaskExecutor;
use Hyperf\Task\Task;

class MethodTask
{
    public function handle($cid)
    {
        return [
            'worker.cid' => $cid,
            // Mengembalikan -1 ketika task_enable_coroutine false, jika tidak mengembalikan ID coroutine yang sesuai
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$exec = $container->get(TaskExecutor::class);
$result = $exec->execute(new Task([MethodTask::class, 'handle'], [Coroutine::id()]));

```

### Menggunakan Annotation

`Pengiriman method aktif` kurang intuitif. Karena itu, kami mengimplementasikan annotation `#[Task]` dan menulis ulang pemanggilan method menggunakan `AOP`. Ketika berada di process `Worker`, task otomatis dikirim ke process `Task`, dan coroutine menunggu hasilnya.

```php
<?php

use Hyperf\Coroutine\Coroutine;
use Hyperf\Context\ApplicationContext;
use Hyperf\Task\Annotation\Task;

class AnnotationTask
{
    #[Task]
    public function handle($cid)
    {
        return [
            'worker.cid' => $cid,
            // Mengembalikan -1 ketika task_enable_coroutine=false, jika tidak mengembalikan ID coroutine yang sesuai
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$task = $container->get(AnnotationTask::class);
$result = $task->handle(Coroutine::id());
```

> Saat menggunakan annotation `#[Task]`, pastikan Anda `use Hyperf\Task\Annotation\Task;`

Annotation mendukung parameter berikut:

| Konfigurasi | Tipe  | Default | Deskripsi                                              |
| :---------: | :---: | :-----: | :----------------------------------------------------: |
| timeout     | int   | 10      | Timeout eksekusi task                                  |
| workerId    | int   | -1      | Menentukan ID Task process untuk pengiriman (-1 berarti dikirim secara acak ke process yang idle) |

## Lampiran

Daftar fungsi yang belum di-coroutine-kan oleh Swoole:

- mysql: menggunakan libmysqlclient secara internal, tidak direkomendasikan; disarankan menggunakan pdo_mysql/mysqli yang sudah di-coroutine-kan.
- mongo: menggunakan mongo-c-client secara internal.
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

### MongoDB

> Karena `MongoDB` tidak bisa `di-hook`, kita dapat menggunakan `Task` untuk memanggilnya. Berikut adalah pengenalan singkat tentang cara memanggil `MongoDB` menggunakan metode annotation.

Di sini kita implementasikan dua method, `insert` dan `query`. Perhatikan bahwa method `manager` tidak bisa menggunakan `Task`, karena `Task` diproses di `Task process` lalu datanya dikembalikan ke `Worker process`. Karena itu, sebaiknya tidak membawa `IO` apa pun di parameter input/output `Task method`, misalnya mengembalikan objek `Redis` yang sudah diinstansiasi.

```php
<?php

declare(strict_types=1);

namespace App\Task;

use Hyperf\Task\Annotation\Task;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

class MongoTask
{
    public Manager $manager;

    #[Task]
    public function insert(string $namespace, array $document)
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
        $bulk = new BulkWrite();
        $bulk->insert($document);

        $result = $this->manager()->executeBulkWrite($namespace, $bulk, $writeConcern);
        return $result->getUpsertedCount();
    }

    #[Task]
    public function query(string $namespace, array $filter = [], array $options = [])
    {
        $query = new Query($filter, $options);
        $cursor = $this->manager()->executeQuery($namespace, $query);
        return $cursor->toArray();
    }

    protected function manager()
    {
        if ($this->manager instanceof Manager) {
            return $this->manager;
        }
        $uri = 'mongodb://127.0.0.1:27017';
        return $this->manager = new Manager($uri, []);
    }
}

```

Penggunaannya sebagai berikut:

```php
<?php
use App\Task\MongoTask;
use Hyperf\Context\ApplicationContext;

$client = ApplicationContext::getContainer()->get(MongoTask::class);
$client->insert('hyperf.test', ['id' => rand(0, 99999999)]);

$result = $client->query('hyperf.test', [], [
    'sort' => ['id' => -1],
    'limit' => 5,
]);
```

## Skema Lain

Jika mekanisme Task tidak dapat memenuhi kebutuhan performa, Anda dapat mencoba proyek open-source lain di bawah organisasi Hyperf: [GoTask](https://github.com/hyperf/gotask). GoTask menggunakan fungsi manajemen process Swoole untuk memulai process Go sebagai Sidecar ke process utama Swoole, dan menggunakan komunikasi antar-process untuk mengirimkan task ke sidecar untuk diproses dan menerima nilai kembali. Ini dapat dipahami sebagai TaskWorker versi Go dari Swoole.
