# Tutorial Build DevOps DaoCloud

Sebagai developer individu, biaya penggunaan `Gitlab` dan `Docker Swarm cluster`
yang dibangun sendiri tentu saja tidak dapat diterima. Berikut adalah layanan
`Devops` bernama `DaoCloud`.

Alasan rekomendasinya sederhana, karena layanan ini gratis dan berfungsi dengan
baik.

[DaoCloud](https://dashboard.daocloud.io)

## Cara Penggunaan

Anda hanya perlu memperhatikan tiga halaman yaitu `project`, `application`, dan
`cluster management`.

### Membuat Project

Pertama, kita perlu membuat project baru di `projects`. DaoCloud mendukung
berbagai repositori mirror, yang dapat dipilih sesuai kebutuhan.

Di sini saya menggunakan repositori
[hyperf-demo](https://github.com/limingxinleo/hyperf-demo) sebagai contoh untuk
konfigurasi. Ketika pembuatan berhasil, akan ada URL yang sesuai di bawah
`WebHooks` pada `Github repository`.

Selanjutnya, mari kita modifikasi `Dockerfile` di dalam repositori dan
tambahkan `&& apk add wget \` di bawah `apk add`. Alasan spesifiknya di sini
kurang begitu jelas, jika Anda tidak memperbarui `wget`, akan ada masalah saat
menggunakannya. Namun tidak ada masalah jika menggunakan Gitlab CI yang
dibangun sendiri.

Ketika kode di-submit, `DaoCloud` akan melakukan operasi pengemasan (packaging)
yang sesuai.

### Membuat Cluster

Kemudian kita pergi ke `cluster management`, buat sebuah `cluster`, dan
tambahkan `hosts`.

Saya tidak akan membahas detailnya di sini, cukup ikuti langkah-langkah di atas.

### Membuat Application

Klik Apply -> Create Application -> Pilih project tadi -> Deploy

Sesuai petunjuk, untuk port host, pengguna dapat memilih port yang tidak
digunakan. Karena `DaoCloud` tidak memiliki fungsi `Config` dari `Swarm`, kita
memetakan (map) `.env` ke kontainer secara aktif.

Tambahkan `Volume`, direktori kontainer `/opt/www/.env`, direktori host
gunakan alamat tempat Anda menyimpan file `.env`, baik itu dapat ditulis
(writable) atau tidak.

Kemudian klik Deploy Now.

### Pengujian

Buka host untuk mengakses nomor port tadi, dan Anda dapat melihat data tampilan
selamat datang dari `Hyperf`.

```
$ curl http://127.0.0.1:9501
{"code":0,"data":{"user":"Hyperf","method":"GET","message":"Hello Hyperf."}}
```
