# Pengantar Panduan

Untuk membantu para developer membuat komponen untuk Hyperf dengan lebih baik dan membangun ekosistem bersama, kami menyediakan panduan ini. Sebelum membaca, Anda harus **telah membaca secara menyeluruh** dokumentasi Hyperf, terutama bab [Coroutine](../coroutine.md) dan [Dependency Injection](../di.md). Kurangnya pemahaman yang memadai tentang komponen dasar Hyperf dapat menyebabkan kesalahan selama pengembangan.

# Tujuan Pengembangan Komponen

Dalam pengembangan tradisional dengan arsitektur PHP-FPM, ketika perlu menggunakan library pihak ketiga, biasanya kita menambahkan `Library` yang sesuai melalui Composer. Namun di Hyperf, karena sifat `persistent application` dan `coroutine`, siklus hidup dan mode aplikasinya berbeda. Oleh karena itu, tidak semua `Library` bisa langsung digunakan di Hyperf, meskipun beberapa `Library` yang dirancang dengan baik memang bisa langsung dipakai. Dengan membaca panduan ini secara menyeluruh, Anda akan tahu cara membedakan apakah suatu `Library` bisa langsung digunakan dan, jika tidak, modifikasi apa yang diperlukan.

# Persiapan Pengembangan Komponen

Persiapan pengembangan yang dimaksud di sini, selain kondisi dasar menjalankan Hyperf, lebih berfokus pada cara mengatur struktur kode dengan lebih nyaman untuk memfasilitasi pengembangan komponen. Perhatikan bahwa metode berikut mungkin tidak cocok untuk lingkungan pengembangan Windows untuk Docker karena *masalah dengan soft link traversal*.

Mengenai pengaturan kode, kami menyarankan untuk meng-clone project skeleton [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) dan proyek library komponen [hyperf/hyperf](https://github.com/hyperf/hyperf) di direktori yang sama. Lakukan operasi berikut untuk mencapai struktur di bawah ini:

```bash
// Install skeleton dan selesaikan konfigurasi
composer create-project hyperf/hyperf-skeleton 

// Clone proyek library komponen hyperf, ingat untuk mengganti hyperf dengan Github ID Anda, yaitu clone proyek yang Anda fork
git clone git@github.com:hyperf/hyperf.git
```

Hasilkan struktur berikut:

```
.
├── hyperf
│   ├── bin
│   └── src
└── hyperf-skeleton
    ├── app
    ├── bin
    ├── config
    ├── runtime
    ├── test
    └── vendor
```

Tujuannya agar proyek `hyperf-skeleton` bisa memuat proyek-proyek di folder `hyperf` sebagai dependensi ke direktori `vendor` melalui bentuk sumber `path`. Kita tambahkan item `repositories` ke file `composer.json` di `hyperf-skeleton`, sebagai berikut:

```json
{
    "repositories": {
        "hyperf": {
            "type": "path",
            "url": "../hyperf/src/*"
        }
    }
}
```

Kemudian, hapus file `composer.lock` dan folder `vendor` di proyek `hyperf-skeleton`, dan jalankan `composer update` untuk memperbarui dependensi lagi. Perintahnya sebagai berikut:

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```

Pada akhirnya, ini memastikan bahwa semua folder proyek di dalam `hyperf-skeleton/vendor/hyperf` terhubung ke folder `hyperf` melalui `soft links`. Kita dapat memverifikasi apakah `soft links` telah berhasil dibuat menggunakan perintah `ls -l`:

```bash
cd vendor/hyperf/
ls -l
```

Ketika kita melihat hubungan koneksi yang mirip dengan di bawah ini, itu menandakan bahwa `soft links` telah berhasil dibuat:

```
cache -> ../../../hyperf/src/cache
command -> ../../../hyperf/src/command
config -> ../../../hyperf/src/config
contract -> ../../../hyperf/src/contract
database -> ../../../hyperf/src/database
db-connection -> ../../../hyperf/src/db-connection
devtool -> ../../../hyperf/src/devtool
di -> ../../../hyperf/src/di
dispatcher -> ../../../hyperf/src/dispatcher
event -> ../../../hyperf/src/event
exception-handler -> ../../../hyperf/src/exception-handler
framework -> ../../../hyperf/src/framework
guzzle -> ../../../hyperf/src/guzzle
http-message -> ../../../hyperf/src/http-message
http-server -> ../../../hyperf/src/http-server
logger -> ../../../hyperf/src/logger
memory -> ../../../hyperf/src/memory
paginator -> ../../../hyperf/src/paginator
pool -> ../../../hyperf/src/pool
process -> ../../../hyperf/src/process
redis -> ../../../hyperf/src/redis
server -> ../../../hyperf/src/server
testing -> ../../../hyperf/src/testing
support -> ../../../hyperf/src/support
```

Pada titik ini, kita bisa memodifikasi file di `vendor/hyperf` langsung dari IDE, tapi sebenarnya yang berubah adalah kode di `hyperf`. Dengan cara ini, kita bisa langsung `commit` di proyek `hyperf` dan mengirimkan `Pull Request (PR)` ke branch utama.
