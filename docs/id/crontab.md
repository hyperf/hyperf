# Task Scheduling

Dalam kebanyakan kasus, eksekusi scheduled task dapat dilakukan melalui
perintah `crontab` pada Linux. Namun, dalam beberapa kasus, melakukan
konfigurasi crontab di lingkungan produksi (production environment) bisa
menyulitkan dan memiliki keterbatasan karena minimal hanya mendukung penjadwalan
tingkat menit (`minute level`).

Komponen [hyperf/crontab](https://github.com/hyperf/crontab) menyediakan
penjadwalan task tingkat detik (`second level`) dan mempermudah pendefinisian
task.

# Instalasi

```bash
composer require hyperf/crontab
```

# Penggunaan

## Menjalankan proses scheduler

Sebelum menggunakan komponen timed task, Anda perlu mendaftarkan
`Hyperf\Crontab\Process\CrontabDispatcherProcess` di
`config/autoload/processes.php`, seperti berikut:

```php
<?php
// config/autoload/processes.php
return [
    Hyperf\Crontab\Process\CrontabDispatcherProcess::class,
];
```

Dengan cara ini, saat service berjalan, sebuah custom process akan dijalankan
untuk menganalisis dan menjadwalkan task. Di saat yang sama, Anda juga perlu
mengatur pengaturan `enable` pada `config/autoload/crontab.php` menjadi `true`,
yang akan mengaktifkan pemrosesan scheduler. Jika file konfigurasi tidak ada,
Anda dapat membuatnya sendiri. Konfigurasinya adalah sebagai berikut:

```php
<?php
return [
    // Whether to enable timed tasks
    'enable' => true,
];
```

## Mendefinisikan scheduled task

### Menggunakan file konfigurasi

Anda dapat mendefinisikan semua scheduled task Anda dalam file konfigurasi
`config/autoload/crontab.php`. File ini mengembalikan array berisi objek
`Hyperf\Crontab\Crontab[]`. Jika file konfigurasi tidak ada, Anda dapat
membuatnya sendiri:

```php
<?php
// config/autoload/crontab.php
use Hyperf\Crontab\Crontab;
return [
    'enable' => true,
    // Timed tasks defined by configuration
    'crontab' => [
        // Callback type timed task (default)
        (new Crontab())->setName('Foo')->setRule('* * * * *')->setCallback([App\Task\FooTask::class, 'execute'])->setMemo('This is an example timed task'),
        // Command type timed task
        (new Crontab())->setType('command')->setName('Bar')->setRule('* * * * *')->setCallback([
            'command' => 'swiftmailer:spool:send',
            // (optional) arguments
            'fooArgument' => 'barValue',
            // (optional) options
            '--message-limit' => 1,
            // Remember to add it, otherwise it will cause the main process to exit
            '--disable-event-dispatcher' => true,
        ])->setEnvironments(['develop', 'production']),
        // Closure type timed task (Only supported in Coroutine style server)
        (new Crontab())->setType('closure')->setName('Closure')->setRule('* * * * *')->setCallback(function () {
            var_dump(date('Y-m-d H:i:s'));
        })->setEnvironments('production'),
    ],
];
```

Sejak versi 3.1, metode konfigurasi baru telah ditambahkan. Anda dapat
mendefinisikan scheduled task melalui `config/crontabs.php`. Jika file
konfigurasi tidak ada, Anda dapat membuatnya sendiri:

```php
<?php
// config/crontabs.php
use Hyperf\Crontab\Schedule;

Schedule::command('foo:bar')->setName('foo-bar')->setRule('* * * * *');
Schedule::call([Foo::class, 'bar'])->setName('foo-bar')->setRule('* * * * *');
Schedule::call(fn() => (new Foo)->bar())->setName('foo-bar')->setRule('* * * * *');
```

### Menggunakan annotation

Definisi task dapat diselesaikan dengan cepat melalui annotation `#[Crontab]`.
Contoh definisi berikut dan definisi file konfigurasi memiliki tujuan yang sama.
Definisikan sebuah timed task bernama `Foo` untuk mengeksekusi
`App\Task\FooTask::execute()` setiap menit.

```php
<?php
namespace App\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

#[Crontab(name: "Foo", rule: "* * * * *", callback: "execute", memo: "This is an example scheduled task")]
class FooTask
{
     #[Inject]
    private StdoutLoggerInterface $logger;

    public function execute()
    {
        $this->logger->info(date('Y-m-d H:i:s', time()));
    }

    #[Crontab(rule: "* * * * * *", memo: "foo")]
    public function foo()
    {
        var_dump('foo');
    }
}
```

### Konfigurasi task

#### name

Nama dari timed task dapat berupa string apa saja, dan nama dari setiap timed
task harus unik.

#### rule

Aturan eksekusi timed task didefinisikan pada tingkat menit, konsisten dengan
aturan perintah `crontab` pada Linux. Ketika didefinisikan pada tingkat detik,
panjang aturan berubah dari 5 digit menjadi 6 digit, dan node tingkat detik
ditambahkan di depan aturan. Ini berarti eksekusi dilakukan pada aturan tingkat
menit jika menggunakan 5 digit dan tingkat detik jika menggunakan 6 digit.
Sebagai contoh, `*/5 * * * * *` berarti akan dieksekusi setiap 5 detik.
Perhatikan bahwa garis miring (forward slash) dalam definisi aturan annotation
harus di-escape menggunakan simbol backslash `\`: `*\/5 * * * * *`.

#### callback

Callback yang dieksekusi oleh timed task. Ketika didefinisikan melalui file
konfigurasi, array `[$class, $method]` digunakan di mana `$class` adalah nama
lengkap dari class dan `$method` adalah method `public` dari class tersebut.
Saat menggunakan annotation, Anda hanya perlu menyediakan nama method `public`
pada class saat ini. Jika class saat ini hanya memiliki satu method `public`,
Anda bahkan tidak perlu menyediakan atribut ini.

#### singleton

Untuk mengatasi masalah eksekusi task secara konkuren, task akan selalu berjalan
di saat yang sama. Namun, hal ini tidak dapat menjamin eksekusi berulang dari
task di dalam cluster.

#### onOneServer

Ketika menyebarkan (deploying) proyek dengan banyak instance, hanya satu
instance yang akan mengeksekusi task yang diberikan.

#### mutexPool

Redis connection pool yang digunakan oleh mutex.

#### mutexExpires

Periode timeout untuk mutex lock. Jika scheduled task dieksekusi tetapi mutex
lock gagal dilepaskan, mutex lock akan dilepaskan secara otomatis setelah waktu
ini.

#### memo

Catatan untuk timed task. Atribut ini bersifat opsional dan tidak memiliki arti
sintaksis. Tujuannya adalah untuk membantu developer memahami timed task
tersebut.

#### enable

Apakah task saat ini aktif.

#### environments

Environment variable yang perlu diatur saat mengeksekusi task. Nilai dari
atribut ini adalah sebuah array, di mana key adalah nama environment variable,
dan value adalah nilai dari environment variable tersebut.

### Strategi distribusi penjadwalan

Timed task dirancang untuk memungkinkan penggunaan berbagai strategi yang
berbeda untuk penjadwalan dan distribusi eksekusi task.

> Saat menggunakan service bergaya coroutine, harap gunakan coroutine
> execution strategy.

#### Menyesuaikan strategi distribusi penjadwalan

Anda dapat mengubah strategi yang digunakan saat ini dengan mengubah instance
yang sesuai dengan interface class `Hyperf\Crontab\Strategy\StrategyInterface`
di `config/autoload/dependencies.php`. Secara default, `Worker process
execution strategy` digunakan, dan class yang sesuai adalah
`Hyperf\Crontab\Strategy\WorkerStrategy`. Sebagai contoh, jika kita ingin
menggunakan `App\Crontab\Strategy\FooStrategy`:

```php
<?php
return [
    \Hyperf\Crontab\Strategy\StrategyInterface::class => \App\Crontab\Strategy\FooStrategy::class,
];
```

##### Worker process execution strategy [default]

Class: `Hyperf\Crontab\Strategy\WorkerStrategy`

Secara default, strategi ini digunakan. Proses `CrontabDispatcherProcess`
mengurai (parse) scheduled task dan meneruskan eksekusi task ke setiap proses
`worker` melalui polling komunikasi antar-proses (inter-process communication).
Setiap proses `worker` kemudian menggunakan coroutine untuk benar-benar
mengeksekusi task tersebut.

##### TaskWorker execution strategy

Class: `Hyperf\Crontab\Strategy\TaskWorkerStrategy`

Strategi ini mengurai scheduled task untuk proses `CrontabDispatcherProcess` dan
meneruskan eksekusi task ke setiap proses `TaskWorker` melalui polling
komunikasi antar-proses. Setiap proses `TaskWorker` kemudian menggunakan
coroutine untuk benar-benar mengeksekusi task tersebut. Saat menggunakan
strategi ini, perhatikan apakah proses `TaskWorker` dikonfigurasi dengan
protokol yang didukung.

##### Multi-process execution strategy

Class: `Hyperf\Crontab\Strategy\ProcessStrategy`

Strategi ini mengurai scheduled task untuk proses `CrontabDispatcherProcess` dan
mentransfer eksekusi task ke setiap proses `Worker` dan proses `TaskWorker`
melalui polling komunikasi antar-proses. Setiap proses kemudian menggunakan
coroutine untuk benar-benar mengeksekusi task. Saat menggunakan strategi ini,
perhatikan apakah proses `TaskWorker` dikonfigurasi untuk mendukung coroutine.

##### Coroutine execution strategy

Class: `Hyperf\Crontab\Strategy\CoroutineStrategy`

Strategi ini mengurai scheduled task untuk proses `CrontabDispatcherProcess` dan
membuat coroutine untuk berjalan pada setiap eksekusi task di dalam proses
tersebut.

## Menjalankan timed task

Setelah Anda menyelesaikan konfigurasi di atas dan mendefinisikan scheduled
task, Anda hanya perlu langsung memulai `Server`, dan timed task akan mulai
berjalan bersamaan. Setelah Anda memulainya, bahkan jika Anda mendefinisikan
timed task dengan periode yang cukup singkat, timed task tersebut tidak akan
langsung berjalan. Semua timed task baru akan dimulai pada periode menit
berikutnya. Sebagai contoh, ketika Anda memulainya pada pukul `10:11 lewat 12
detik`, maka timed task akan secara resmi mulai dieksekusi pada pukul
`10:12:00`.
