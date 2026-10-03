# Scheduled Tasks (Crontab)

Umumnya, scheduled task dijalankan menggunakan perintah `crontab` Linux. Namun, tidak semua developer punya akses ke server produksi untuk mengaturnya. Komponen [hyperf/crontab](https://github.com/hyperf/crontab) menyediakan fungsionalitas scheduled task `tingkat-detik` yang bisa didefinisikan dengan konfigurasi sederhana.

# Instalasi

```bash
composer require hyperf/crontab
```

# Penggunaan

## Memulai process task scheduler

Sebelum menggunakan komponen scheduled task, Anda perlu mendaftarkan custom process `Hyperf\Crontab\Process\CrontabDispatcherProcess` di `config/autoload/processes.php`:

```php
<?php
// config/autoload/processes.php
return [
    Hyperf\Crontab\Process\CrontabDispatcherProcess::class,
];
```

Ketika service dimulai, ini akan meluncurkan custom process yang digunakan untuk mengurai, menjadwalkan, dan mendistribusikan scheduled task. Selain itu, Anda perlu mengatur konfigurasi `enable` di `config/autoload/crontab.php` menjadi `true` untuk mengaktifkan fungsionalitas scheduled task. Jika file ini belum ada, Anda dapat membuatnya sendiri:

```php
<?php
return [
    // Apakah akan mengaktifkan scheduled task
    'enable' => true,
];
```

## Mendefinisikan scheduled task

### Definisi melalui file konfigurasi

Anda dapat mengonfigurasi semua scheduled task di file konfigurasi `config/autoload/crontab.php`. File tersebut harus mengembalikan array dari objek `Hyperf\Crontab\Crontab`. Jika file ini belum ada, Anda dapat membuatnya sendiri:

```php
<?php
// config/autoload/crontab.php
use Hyperf\Crontab\Crontab;
return [
    'enable' => true,
    // Scheduled task yang didefinisikan melalui file konfigurasi
    'crontab' => [
        // Callback type scheduled task (default)
        (new Crontab())->setName('Foo')->setRule('* * * * *')->setCallback([App\Task\FooTask::class, 'execute'])->setMemo('Ini adalah contoh scheduled task'),
        // Command type scheduled task
        (new Crontab())->setType('command')->setName('Bar')->setRule('* * * * *')->setCallback([
            'command' => 'swiftmailer:spool:send',
            // (opsional) arguments
            'fooArgument' => 'barValue',
            // (opsional) options
            '--message-limit' => 1,
            // Ingat untuk menyertakan ini, jika tidak process utama mungkin akan keluar
            '--disable-event-dispatcher' => true,
        ])->setEnvironments(['develop', 'production']),
        // Closure type scheduled task (hanya didukung di Coroutine style server)
        (new Crontab())->setType('closure')->setName('Closure')->setRule('* * * * *')->setCallback(function () {
            var_dump(date('Y-m-d H:i:s'));
        })->setEnvironments('production'),
    ],
];
```

Sejak versi 3.1, metode konfigurasi baru telah ditambahkan. Anda dapat mendefinisikan scheduled task melalui `config/crontabs.php`. Jika file ini belum ada, Anda dapat membuatnya sendiri:

```php
<?php
// config/crontabs.php
use Hyperf\Crontab\Schedule;

Schedule::command('foo:bar')->setName('foo-bar')->setRule('* * * * *');
Schedule::call([Foo::class, 'bar'])->setName('foo-bar')->setRule('* * * * *');
Schedule::call(fn() => (new Foo)->bar())->setName('foo-bar')->setRule('* * * * *');
```

### Definisi melalui Annotation

Anda dapat dengan cepat mendefinisikan task menggunakan annotation `#[Crontab]`. Contoh definisi berikut mencapai tujuan yang sama dengan definisi file konfigurasi. Ini mendefinisikan scheduled task bernama `Foo` yang menjalankan `App\Task\FooTask::execute()` setiap menit.

```php
<?php
namespace App\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

#[Crontab(name: "Foo", rule: "* * * * *", callback: "execute", memo: "Ini adalah contoh scheduled task")]
class FooTask
{
    #[Inject]
    private StdoutLoggerInterface $logger;

    public function execute()
    {
        $this->logger->info(date('Y-m-d H:i:s', time()));
    }

    #[Crontab(rule: "* * * * *", memo: "foo")]
    public function foo()
    {
        var_dump('foo');
    }
}
```

### Atribut Task

#### name
Nama dari scheduled task, dapat berupa string apa pun. Nama semua scheduled task harus unik.

#### rule
Aturan eksekusi scheduled task. Jika didefinisikan di level menit, aturannya sama seperti perintah `crontab` Linux. Jika di level detik, panjang aturan berubah dari 5 jadi 6 digit, dengan menambahkan node detik di awal aturan. Jadi, aturan 5 digit dieksekusi per menit, dan aturan 6 digit dieksekusi per detik, misalnya `*/5 * * * * *` berarti setiap 5 detik. Perhatikan, jika mendefinisikan lewat annotation, Anda perlu meng-escape simbol `\` dalam aturan, yaitu tulis `*\/5 * * * * *`.

#### callback
Callback yang akan dijalankan oleh scheduled task. Jika didefinisikan lewat file konfigurasi, Anda perlu memberikan array `[$class, $method]`, di mana `$class` adalah nama kelas lengkap (FQCN), dan `$method` adalah method `public` di kelas tersebut. Jika lewat annotation, Anda cukup memberikan nama method `public` di kelas yang sama. Jika kelas hanya punya satu method `public`, atribut ini bisa dihilangkan.

#### singleton
Mencegah eksekusi task secara bersamaan; maksimal satu instance task berjalan dalam satu waktu. Namun, ini tidak menjamin task tidak akan dijalankan berulang di cluster.

#### onOneServer
Ketika proyek di-deploy dalam beberapa instance, hanya satu instance yang akan terpicu.

#### mutexPool
Connection pool `Redis` yang digunakan untuk mutex lock.

#### mutexExpires
Timeout untuk mutex lock. Jika scheduled task selesai dieksekusi tetapi gagal melepaskan mutex lock, lock akan otomatis dilepaskan setelah waktu ini.

#### memo
Catatan untuk scheduled task. Atribut ini opsional dan tidak memiliki signifikansi logis; hanya untuk dibaca oleh developer untuk membantu memahami task.

#### enable
Apakah task saat ini diaktifkan.

> Selain boolean, tipe string dan array juga didukung.

Jika `enable` berupa `string`, method dengan nama tersebut di kelas yang sama akan dipanggil untuk menentukan apakah scheduled task ini berjalan:

```php
<?php

namespace App\Crontab;

use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;

#[Crontab(name: "Echo", rule: "* * * * *", callback: "execute", enable: "isEnable", memo: "Ini adalah contoh scheduled task")]
class EchoCrontab
{
    public function execute()
    {
        var_dump(Carbon::now()->toDateTimeString());
    }

    public function isEnable(): bool
    {
        return true;
    }
}
```

Jika `enable` berupa `array`, method `array[1]` dari kelas `array[0]` akan dipanggil untuk menentukan apakah scheduled task ini berjalan:

```php
<?php

namespace App\Crontab;

class EnableChecker
{
    public function isEnable(): bool
    {
        return false;
    }
}
```

```php
<?php

namespace App\Crontab;

use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;

#[Crontab(name: "Echo", rule: "* * * * *", callback: "execute", enable: [EnableChecker::class, "isEnable"], memo: "Ini adalah contoh scheduled task")]
class EchoCrontab
{
    public function execute()
    {
        var_dump(Carbon::now()->toDateTimeString());
    }

    public function isEnable(): bool
    {
        return true;
    }
}

```

#### environments
Mengatur environment untuk scheduled task. Jika tidak diatur, akan efektif di semua environment. Mendukung input array dan string.

### Strategi Penjadwalan dan Distribusi
Scheduled task dirancang agar bisa dijadwalkan dan didistribusikan dengan berbagai strategi. Saat ini tersedia `Multi-process Execution Strategy` dan `Coroutine Execution Strategy`. Defaultnya adalah `Multi-process Execution Strategy`, dan strategi yang lebih powerful akan menyusul di versi mendatang.

> Saat menggunakan Coroutine style server, gunakan Coroutine Execution Strategy.

#### Mengubah Strategi Penjadwalan dan Distribusi
Anda dapat mengubah strategi yang saat ini digunakan dengan mengubah instance yang sesuai dengan kelas interface `Hyperf\Crontab\Strategy\StrategyInterface` di `config/autoload/dependencies.php`. Secara default, `Worker Process Execution Strategy` digunakan, sesuai dengan kelas `Hyperf\Crontab\Strategy\WorkerStrategy`. Jika kita ingin mengubah strategi ke yang baru, misalnya `App\Crontab\Strategy\FooStrategy`, Anda dapat melakukan hal berikut:

```php
<?php
return [
    \Hyperf\Crontab\Strategy\StrategyInterface::class => \App\Crontab\Strategy\FooStrategy::class,
];
```

##### Worker Process Execution Strategy [Default]
Strategy Class: `Hyperf\Crontab\Strategy\WorkerStrategy`

Strategi ini digunakan secara default. Process `CrontabDispatcherProcess` mengurai scheduled task dan mem-polling-nya untuk mengirimkan task ke setiap process `Worker` untuk dieksekusi melalui komunikasi antar-process. Setiap process `Worker` kemudian mengeksekusi task sebagai coroutine.

##### TaskWorker Process Execution Strategy
Strategy Class: `Hyperf\Crontab\Strategy\TaskWorkerStrategy`

Dalam strategi ini, process `CrontabDispatcherProcess` mengurai scheduled task dan mem-polling-nya untuk mengirimkan task ke setiap process `TaskWorker` untuk dieksekusi melalui komunikasi antar-process. Setiap process `TaskWorker` kemudian mengeksekusi task sebagai coroutine. Perhatikan apakah process `TaskWorker` dikonfigurasi untuk mendukung coroutine saat menggunakan strategi ini.

##### Multi-process Execution Strategy
Strategy Class: `Hyperf\Crontab\Strategy\ProcessStrategy`

Dalam strategi ini, process `CrontabDispatcherProcess` mengurai scheduled task dan mem-polling-nya untuk mengirimkan task ke setiap process `Worker` dan process `TaskWorker` untuk dieksekusi melalui komunikasi antar-process. Setiap process kemudian mengeksekusi task sebagai coroutine. Perhatikan apakah process `TaskWorker` dikonfigurasi untuk mendukung coroutine saat menggunakan strategi ini.

##### Coroutine Execution Strategy
Strategy Class: `Hyperf\Crontab\Strategy\CoroutineStrategy`

Dalam strategi ini, process `CrontabDispatcherProcess` mengurai scheduled task dan membuat coroutine di dalam process untuk mengeksekusi setiap task.

## Menjalankan Scheduled Task

Setelah Anda menyelesaikan konfigurasi di atas dan mendefinisikan scheduled task, Anda hanya perlu memulai `Server`, dan scheduled task akan mulai berjalan secara bersamaan.

Setelah dimulai, meskipun Anda sudah mendefinisikan scheduled task dengan siklus pendek, task tidak langsung dijalankan. Semua scheduled task akan menunggu hingga siklus menit berikutnya. Misalnya, jika mulai pada `10:11:12`, task akan mulai dijalankan pada `10:12:00`.

### FailToExecute Event
Ketika scheduled task gagal dieksekusi, event `FailToExecute` akan terpicu. Kita dapat menulis listener berikut untuk menerima `Crontab` dan `Throwable` yang sesuai.

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Crontab\Event\FailToExecute;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

#[Listener]
class FailToExecuteCrontabListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            FailToExecute::class,
        ];
    }

    /**
     * @param FailToExecute $event
     */
    public function process(object $event)
    {
        var_dump($event->crontab->getName());
        var_dump($event->throwable->getMessage());
    }
}
```
