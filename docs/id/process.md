# Custom Process

[hyperf/process](https://github.com/hyperf/process) memungkinkan Anda
menambahkan custom process yang ditentukan oleh pengguna. Fitur ini biasanya
digunakan untuk membuat process khusus untuk monitoring, reporting, atau tugas
khusus lainnya. Ketika server dimulai, ia akan secara otomatis membuat process
dan mengeksekusi subprocess yang ditentukan. Jika process berhenti secara tidak
terduga, server akan secara otomatis me-restart process tersebut.

## Membuat Custom Process

Implementasikan subclass yang mewarisi `Hyperf\Process\AbstractProcess` dan
implementasikan interface method `handle(): void` dengan kode logika Anda di
dalam method tersebut. Mari kita ambil kode ini sebagai contoh:

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;

class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // Your code...
    }
}
```

Ini mendefinisikan class custom process, tetapi class tersebut belum terdaftar
di `ProcessManager`. Kita dapat mendaftarkannya menggunakan salah satu dari dua
cara: `configuration file` atau `annotation`.

### Registrasi melalui configuration file

Cukup tambahkan class custom process Anda di `config/autoload/processes.php`:

```php
// config/autoload/processes.php
return [
    \App\Process\FooProcess::class,
];
```

### Registrasi melalui annotation

Cukup definisikan annotation `#[Process]` pada class custom process, dan Hyperf
akan mengumpulkan serta secara otomatis menyelesaikan proses registrasi:

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "foo_process")]
class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // Your code...
    }
}
```

> Ketika menggunakan annotation `#[Process]`, namespace
> `use Hyperf\Process\Annotation\Process;` diperlukan;

## Menambahkan Kondisi untuk Startup Process

Terkadang custom process tidak harus selalu dijalankan setiap saat. Apakah
sebuah custom process dijalankan atau tidak dapat ditentukan berdasarkan
konfigurasi atau kondisi tertentu dengan meng-override method `isEnable($server): bool`
pada class custom process. Method ini secara default diimplementasikan dengan
mengembalikan nilai `true`, yang akan berjalan bersamaan dengan startup
service. Jika method mengembalikan nilai `false`, custom process tidak akan
dijalankan saat service dimulai.

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "foo_process")]
class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // Your code...
    }

    public function isEnable($server): bool
    {
        // Do not start with service startup
        return false;
    }
}
```

## Mengonfigurasi Custom Process

Terdapat beberapa parameter konfigurasi dalam custom process, yang dapat
didefinisikan dengan meng-override attribute yang sesuai pada subclass atau
mendefinisikan attribute yang sesuai pada annotation `#[Process]`.

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "foo_process", name: "user-process", redirectStdinStdout: false, pipeType: 2, enableCoroutine: true)]
class FooProcess extends AbstractProcess
{
    /**
     * Number of processes
     * @var int
     */
    public $nums = 1;

    /**
     * Process name
     * @var string
     */
    public $name = 'user-process';

    /**
     * Redirect the standard input and output of a custom process
     * @var bool
     */
    public $redirectStdinStdout = false;

    /**
     * Pipe type
     * @var int
     */
    public $pipeType = 2;

    /**
     * Whether to enable coroutine
     * @var bool
     */
    public $enableCoroutine = true;
}
```

## Contoh Penggunaan

Kita membuat sebuah child process untuk memantau jumlah kegagalan antrean
(failure queue), dan melaporkan peringatan ketika terdapat data di dalam
failure queue tersebut.

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Contract\StdoutLoggerInterface;

#[Process(name: "demo_process")]
class DemoProcess extends AbstractProcess
{
    public function handle(): void
    {
        $logger = $this->container->get(StdoutLoggerInterface::class);

        while (true) {
            $redis = $this->container->get(\Redis::class);
            $count = $redis->llen('queue:failed');

            if ($count > 0) {
                $logger->warning('The num of failed queue is ' . $count);
            }

            sleep(1);
        }
    }
}
```
