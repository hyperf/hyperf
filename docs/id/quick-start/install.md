# Instalasi

## Persyaratan Server

Hyperf punya beberapa syarat lingkungan sistem. Kalo pake Swoole network engine, cuma bisa jalan di Linux dan Mac. Tapi dengan Docker, bisa juga jalan di Windows lewat Docker for Windows. Di Mac, kami saranin deploy lokal aja biar gak lambat akibat akses shared disk Docker. Kalo pake Swow network engine, bisa jalan di Windows, Linux, dan Mac.

Project [hyperf/hyperf-docker](https://github.com/hyperf/hyperf-docker) udah nyiapin berbagai versi Dockerfile, atau Anda bisa langsung pake image [hyperf/hyperf](https://hub.docker.com/r/hyperf/hyperf) yang udah dibangun.

Kalo gak mau pake Docker sebagai basis lingkungan runtime, bisa pake [Box](id/eco/box.md). Kalo mau setup sendiri, pastiin lingkungan runtime-nya memenuhi syarat berikut:

 - PHP >= 8.2
 - Salah satu dari network engine berikut:
   - [Swoole PHP extension](https://github.com/swoole/swoole-src) >= 5.0, dengan `swoole.use_shortname` diset ke `Off` pada `php.ini`.
   - [Swow PHP extension](https://github.com/swow/swow) >= 1.4
 - JSON PHP extension
 - Pcntl PHP extension (hanya saat menggunakan Swoole engine)
 - OpenSSL PHP extension (jika diperlukan HTTPS)
 - PDO PHP extension (jika diperlukan MySQL client)
 - Redis PHP extension (jika diperlukan Redis client)
 - Protobuf PHP extension (jika diperlukan gRPC server atau client)

## Menginstal Hyperf

Hyperf menggunakan [Composer](https://getcomposer.org) untuk mengelola dependencies project. Sebelum menggunakan Hyperf, pastikan Composer sudah terinstal di lingkungan runtime Anda.

### Membuat Project melalui `Composer`

Kita udah nyiapin skeleton project lengkap dengan komponen umum, file konfigurasi, dan struktur yang sesuai. Ini project Web dasar yang siap pake buat pengembangan bisnis. Pas instalasi, Anda bisa milih component dependencies sesuai kebutuhan.

Jalankan perintah berikut untuk membuat skeleton project di lokasi Anda saat ini:

Berbasis Swoole driver:
```
composer create-project hyperf/hyperf-skeleton 
```
Berbasis Swow driver:
```
composer create-project hyperf/swow-skeleton 
```

> Selama proses instalasi, untuk opsi yang Anda tidak yakin, cukup tekan Enter untuk melanjutkan. Ini menghindari masalah di mana layanan gagal dijalankan karena secara otomatis menambahkan beberapa listener tanpa konfigurasi yang benar.

### Pengembangan di dalam Docker

Jika lingkungan lokal Anda tidak memenuhi persyaratan lingkungan Hyperf, atau jika Anda tidak terbiasa dengan konfigurasi lingkungan, Anda bisa menjalankan dan mengembangkan project Hyperf menggunakan metode berikut:

- Menjalankan container

Anda bisa melakukan mapping ke direktori yang sesuai di host machine sesuai dengan situasi aktual. Berikut adalah contoh menggunakan `/workspace/skeleton`.

> Jika opsi `selinux-enabled` diaktifkan saat menjalankan Docker, akses ke resource host dari dalam container akan dibatasi. Oleh karena itu, Anda bisa menambahkan opsi `--privileged -u root` saat menjalankan container.

```shell
docker run --name hyperf \
-v /workspace/skeleton:/data/project \
-w /data/project \
-p 9501:9501 -it \
--privileged -u root \
--entrypoint /bin/sh \
hyperf/hyperf:8.1-alpine-v3.18-swoole
```

- Membuat project

```shell
composer create-project hyperf/hyperf-skeleton
```

- Menjalankan project

```shell
cd hyperf-skeleton
php bin/hyperf.php start
```

Selanjutnya, Anda akan melihat kode Anda yang terinstal di host machine pada `/workspace/skeleton/hyperf-skeleton`.
Karena Hyperf adalah framework CLI persisten, setelah ngubah kode, matiin proses pake `CTRL + C` trus jalanin ulang `php bin/hyperf.php start`.

## Ekstensi dengan Masalah Kompatibilitas

Karena Hyperf dibangun di atas Swoole coroutines, dan coroutine di Swoole 4 adalah hal baru di PHP, masih ada masalah kompatibilitas dengan banyak ekstensi.
Ekstensi berikut (namun tidak terbatas pada) akan menyebabkan masalah kompatibilitas tertentu dan tidak bisa digunakan bersama atau berdampingan dengan Hyperf:

- xhprof
- xdebug (tersedia saat PHP version >= 8.2 dan Swoole version >= 5.0.2)
- blackfire
- trace
- uopz
