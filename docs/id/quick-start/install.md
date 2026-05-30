# Instalasi

## Persyaratan

Hyperf hanya dapat berjalan pada lingkungan sistem Linux dan macOS. Namun,
dengan perkembangan teknologi virtualisasi Docker, Anda dapat menggunakan
Windows sebagai lingkungan sistem dengan menggunakan Docker untuk Windows. Jika
Anda menggunakan macOS, kami menyarankan deployment lokal untuk menghindari
lambatnya waktu startup Hyperf yang disebabkan oleh shared disk Docker.

Berbagai Dockerfile telah disiapkan dalam proyek
[hyperf/hyperf-docker](https://github.com/hyperf/hyperf-docker), atau Anda
dapat menggunakan image prebuilt berbasis
[hyperf/hyperf](https://hub.docker.com/r/hyperf/hyperf).

Jika Anda tidak menggunakan Docker sebagai dasar dari lingkungan sistem Anda,
Anda juga dapat mempertimbangkan penggunaan [Box](id/eco/box.md) sebagai
lingkungan dasar untuk menjalankan aplikasi. Jika Anda ingin menyiapkan
lingkungan sendiri, Anda perlu memastikan bahwa lingkungan lokal Anda memenuhi
persyaratan berikut:

 - PHP >= 8.1
 - Salah satu dari engine jaringan berikut:
   - [Ekstensi PHP Swoole](https://github.com/swoole/swoole-src) >= 5.0, dengan `swoole.use_shortname` diset ke `Off` pada `php.ini` Anda
   - [Ekstensi PHP Swow](https://github.com/swow/swow) >= 1.4
 - Ekstensi PHP JSON
 - Ekstensi PHP Pcntl (Hanya pada engine Swoole)
 - Ekstensi PHP OpenSSL (Jika Anda perlu menggunakan HTTPS)
 - Ekstensi PHP PDO (Jika Anda perlu menggunakan MySQL Client)
 - Ekstensi PHP Redis (Jika Anda perlu menggunakan Redis Client)
 - Ekstensi PHP Protobuf (Jika Anda perlu menggunakan gRPC Server atau Client)


## Instal Hyperf

Hyperf menggunakan [Composer](https://getcomposer.org) untuk mengelola
dependency proyek. Sebelum menggunakan Hyperf, pastikan lingkungan operasi Anda
telah terinstal Composer.

### Membuat proyek via `Composer`

Proyek [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton)
adalah proyek skeleton yang telah kami siapkan untuk Anda, dengan file bawaan
untuk komponen umum dan konfigurasi terkait. Ini adalah proyek web dasar yang
dapat digunakan dengan cepat untuk memulai pengembangan Hyperf secara
profesional. Pada saat instalasi, Anda dapat memilih dependency komponen sesuai
dengan kebutuhan Anda sendiri.
Jalankan perintah berikut untuk membuat proyek hyperf-skeleton di lokasi saat
ini:

Berbasis engine Swoole:
```
composer create-project hyperf/hyperf-skeleton 
```

Berbasis engine Swow:
```
composer create-project hyperf/swow-skeleton 
```

> Selama proses instalasi, untuk opsi yang tidak Anda yakini, silakan langsung
tekan Enter untuk menghindari masalah di mana service tidak dapat dijalankan
karena penambahan otomatis beberapa listener tanpa konfigurasi yang tepat.

### Pengembangan di Docker

Jika lingkungan lokal Anda tidak memenuhi persyaratan sistem Hyperf, atau jika
Anda tidak terbiasa dengan konfigurasi sistem, Anda dapat menjalankan dan
mengembangkan proyek Hyperf seperti berikut menggunakan Docker.

- Menjalankan Container

Pada contoh berikut, host akan dimapping ke direktori lokal
`/workspace/skeleton`:

> Jika opsi `selinux-enabled` diaktifkan saat docker dimulai, akses ke resource
host di dalam container akan dibatasi, sehingga Anda harus menambahkan opsi
`--privileged -u root` saat memulai container.

```shell
docker run --name hyperf \
-v /workspace/skeleton:/data/project \
-p 9501:9501 -it \
--privileged -u root \
--entrypoint /bin/sh \
hyperf/hyperf:8.1-alpine-v3.18-swoole
```

- Membuat Proyek

```shell
cd /data/project
composer create-project hyperf/hyperf-skeleton
```

- Memulai proyek

```shell
cd hyperf-skeleton
php bin/hyperf.php start
```

Selanjutnya, Anda dapat melihat proyek yang terinstal di
`/workspace/skeleton/hyperf-skeleton`. Karena Hyperf adalah framework CLI
persisten, ketika Anda telah memodifikasi kode Anda, Anda harus menghentikan
instance process yang sedang berjalan dengan `CTRL + C` dan menjalankan kembali
perintah startup `php bin/hyperf.php start` untuk me-restart server Anda dan
memuat ulang kode.

## Ekstensi yang tidak kompatibel

Karena Hyperf didasarkan pada fungsionalitas coroutine Swoole yang belum pernah
ada sebelumnya, banyak ekstensi yang tidak kompatibel. Ekstensi berikut
(termasuk namun tidak terbatas pada) saat ini tidak kompatibel:

- xhprof
- xdebug (Tersedia pada PHP 8.1+ dan Swoole >= 5.0.2)
- blackfire
- trace
- uopz
