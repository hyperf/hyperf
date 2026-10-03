# Hot Update Watcher

> Pertama kali dijalankan akan relatif lambat karena belum ada cache. Kedua kali dijalankan, pengumpulan file dilakukan secara dinamis berdasarkan waktu modifikasi, sehingga waktu startup masih cukup lama.

Selain mengatasi masalah startup di atas, komponen `Watcher` juga menyediakan fungsi restart segera setelah modifikasi file.

## Instalasi

```bash
composer require hyperf/watcher --dev
```

## Konfigurasi

### Publikasi konfigurasi

```bash
php bin/hyperf.php vendor:publish hyperf/watcher
```

### Deskripsi konfigurasi

| Konfigurasi | Nilai Default | Deskripsi |
| :---------: | :-----------: | :--------------------------------------------------------: |
| driver      | `ScanFileDriver` | Driver pemindaian file terjadiwal default |
| bin         | `PHP_BINARY`  | Skrip untuk memulai service, mis., `php -d swoole.use_shortname=Off` |
| watch.dir   | `app`, `config` | Direktori yang dipantau |
| watch.file  | `.env`        | File yang dipantau |
| watch.interval | `2000`      | Interval pemindaian (milidetik) |
| ext         | `.php`, `.env` | Ekstensi file di bawah direktori yang dipantau |

## Driver yang Didukung

| Driver                                     | Deskripsi                           |
| :----------------------------------------: | :---------------------------------: |
| Hyperf\Watcher\Driver\ScanFileDriver       | Tidak memerlukan ekstensi           |
| Hyperf\Watcher\Driver\FswatchDriver        | Perlu menginstal fswatch            |
| Hyperf\Watcher\Driver\FindDriver           | Perlu menginstal find, gfind di MAC |
| Hyperf\Watcher\Driver\FindNewerDriver      | Perlu menginstal find               |

### Instalasi `fswatch`

Mac

```bash
brew install fswatch
```

Ubuntu/Debian

```bash
apt-get install fswatch
```

Lainnya

```bash
wget https://github.com/emcrisostomo/fswatch/releases/download/1.14.0/fswatch-1.14.0.tar.gz \
&& tar -xf fswatch-1.14.0.tar.gz \
&& cd fswatch-1.14.0/ \
&& ./configure \
&& make \
&& make install
```

## Menjalankan

Karena alasan direktori, perlu dijalankan dari direktori root proyek.

```bash
php bin/hyperf.php server:watch
```

## Keterbatasan

- Saat ini, ada beberapa masalah kecil di lingkungan Alpine Docker yang akan diperbaiki kemudian.
- Menghapus file dan memodifikasi `.env` memerlukan restart manual agar dapat diterapkan.
