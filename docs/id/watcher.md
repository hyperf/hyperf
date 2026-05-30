# Watcher (Hot Reload)

Sejak versi `2.0` menggunakan `BetterReflection` untuk mengumpulkan `abstract
syntax tree (AST)` dan `reflection data`, kecepatan pemindaian menjadi jauh lebih
lambat dibandingkan versi `1.1`.

> Startup pertama aplikasi akan lebih lambat karena belum ada cache pemindaian
> yang tersedia. Kecepatan startup berikutnya akan meningkat, tetapi karena
> `BetterReflection` perlu diinstansiasi, waktu startup masih relatif lama.

Selain menyelesaikan masalah startup di atas, komponen `Watcher` juga menangani
pembaruan (restart) aplikasi segera setelah perubahan file terjadi.

> Komponen ini hanya cocok untuk lingkungan pengembangan (development), silakan
> gunakan dengan hati-hati di lingkungan produksi (production).

## Instalasi

```bash
composer require hyperf/watcher --dev
```

## Konfigurasi

### Publish konfigurasi

```bash
php bin/hyperf.php vendor:publish hyperf/watcher
```

### Petunjuk Konfigurasi

|      Nama      |     Default      |                                      Deskripsi                                       |
| :------------: | :--------------: | :----------------------------------------------------------------------------------: |
|     driver     | `ScanFileDriver` |                          File watcher dengan metode polling default                  |
|      bin       |   `PHP_BINARY`   | Skrip yang digunakan untuk memulai layanan, contoh: `php -d swoole.use_shortname=Off`|
|   watch.dir    | `app`, `config`  |                                 Direktori yang diawasi                               |
|   watch.file   |      `.env`      |                                  File yang diawasi                                   |
| watch.interval |      `2000`      |                                Interval polling (ms)                                 |
|      ext       |  `.php`, `.env`  |                     Ekstensi file di dalam direktori yang diawasi                    |

## Dukungan Driver

|                Driver                 |                 Catatan                 |
| :-----------------------------------: | :-------------------------------------: |
| Hyperf\Watcher\Driver\ScanFileDriver  |        tidak memerlukan ekstensi        |
|  Hyperf\Watcher\Driver\FswatchDriver  |            memerlukan fswatch           |
|   Hyperf\Watcher\Driver\FindDriver    | memerlukan find, MAC memerlukan gfind |
| Hyperf\Watcher\Driver\FindNewerDriver |             memerlukan find             |

### Instalasi `fswatch`

Mac:

```bash
brew install fswatch
```

Ubuntu/Debian

```bash
apt-get install fswatch
```

Linux:

```bash
wget https://github.com/emcrisostomo/fswatch/releases/download/1.14.0/fswatch-1.14.0.tar.gz \
&& tar -xf fswatch-1.14.0.tar.gz \
&& cd fswatch-1.14.0/ \
&& ./configure \
&& make \
&& make install
```

## Memulai (Startup)

Karena struktur direktori, perintah startup harus dijalankan di direktori
root proyek.

```bash
php bin/hyperf.php server:watch
```

## Startup dengan Docker

Saat mengonfigurasi file watcher untuk hot-reloading di Docker, tentukan
entrypoint pada Dockerfile sebagai berikut:

```bash
ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "server:watch"]
```

## Masalah

- Untuk saat ini, ada sedikit masalah di lingkungan Docker Alpine, yang akan
  diperbaiki pada versi mendatang.
- Penghapusan file dan perubahan pada `.env` memerlukan restart manual agar
  dapat diterapkan.
