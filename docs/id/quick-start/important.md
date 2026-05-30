# Pengetahuan Sebelum Mulai Pemrograman

Berikut adalah kumpulan pengetahuan atau konten yang harus diketahui sebelum
mulai memprogram dengan Hyperf.

## Tidak Dapat Mengambil/Mengatur Parameter Properti Melalui Variabel Global

Di bawah `PHP-FPM`, Anda dapat mengambil parameter permintaan melalui variabel
global, parameter server, dll. Namun, dalam `Hyperf` dan `Swoole`, Anda **tidak
dapat** mengambil parameter atribut apa pun melalui variabel global yang diawali
dengan `$_` seperti `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`.

## Class yang Diperoleh Melalui Container Bersifat Singleton

Melalui dependency injection container, semua persistensi dalam proses akan
dibagikan ke beberapa coroutine, sehingga tidak boleh berisi data apa pun yang
bersifat unik untuk request atau unik untuk coroutine. Jenis data seperti ini
diproses melalui coroutine context. Silakan baca bagian
[Dependency Injection](id/di.md) dan [Coroutine](id/coroutine.md) dengan cermat.

## Deployment

> Dockerfile resmi sudah menyiapkan operasi-operasi ini.

Saat melakukan deployment ke lingkungan produksi (production), pastikan untuk
mengaktifkan `scan_cacheable`.

Setelah konfigurasi ini diaktifkan, proxy class dan cache anotasi akan dibuat
selama pemindaian pertama, dan cache tersebut dapat langsung digunakan saat
dijalankan ulang. Hal ini sangat mengoptimalkan penggunaan memori dan waktu
memulai (startup). Karena tahap pemindaian dilewati, `Composer Class Map` akan
diandalkan, sehingga kita harus menjalankan perintah composer dengan opsi
`--optimize-autoloader` untuk mengoptimalkan indeks class.

Singkatnya, untuk memperbarui kode di lingkungan produksi, Anda perlu
menjalankan perintah berikut sebelum memulai ulang proyek:

```bash
# Optimize the composer class index
composer dump-autoload -o
# Generate all proxy classes and the annotation cache
php bin/hyperf.php
```

## Hindari Peralihan Coroutine dalam Magic Method

> Tidak termasuk metode __call dan __callStatic

Cobalah untuk menghindari peralihan coroutine di dalam `__get`, `__set`, dan
`__isset` karena hal ini dapat menyebabkan perilaku yang tidak terduga.

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

Ketika kita menjalankan kode di atas, kode tersebut akan mengembalikan hasil
berikut:

```shell
bool(false)
string(3) "xxx"
bool(true)
```
