# Task

Pada tahap ini, `Swoole` tidak memiliki cara untuk melakukan `hook` terhadap
semua fungsi pemblokir (blocking functions), yang berarti beberapa fungsi
masih akan menyebabkan `process blocking` yang akan memengaruhi penjadwalan
coroutine. Pada saat ini, kita dapat mensimulasikan coroutine dengan
menggunakan komponen `Task`. Untuk mencapai tujuan memanggil fungsi pemblokir
tanpa memblokir proses, pada dasarnya ini masih berupa proses multi-process
yang menjalankan fungsi pemblokir, sehingga kinerjanya jelas akan lebih rendah
daripada coroutine asli (native coroutine), tergantung pada jumlah
`Task Worker`.

## Instalasi

```bash
composer require hyperf/task
```

## Konfigurasi

Karena Task bukan merupakan komponen bawaan (default), Anda perlu
menambahkan konfigurasi terkait `Task` ke `server.php` saat menggunakannya.

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;

return [
    // Item konfigurasi tidak relevan lainnya diabaikan di sini
    'settings' => [
        // Jumlah Task Worker, konfigurasikan jumlah yang sesuai berdasarkan konfigurasi server Anda
        'task_worker_num' => 8,
        // Karena `Task` terutama menangani metode yang tidak dapat dijadikan coroutine, disarankan untuk mengatur `false` di sini guna menghindari kebingungan data di bawah coroutine
        'task_enable_coroutine' => false,
    ],
    'callbacks' => [
        // Callback Task
        Event::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        Event::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'],
    ],
];
```

## Penggunaan

Komponen Task menyediakan dua metode penggunaan: `active method delivery`
dan `annotation delivery`.

### Active method delivery

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
            // Mengembalikan -1 ketika task_enable_coroutine bernilai false, jika tidak akan mengembalikan ID coroutine yang sesuai
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$exec = $container->get(TaskExecutor::class);
$result = $exec->execute(new Task([MethodTask::class, 'handle'], [Coroutine::id()]));
```

### Menggunakan anotasi

Menggunakan `active method delivery` tidak terlalu intuitif. Di sini kita
mengimplementasikan anotasi `#[Task]` yang sesuai dan menulis ulang panggilan
metode melalui `AOP`. Saat berada di dalam proses `Worker`, metode tersebut
secara otomatis dikirimkan (delivered) ke proses `Task`, dan coroutine akan
menunggu data dikembalikan.

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
            // Mengembalikan -1 ketika task_enable_coroutine=false, jika tidak akan mengembalikan ID coroutine yang sesuai
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$task = $container->get(AnnotationTask::class);
$result = $task->handle(Coroutine::id());
```

> `use Hyperf\Task\Annotation\Task;` diperlukan saat menggunakan anotasi `#[Task]`

Anotasi ini mendukung parameter berikut

| Konfigurasi | Tipe | Default | Keterangan |
| :------: | :---: | :----: | :------------------------------------------------------------: |
| timeout | int | 10 | Batas waktu (timeout) eksekusi Task |
| workerId | int | -1 | Menentukan ID proses tugas yang akan dikirim (-1 berarti pengiriman acak ke proses yang menganggur/idle) |

## Lampiran

Swoole belum memiliki daftar fungsi coroutine untuk saat ini:

- mysql, lapisan bawah menggunakan libmysqlclient, yang tidak disarankan, disarankan untuk menggunakan pdo_mysql/mysqli yang sudah mengimplementasikan coroutine
- mongo, lapisan bawah menggunakan mongo-c-client
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

### MongoDB

> Karena `MongoDB` tidak dapat di-`hook`, kita dapat memanggilnya melalui `Task`. Berikut adalah pengantar singkat tentang cara memanggil `MongoDB` melalui anotasi.

Di bawah ini kita mengimplementasikan dua metode `insert` dan `query`. Perlu
dicatat bahwa metode `manager` tidak dapat menggunakan `Task`, karena `Task`
akan diproses di dalam `proses Task` yang sesuai, lalu mengembalikan data dari
`proses Task` ke `proses Worker`. Oleh karena itu, parameter input dan output
dari `metode Task` tidak boleh membawa `IO` apa pun, seperti mengembalikan
instansiasi `Redis` dan sebagainya.

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

Penggunaan adalah sebagai berikut:

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

## Opsi lain

Jika mekanisme Task tidak dapat memenuhi persyaratan kinerja, Anda dapat
mencoba proyek sumber terbuka (open source) lain di bawah organisasi Hyperf,
yaitu [GoTask](https://github.com/hyperf/gotask). GoTask memulai proses Go
sebagai sidecar proses utama Swoole melalui fungsi manajemen proses Swoole, dan
menggunakan komunikasi proses untuk mengirimkan tugas ke sidecar untuk diproses
dan menerima nilai pengembalian. Ini dapat dipahami sebagai versi Go dari
Swoole TaskWorker.
