# Pengetahuan Sebelum Memulai Pemrograman

Berikut hal-hal yang perlu diketahui sebelum coding pake Hyperf.

## Tidak Bisa Mendapatkan/Menyetel Parameter Properti melalui Global Variables

Di `PHP-FPM`, Anda bisa dapetin parameter request lewat global variables, server parameters, dll. Tapi di `Hyperf` dan `Swoole`, Anda **gak bisa** pake `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER` atau variabel `$_` lainnya buat dapetin parameter.

## Class yang Diperoleh melalui Container Bersifat Singleton

Lewat dependency injection container, semua objek yang persist di dalam proses dibagi ke multiple coroutines, jadi gak boleh nyimpen data spesifik request atau spesifik coroutine tertentu. Data kayak gitu diproses lewat coroutine context. Baca bagian [Dependency Injection](id/di.md) dan [Coroutine](id/coroutine.md) baik-baik.

## Deployment

> Dockerfile resmi sudah menyiapkan operasi-operasi ini.

Saat men-deploy production environment, pastikan untuk mengaktifkan `scan_cacheable`.

Kalo konfigurasi ini diaktifin, proxy class dan annotation cache bakal digenerate pas scan pertama, dan cache bisa langsung dipake pas restart, ini ngoptimalin pemakaian memori dan waktu startup. Karena tahap scan dilewati, `Composer Class Map` jadi andalan, makanya kita harus jalanin `--optimize-autoloader` dari composer buat ngoptimalkan class index.

Ringkasnya, untuk mengupdate kode di production environment, Anda perlu menjalankan perintah berikut sebelum me-restart project:

```bash
# Optimasi composer class index
composer dump-autoload -o
# Generate semua proxy class dan annotation cache
php bin/hyperf.php
```

## Hindari Beralih Coroutine di Magic Methods

> Tidak termasuk method __call dan __callStatic

Hindari coroutine switching di `__get`, `__set`, dan `__isset`, bisa bikin perilaku yang gak terduga.

```php
<?php

require_once 'vendor/autoload.php';

use function Hyperf\Coroutine\go;

Swoole\Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

class Foo
{
    public function __get(string $name)
    {
        sleep(1);
        return $name;
    }

    public function __set(string $name, mixed $value)
    {
        sleep(1);
        var_dump($name, $value);
    }

    public function __isset(string $name): bool
    {
        sleep(1);
        var_dump($name);
        return true;
    }
}

$foo = new Foo();
go(static function () use ($foo) {
    var_dump(isset($foo->xxx));
});

go(static function () use ($foo) {
    var_dump(isset($foo->xxx));
});

\Swoole\Event::wait();
```

Saat kita menjalankan kode di atas, akan menghasilkan output berikut:

```shell
bool(false)
string(3) "xxx"
bool(true)
```
