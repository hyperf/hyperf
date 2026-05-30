# Pengantar Panduan

Untuk membantu developer mengembangkan komponen Hyperf dengan lebih baik dan
membangun ekosistem bersama, kami menyediakan panduan ini sebagai panduan dalam
pengembangan komponen. Sebelum membaca panduan ini, Anda perlu membaca
dokumentasi Hyperf secara **menyeluruh**, terutama bab [coroutine](id/coroutine.md)
dan [Dependency Injection](id/di.md). Jika Anda kurang memahami komponen dasar
Hyperf, hal itu dapat menyebabkan kesalahan selama pengembangan.

# Tujuan Pengembangan Komponen

Dalam pengembangan di bawah arsitektur PHP-FPM tradisional, biasanya ketika kita
perlu menggunakan library pihak ketiga untuk menyelesaikan kebutuhan kita, kita
akan langsung memasukkan library yang sesuai melalui Composer. Namun, di bawah
Hyperf, karena dua karakteristik yaitu `persistent application` dan `coroutine`
menyebabkan beberapa perbedaan dalam siklus hidup (life cycle) dan mode aplikasi,
sehingga tidak semua `library` dapat digunakan secara langsung di Hyperf. Tentu
saja, beberapa `library` yang dirancang dengan baik juga dapat digunakan secara
langsung. Setelah membaca panduan ini, Anda akan mengetahui cara mengidentifikasi
apakah suatu `library` dapat digunakan secara langsung dalam proyek, dan
bagaimana cara melakukan perubahan jika tidak bisa.

# Persiapan Pengembangan Komponen

Persiapan pengembangan yang dimaksud di sini, selain kondisi pengoperasian dasar
Hyperf, lebih berfokus pada cara mengatur struktur kode dengan lebih mudah untuk
memfasilitasi pengembangan komponen. Perlu dicatat bahwa metode berikut mungkin
tidak dapat berjalan (jump) karena *masalah softlink (softlink issue)* dan tidak
berlaku untuk lingkungan pengembangan di bawah Windows untuk Docker.

Dalam hal pengorganisasian kode, kami menyarankan untuk melakukan clone pada
proyek skeleton [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton)
dan proyek library komponen [hyperf/hyperf](https://github.com/hyperf/hyperf).
Lakukan hal berikut untuk mendapatkan struktur seperti di bawah ini:

```bash
// Install the skeleton and configure it
composer create-project hyperf/hyperf-skeleton

// Clone the hyperf component library project, remember to replace hyperf with your Github ID, that is, clone the project you forked
git clone git@github.com:hyperf/hyperf.git
```

Strukturnya adalah sebagai berikut:

```
.
├── hyperf
│ ├── bin
│ └── src
└── hyperf-skeleton
     ├── app
     ├── bin
     ├──config
     ├── runtime
     ├── test
     └── vendor
```

Tujuannya adalah agar proyek `hyperf-skeleton` dapat langsung mengambil sumber
melalui bentuk `path`, sehingga Composer dapat langsung memuat proyek di dalam
folder `hyperf` sebagai dependensi ke dalam direktori `vendor` dari proyek
`hyperf-skeleton`. Kita menambahkan item `repositories` ke file `composer.json`
di dalam `hyperf-skeleton`, sebagai berikut:

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

Kemudian hapus file `composer.lock` dan folder `vendor` di dalam proyek
`hyperf-skeleton`, lalu jalankan `composer update` untuk memperbarui dependensi
kembali. Perintahnya adalah sebagai berikut:

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```

Akhirnya, semua folder proyek di dalam folder `hyperf-skeleton/vendor/hyperf`
akan terhubung ke folder `hyperf` melalui `softlink`. Kita dapat menggunakan
perintah `ls -l` untuk memverifikasi apakah `softlink` telah berhasil dibuat:

```bash
cd vendor/hyperf/
ls -l
```

Ketika kita melihat hubungan koneksi seperti berikut, itu berarti `softlink`
telah berhasil dibuat:

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

Pada titik ini, kita dapat langsung memodifikasi file di dalam `vendor/hyperf`
melalui IDE, tetapi yang kita modifikasi sebenarnya adalah kode di dalam
`hyperf`, sehingga pada akhirnya kita dapat langsung melakukan `commit` pada
proyek `hyperf`, lalu mengirimkan `Pull Request (PR)` ke trunk utama.
