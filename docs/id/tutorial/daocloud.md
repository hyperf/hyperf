# Setup DaoCloud DevOps

Sebagai pengembang individu, biaya membangun `Gitlab` dan `Docker Swarm Cluster` sendiri jelas tidak masuk akal. Di sinilah layanan `DevOps` bernama `DaoCloud` hadir.

Alasan merekomendasikannya sederhana: gratis dan berfungsi dengan baik.

[DaoCloud](https://dashboard.daocloud.io)

## Cara Menggunakan

Anda hanya perlu fokus pada tiga tab: `Projects`, `Applications`, dan `Cluster Management`.

### Membuat Project

Pertama, kita perlu membuat project baru di `Projects`. DaoCloud mendukung berbagai image registry, yang dapat dipilih sesuai kebutuhan Anda.

Di sini, saya akan menggunakan repository [hyperf-demo](https://github.com/limingxinleo/hyperf-demo) sebagai contoh untuk konfigurasi. Ketika pembuatan berhasil, URL yang sesuai akan muncul di bawah `WebHooks` dari `Github repository` yang sesuai.

Selanjutnya, modifikasi `Dockerfile` di repository dan tambahkan `&& apk add wget \` di bawah `apk add`. Alasannya kurang jelas, tapi jika `wget` tidak ditambahkan, akan muncul masalah saat digunakan. Namun, Gitlab CI bawaan tidak memiliki masalah serupa.

Setelah setiap pengiriman kode, `DaoCloud` akan menjalankan operasi packaging yang sesuai untuk proyek yang Anda buat.

### Membuat Cluster

Kemudian kita pergi ke `Cluster Management`, buat `Cluster`, dan tambahkan `Host`.

Saya tidak akan menjelaskan secara detail di sini; ikuti langkah-langkahnya satu per satu seperti yang diinstruksikan di atas.

### Membuat Application

Klik Applications -> Create Application -> Pilih proyek yang baru dibuat (wajib sudah pernah push kode minimal sekali, dan `DaoCloud` sudah menghasilkan image) -> Deploy.

Ikuti petunjuknya. Anda bisa memilih port yang tidak terpakai untuk host. Karena `DaoCloud` tidak memiliki fitur `Config` seperti `Swarm`, kita perlu memetakan file `.env` ke container secara manual.

Tambahkan `Volume`: direktori container `/opt/www/.env`, direktori host diisi dengan lokasi file `.env`, dan atur sebagai read-only.

Kemudian klik "Deploy Now".

### Pengujian

Akses port yang baru saja digunakan di host, dan Anda akan melihat data antarmuka selamat datang dari `Hyperf`.

```bash
$ curl http://127.0.0.1:9501
{"code":0,"data":{"user":"Hyperf","method":"GET","message":"Hello Hyperf."}}
```
