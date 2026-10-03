# FAQ

## Swoole short name belum dimatiin

```
[ERROR] Swoole short function names must be disabled before the server starts, please set swoole.use_shortname = 'Off' in your php.ini.
```

Tambahin konfigurasi `swoole.use_shortname = 'Off'` ke `php.ini`.

> Perhatikan bahwa konfigurasi ini harus disetel di `php.ini` dan tidak bisa ditimpa melalui fungsi `ini_set()`.

Atau, Anda bisa menjalankan service dengan perintah berikut untuk menonaktifkan fitur Swoole short name saat mengeksekusi perintah PHP:

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## Async queue kehilangan pesan

Kalo method di `handle` gak jalan pas pake komponen `async-queue`, cek dulu hal berikut:

1. Apakah `Redis` digunakan bersama dengan orang lain dan pesan dikonsumsi oleh orang lain.
2. Apakah ada sisa proses lokal yang dikonsumsi oleh proses lain.

Berikut adalah solusi yang tidak pernah gagal:

1. `killall php`
2. Ubah konfigurasi `channel` pada `async-queue`

## Menggunakan komponen AMQP menghasilkan error `Swoole\Error: API must be called in the coroutine`

Tinggal ganti `params.close_on_destruct` jadi `false` di `config/autoload/amqp.php`.

## Perubahan kode tidak berpengaruh

Kalo kode yang diubah gak ngefek, jalanin perintah ini:

```bash
composer dump-autoload -o
```

Pas tahap development, jangan set `scan_cacheable` ke `true`, soalnya nanti scanner gak bakal mindai ulang file kalo `collector cache` udah ada. Oh iya, `Dockerfile` di skeleton package resmi udah enable ini secara default. Developer `Docker` harap perhatiin.

> Ketika environment variable `SCAN_CACHEABLE` ada, konfigurasi ini tidak bisa dimodifikasi di `.env`.

## Service gagal dijalankan karena syntax error

Saat project dijalankan dan memunculkan error seperti berikut:

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

Anda bisa menjalankan script `composer analyse` untuk melakukan static analysis pada project guna menemukan segmen kode yang bermasalah.

Masalah ini biasanya karena update `zircote/swagger` ke versi 3.0.5. Detailnya liat [#834](https://github.com/zircote/swagger-php/issues/834).
Jika Anda telah menginstal [hyperf/swagger](https://github.com/hyperf/swagger), disarankan untuk mengunci versi [zircote/swagger](https://github.com/zircote/swagger-php) ke 3.0.4.

## Project gagal dijalankan karena batas memori terlalu kecil

`memory_limit` default PHP hanya `128M`.

Kita bisa jalanin pake `php -d memory_limit=-1 bin/hyperf.php start`, atau ubah `php.ini`:

```
# Lihat lokasi file konfigurasi php.ini
php --ini

# Ubah konfigurasi memory_limit
memory_limit=-1
```

## Error saat menggunakan `#[Inject]` di Trait: `Error while injecting dependencies into ... No entry or class found ...`

Kalo sebuah Trait inject properti pake `#[Inject] @var`, dan subclass-nya `use` class dengan nama yang sama dari namespace beda, nama class di Trait bakal ketimpa, jadinya injection gagal:

```php
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;

trait TestTrait
{
    #[Inject]
    protected ResponseInterface $response;
}
```

Seperti di atas, Trait class inject `Hyperf\HttpServer\Contract\ResponseInterface`. Tapi kalo subclass pake class `ResponseInterface` dari namespace beda, misalnya `use Psr\Http\Message\ResponseInterface`, nama class asli di Trait bakal ketimpa:

```php
// penggunaan nama class yang sama akan menimpa Trait
use Psr\Http\Message\ResponseInterface;

class IndexController
{
    use TestTrait;
}
// Error while injecting dependencies into App\Controller\IndexController: No entry or class found for 'Psr\Http\Message\ResponseInterface'
```

Masalah di atas bisa diselesaikan dengan dua metode berikut:

- Ubah alias di subclass melalui `as`: `use Psr\Http\Message\ResponseInterface as PsrResponseInterface;`
- Untuk Trait class, batasi tipe properti di `PHP 7.4` ke atas: `protected ResponseInterface $response;`

## Project gagal dijalankan karena ekstensi Grpc tidak terinstal atau Pcntl hilang

- Scanning annotation di v2.2 pake ekstensi `pcntl`, jadi pastiin `PHP` Anda punya ekstensi ini.

```shell
php --ri pcntl

pcntl

pcntl support => enabled
```

- Saat `grpc` diaktifkan, Anda perlu menambahkan `grpc.enable_fork_support= 1;` ke `php.ini` untuk mendukung pengaktifan child processes.

## HTTP Server gagal dijalankan setelah menyetel `open_websocket_protocol` ke `false`: `Swoole\Server::start(): require onReceive callback`

1. Periksa apakah Swoole dikompilasi dengan http2

```shell
php --ri swoole | grep http2
http2 => enabled
```

Jika tidak, Anda perlu mengkompilasi ulang Swoole dan menambahkan parameter `--enable-http2`.

2. Periksa apakah opsi `open_http2_protocol` di file `server.php` adalah `true`.

## Command tidak bisa ditutup secara normal

Abis pake teknologi multiplexing kayak AMQP di dalam Command, kadang prosesnya gak bisa ditutup normal. Kalo gitu, tinggal tambahin kode berikut di akhir logika eksekusi:

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;

CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
```

## Komponen upload OSS melaporkan error iconv

- fix aliyun oss wrong charset: https://github.com/aliyun/aliyun-oss-php-sdk/issues/101
- https://github.com/docker-library/php/issues/240#issuecomment-762438977
- https://github.com/docker-library/php/pull/1264

Pas pake komponen `aliyuncs/oss-sdk-php` buat upload, bisa muncul error iconv. Coba hindari pake cara berikut:

Saat menggunakan image `hyperf/hyperf:8.0-alpine-v3.12-swoole`:

```
RUN apk --no-cache --allow-untrusted --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ add gnu-libiconv=1.15-r2
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so
```

Saat menggunakan image `hyperf/hyperf:8.0-alpine-v3.13-swoole`:

```dockerfile
RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.13/community/ gnu-libiconv=1.15-r3
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
```

## Koleksi DI gagal

Kalo terjadi exception pas fase koleksi DI (misalnya namespace salah, dll), log-nya bakal keliatan kayak gini:

- Untuk kode bisnis, perbaiki file dan class yang terkait dengan path di log.
- Untuk kode framework, kirimkan PR atau Issue sebagai feedback.
- Untuk komponen pihak ketiga, berikan feedback ke penulis komponen.

```bash
[ERROR] DI Reflection Manager collecting class reflections failed. 
File: xxxx.
Exception: xxxx
```

## Service gagal dijalankan karena ketidakcocokan versi lingkungan

Saat project dijalankan dan memunculkan error seperti berikut:

```bash
Hyperf\Engine\Channel::push(mixed $data, float $timeout = -1): bool must be compatible with Swoole\Coroutine\Channel::push($data, $timeout = -1)
```

Masalah ini biasanya karena versi Swoole saat runtime beda sama versi Swoole pas install framework/component.

Ini bisa diselesaikan dengan menggunakan versi Swoole dan PHP yang sama seperti yang digunakan saat instalasi.
