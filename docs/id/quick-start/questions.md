# FAQ

## Nama fungsi pendek Swoole belum dinonaktifkan

```
[ERROR] Swoole short function names must be disabled before the server starts, please set swoole.use_shortname = 'Off' in your php.ini.
```

Anda perlu menambahkan `swoole.use_shortname = 'Off'` ke dalam file konfigurasi
php.ini Anda.

> Catatan: Konfigurasi ini HARUS dikonfigurasi di php.ini dan TIDAK DAPAT
> ditimpa menggunakan fungsi ini_set().

Anda juga dapat memulai server melalui perintah berikut untuk menonaktifkan
nama fungsi pendek Swoole saat menjalankan perintah PHP:

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## Kehilangan pesan pada asynchronous queue

Jika metode `handle` tidak dijalankan saat menggunakan komponen `async-queue`,
silakan periksa beberapa kemungkinan berikut:

1. Apakah `Redis` digunakan bersama dengan proyek lain atau pengguna lain,
   sehingga pesan dikonsumsi oleh proyek atau pengguna tersebut?
2. Apakah Anda masih memiliki sisa-sisa proses lama yang berjalan yang mungkin
   mengonsumsi pesan tersebut?

Berikut adalah solusi mudah untuk kedua masalah tersebut:

1. Jalankan perintah `killall php` di `console` Anda
2. Ubah konfigurasi `channel` pada `async-queue` Anda

## Kesalahan `Swoole\Error: API must be called in the coroutine` saat menggunakan komponen `hyperf/amqp`

Atur nilai konfigurasi `close_on_destruct` menjadi `false` di dalam file
konfigurasi `config/autoload/amqp.php`.

## Semua request mengembalikan error 404 saat menggunakan Swoole versi 4.5 dan komponen `view`

Jika Anda menggunakan Swoole versi 4.5 dan komponen `view` serta mengalami
masalah error `404`, Anda dapat mencoba menghapus item konfigurasi
`static_handler_locations` dari file konfigurasi
`config/autoload/server.php`.

Nilai konfigurasi ini berisi path yang akan dianggap sebagai rute
`static file`, jadi jika nilainya adalah `/`, semua request akan diproses
sebagai file, yang menghasilkan error 404.

## Perubahan kode tidak berpengaruh

Jika tidak ada perubahan saat Anda memodifikasi kode aplikasi `Hyperf` Anda,
jalankan perintah berikut:

```bash
composer dump-autoload -o
```

Selama pengembangan, harap JANGAN mengatur nilai konfigurasi `scan_cacheable`
menjadi `true`, karena hal itu akan menyebabkan file tidak diparse ulang saat
`collector cache` digunakan. Selain itu, `Dockerfile` dalam paket resmi
`hyperf-skeleton` mengaktifkan konfigurasi ini secara default. Saat melakukan
pengembangan di lingkungan `Docker`, harap atur `scan_cacheable` ke `false`.

> Ketika environment variable `SCAN_CACHEABLE` ada, konfigurasi ini tidak dapat
> diubah di file `.env` apa pun.

## Error sintaksis saat memulai server

Apakah exception berikut dilemparkan ketika server `Hyperf` dimulai:

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

Silakan jalankan `composer analyse` untuk menginisialisasi static scan dari
source code untuk menemukan masalahnya.

Biasanya masalah ini disebabkan oleh penggunaan versi `3.0.5` dari
[zircote/swagger](https://github.com/zircote/swagger-php), silakan lihat
[#834](https://github.com/zircote/swagger-php/issues/834) untuk informasi lebih
lanjut.

Jika Anda telah menginstal [hyperf/swagger](https://github.com/hyperf/swagger),
harap kunci versi [zircote/swagger](https://github.com/zircote/swagger-php) pada
`3.0.4`.

## `Hyperf` tidak dapat dimulai karena memory_limit terlalu kecil

Secara default, `memory_limit` pada `PHP` diatur ke `128M`. Karena `Hyperf`
menggunakan paket `BetterReflection` untuk melakukan analisis kode, sejumlah
besar memori mungkin dikonsumsi dan proses `PHP` dapat melemparkan fatal
exception ketika kehabisan memori.

Anda dapat menjalankan perintah dengan argumen untuk meningkatkan batas memori
seperti `php -d memory_limit=-1 bin/hyperf.php start` atau memodifikasi file
konfigurasi `php.ini`:

```ini
# Look for the location of your php.ini file
php --ini

# Set the memory_limit within that file
memory_limit=-1
```

## Error `Error while injecting dependencies into... No entry or class found...` saat menginjeksi trait menggunakan `#[Inject]`

Error ini muncul ketika Anda menginjeksi trait menggunakan namespace melalui
`Inject` dan class yang berisi sintaks `use Trait;` menggunakan namespace yang
berkonflik. Ini adalah konsep yang rumit tetapi contoh berikut akan membuatnya
mudah dipahami:

```php
use Hyperf\HttpServer\Contract\ResponseInterface; # Namespace containing ResponseInterface class
use Hyperf\Di\Annotation\Inject;

trait TestTrait
{
    #[Inject]
    protected ResponseInterface $response;
}
```

Pada trait di atas, class `Hyperf\HttpServer\Contract\ResponseInterface`
diinjeksi. Jika sub-class (class yang menggunakan trait ini) menggunakan class
`ResponseInterface` dengan namespace yang berbeda, misalnya
`Psr\Http\Message\ResponseInterface`, hal itu akan menyebabkan
`ResponseInterface` yang diinjeksi tertimpa.

```php
use Psr\Http\Message\ResponseInterface; # A conflicting namespace containing a ResponseInterface class

class IndexController
{
    use TestTrait;
    // Error while injecting dependencies into App\Controller\IndexController: No entry or class found for 'Psr\Http\Message\ResponseInterface'
}
```

Masalah ini dapat diperbaiki menggunakan metode berikut:

* Buat alias pada sub-class untuk mencegah konflik: `use Psr\Http\Message\ResponseInterface as PsrResponseInterface;`
* Di `PHP` versi `7.4` Anda dapat menambahkan tipe ke atribut di dalam class trait: `protected ResponseInterface $response;`

## `Hyperf` tidak akan menjalankan perintah karena ekstensi `grpc` atau `pcntl` tidak diinstal

`Hyperf` versi `2.2` membutuhkan ekstensi `pcntl`, Anda dapat memeriksa apakah
ekstensi tersebut sudah terinstal dengan menjalankan perintah `php --ri pcntl`:

```
pcntl

pcntl support => enabled
```

Saat menggunakan `grpc`, Anda harus mengaktifkan `fork support` untuk mendukung
pembukaan child process dengan menambahkan baris berikut ke `php.ini` Anda:

```
grpc.enable_fork_support=1;
```

## Nilai `open_websocket_protocol` diatur ke `false` setelah menerima error: `Swoole\Server::start(): require onReceive callback`

1. Periksa apakah `Swoole` telah dikompilasi dengan dukungan `http2`:

```
php --ri swoole | grep http2
http2 => enabled
```

Jika hasil dari perintah ini kosong, Anda perlu mengompilasi ulang `Swoole`
dengan parameter `--enable-http2`.

2. Periksa apakah nilai konfigurasi `open_http2_protocol` diatur ke `true` di
   dalam file konfigurasi `config/autoload/server.php`

## Command tidak dapat ditutup dengan benar

Setelah menggunakan teknologi multipleks seperti AMQP di dalam Command,
Command tersebut mungkin tidak dapat ditutup secara normal. Dalam kasus ini,
Anda hanya perlu menambahkan kode berikut di akhir logika eksekusi.

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;

CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
```

## Komponen unggah OSS melaporkan error iconv

- fix Aliyun oss wrong charset: https://github.com/aliyun/aliyun-oss-php-sdk/issues/101
- https://github.com/docker-library/php/issues/240#issuecomment-762438977
- https://github.com/docker-library/php/pull/1264

Saat menggunakan komponen `aliyuncs/oss-sdk-php` untuk mengunggah, error iconv
akan dilaporkan. Anda dapat mencoba menghindarinya dengan metode berikut:

Saat menggunakan image `hyperf/hyperf:8.0-alpine-v3.12-swoole`

```
RUN apk --no-cache --allow-untrusted --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ add gnu-libiconv=1.15-r2
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so
```

Saat menggunakan image `hyperf/hyperf:8.0-alpine-v3.13-swoole`

```dockerfile
RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.13/community/gnu-libiconv=1.15-r3
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
```

## DI Reflection Manager gagal melakukan pengumpulan (collect failed)

Ketika terjadi exception selama fase pengumpulan DI (misalnya, error namespace),
output log dengan format berikut mungkin akan dihasilkan.

- Kode layanan (service code), periksa file dan class yang terkait dengan path
  di dalam log.
- Kode framework, kirimkan umpan balik melalui PR.
- Komponen pihak ketiga, kirimkan umpan balik ke pembuat komponen.

```bash
[ERROR] DI Reflection Manager collecting class reflections failed. 
File: xxxx.
Exception: xxxx
```

## Layanan tidak dapat dimulai karena versi lingkungan tidak konsisten

Ketika proyek dimulai, error yang mirip dengan berikut ini akan dilemparkan

```
Hyperf\Engine\Channel::push(mixed $data, float $timeout = -1): bool must be compatible with Swoole\Coroutine\Channel::push($data, $timeout = -1)
```

Masalah ini biasanya disebabkan oleh ketidakkonsistenan antara versi Swoole
yang digunakan saat menginstal framework/komponen dan versi Swoole aktual yang
digunakan saat runtime.

Harap jaga konsistensi versi Swoole dan PHP saat menginstal dan menggunakan.
