# Custom Process

[hyperf/process](https://github.com/hyperf/process) memungkinkan Anda menambahkan worker process kustom. Biasanya digunakan untuk membuat process khusus untuk monitoring, pelaporan, atau tugas spesifik lainnya. Process ini dibuat otomatis saat Server dimulai dan menjalankan fungsi child process yang ditentukan. Jika process keluar secara tidak terduga, Server akan merestartnya.

## Membuat custom process

Untuk membuat custom process, buat subclass yang meng-extend `Hyperf\Process\AbstractProcess` dan implementasikan method `handle(): void`, lalu isi dengan logika Anda. Contohnya:

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;

class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // Kode Anda ...
    }
}
```

Ini melengkapi kelas custom process, tetapi belum didaftarkan ke `ProcessManager`. Anda dapat mendaftarkannya menggunakan `file konfigurasi` atau `annotation`.

### Mendaftarkan melalui file konfigurasi

Cukup tambahkan kelas custom process Anda ke `config/autoload/processes.php`:

```php
// config/autoload/processes.php
return [
    \App\Process\FooProcess::class,
];
```

### Mendaftarkan melalui Annotation

Cukup definisikan annotation `#[Process]` pada kelas custom process Anda, dan Hyperf akan mengumpulkan serta mendaftarkannya secara otomatis:

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
        // Kode Anda ...
    }
}
```

> Saat menggunakan annotation `#[Process]`, pastikan Anda `use Hyperf\Process\Annotation\Process;`.

## Mengatur kondisi启动

Terkadang, Anda mungkin tidak ingin menjalankan custom process setiap saat. Apakah process dijalankan bisa tergantung pada konfigurasi atau kondisi tertentu. Caranya dengan meng-override method `isEnable(): bool` di kelas custom process. Secara default method ini mengembalikan `true`, yang berarti process ikut berjalan saat service menyala. Jika mengembalikan `false`, custom process tidak akan berjalan.

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
        // Kode Anda ...
    }
    
    public function isEnable($server): bool
    {
        // Tidak ikut serta dalam startup service
        return false;   
    }
}
```

## Mengonfigurasi custom process

Custom process memiliki beberapa parameter yang dapat dikonfigurasi. Parameter-parameter ini dapat didefinisikan baik dengan meng-override properti yang sesuai di subclass atau dengan mendefinisikan atribut yang sesuai di dalam annotation `#[Process]`.

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "user-process", redirectStdinStdout: false, pipeType: 2, enableCoroutine: true)]
class FooProcess extends AbstractProcess
{
    /**
     * Jumlah process
     */
    public int $nums = 1;

    /**
     * Nama process
     */
    public string $name = 'user-process';

    /**
     * Mengalihkan input dan output standar dari custom process
     */
    public bool $redirectStdinStdout = false;

    /**
     * Tipe pipe
     */
    public int $pipeType = 2;

    /**
     * Apakah akan mengaktifkan coroutine
     */
    public bool $enableCoroutine = true;
}
```

## Contoh Penggunaan

Kita akan membuat child process untuk memantau jumlah tugas gagal dalam antrean dan melaporkan peringatan jika ada data di antrean gagal.

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
                $logger->warning('Jumlah antrean gagal adalah ' . $count);
            }

            sleep(1);
        }
    }
}
```

Jika Anda menggunakan asynchronous I/O dan tidak bisa meletakkan logika langsung ke dalam loop, Anda dapat mencoba pendekatan berikut:

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Swoole\Timer;

#[Process(name: "demo_process")]
class DemoProcess extends AbstractProcess
{
    public function handle(): void
    {
        Timer::tick(1000, function(){
            var_dump(1);
            // Lakukan sesuatu...
        });

        while (true) {
            sleep(1);
        }
    }
}
```
