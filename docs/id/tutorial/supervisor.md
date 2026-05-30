# Deployment Aplikasi dengan Supervisor

[Supervisor](http://www.supervisord.org/) adalah alat process management untuk
sistem Linux/Unix. Satu atau lebih process dapat dengan mudah dipantau,
dijalankan, dihentikan, dan dijalankan ulang. Ketika process yang dikelola
oleh Supervisor terhenti secara tidak sengaja (ter-kill), Supervisor secara
otomatis akan menjalankannya kembali. Hal ini sangat memudahkan untuk mencapai
pemulihan process secara otomatis tanpa harus menulis shell script untuk
mengelola process tersebut.

## Instalasi Supervisor

Berikut adalah contoh metode instalasi pada sistem CentOS:

```bash
# Install the epel source, if it has been installed before, skip this step
yum install -y epel-release
yum install -y supervisor  
```

## Membuat File Konfigurasi

```bash
cp /etc/supervisord.conf /etc/supervisord.d/supervisord.conf
```

Edit file konfigurasi yang baru disalin `/etc/supervisord.d/supervisord.conf`
dan simpan file setelah menambahkan konfigurasi berikut di bagian akhir file:

```ini
# Create a new application and set a name, here is set to hyperf
[program:hyperf]
# Here is the startup command of the project you want to manage, corresponding to the real path of your project
command=php /var/www/hyperf/bin/hyperf.php start
# Which user to run the process as
user=root
# automatically the app when supervisor starts
autostart=true
# Automatically restart the process after the process exits
autorestart=true
# retry interval in seconds
startsecs=5
# number of retries
startretries=3
# stderr log output location
stderr_logfile=/var/www/hyperf/runtime/stderr.log
# stdout log output location
stdout_logfile=/var/www/hyperf/runtime/stdout.log
```

## Menjalankan Supervisor

Jalankan perintah berikut untuk memulai program Supervisor berdasarkan file
konfigurasi:

```bash
supervisord -c /etc/supervisord.d/supervisord.conf
```

## Menggunakan `supervisorctl` untuk Mengelola Aplikasi

```bash
# start the hyperf application
supervisorctl start hyperf
# restart hyperf application
supervisorctl restart hyperf
# stop hyperf application
supervisorctl stop hyperf
# View the running status of all managed projects
supervisorctl status
```
