# Deployment Supervisor

[Supervisor](http://www.supervisord.org/) adalah alat manajemen proses untuk sistem `Linux/Unix`. Ia dapat memonitor, memulai, menghentikan, dan me-restart satu atau lebih proses dengan mudah. Proses yang dikelola [Supervisor](http://www.supervisord.org/) akan otomatis di-restart jika tidak sengaja di-`Killed`, sehingga kita bisa mencapai pemulihan proses otomatis tanpa perlu menulis script `shell` sendiri.

## Menginstal Supervisor

Berikut adalah contoh metode instalasi di `CentOS`:

```bash
# Install sumber epel; lewati langkah ini jika sudah diinstal sebelumnya
yum install -y epel-release
yum install -y supervisor  
```

## Membuat File Konfigurasi

```bash
cp /etc/supervisord.conf /etc/supervisord.d/supervisord.conf
```

Edit file konfigurasi yang baru saja disalin `/etc/supervisord.d/supervisord.conf`, dan tambahkan konten berikut di akhir file sebelum menyimpan:

```ini
# Buat aplikasi baru dan atur nama, di sini diatur ke hyperf
[program:hyperf]
# Atur perintah yang akan dijalankan di direktori yang ditentukan
directory=/var/www/hyperf/
# Ini adalah perintah startup untuk proyek yang ingin Anda kelola
command=php ./bin/hyperf.php start
# Sebagai user mana proses ini akan dijalankan
user=root
# Mulai otomatis aplikasi ini ketika supervisor dijalankan
autostart=true
# Restart otomatis proses setelah keluar
autorestart=true
# Berapa lama proses harus berjalan untuk dianggap berhasil dimulai
startsecs=1
# Jumlah percobaan ulang
startretries=3
# Lokasi output log stderr
stderr_logfile=/var/www/hyperf/runtime/stderr.log
# Lokasi output log stdout
stdout_logfile=/var/www/hyperf/runtime/stdout.log
```

!> Disarankan untuk meningkatkan nilai `minfds` di file konfigurasi, yang default-nya adalah `1024`. Anda juga harus memodifikasi [ulimit](https://wiki.swoole.com/#/other/sysctl?id=ulimit-settings) sistem untuk mencegah masalah `Failed to open stream: Too many open files`.

## Memulai Supervisor

Jalankan perintah berikut untuk memulai program Supervisor berdasarkan file konfigurasi:

```bash
supervisord -c /etc/supervisord.d/supervisord.conf
```

## Menggunakan supervisorctl untuk Mengelola Proyek

```bash
# Mulai aplikasi hyperf
supervisorctl start hyperf
# Restart aplikasi hyperf
supervisorctl restart hyperf
# Hentikan aplikasi hyperf
supervisorctl stop hyperf  
# Lihat status berjalan dari semua proyek yang dikelola
supervisorctl status
# Muat ulang file konfigurasi
supervisorctl update
# Restart semua program
supervisorctl reload
```
