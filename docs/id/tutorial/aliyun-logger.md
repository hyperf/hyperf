# Alibaba Cloud Log Service

Saat men-deploy proyek di cluster `Docker Swarm`, pengumpulan log bisa jadi rumit. Untungnya, Alibaba Cloud menyediakan `Log Collection System` yang sangat praktis. Dokumen ini akan memperkenalkan cara menggunakannya.

* [Pengaturan Cluster Docker Swarm](docker-swarm.md)

## Mengaktifkan Log Service

Langkah pertama adalah mengaktifkan `Log Service` di Alibaba Cloud.

[Dokumentasi Log Service](https://help.aliyun.com/product/28958.html)

Berikut adalah panduan langkah demi langkah menggunakan Log Service.

## Menginstal Container Logtail

[Dokumentasi Proses Pengumpulan Log Docker Standar](https://help.aliyun.com/document_detail/66659.html)

| Parameter | Deskripsi |
| :---: | :---: |
| ${your_region_name} | ID Region, misalnya region East China 1 adalah cn-hangzhou |
| ${your_aliyun_user_id} | Identifikasi pengguna, ganti dengan ID akun utama Alibaba Cloud Anda. |
| ${your_machine_group_user_defined_id} | Identifikasi kustom untuk machine group cluster. Hyperf digunakan di bawah ini. |

```
docker run -d -v /:/logtail_host:ro -v /var/run/docker.sock:/var/run/docker.sock \
--env ALIYUN_LOGTAIL_CONFIG=/etc/ilogtail/conf/${your_region_name}/ilogtail_config.json \
--env ALIYUN_LOGTAIL_USER_ID=${your_aliyun_user_id} \
--env ALIYUN_LOGTAIL_USER_DEFINED_ID=${your_machine_group_user_defined_id} \
registry.cn-hangzhou.aliyuncs.com/log-service/logtail
```

## Mengonfigurasi Pengumpulan Log

### Membuat Project

Masuk ke Alibaba Cloud Log Service, klik `Create Project`, dan isi informasi berikut:

| Parameter | Contoh |
| :---: | :---: |
| Nama Project | hyperf |
| Deskripsi | Digunakan untuk demonstrasi sistem log |
| Region | East China 1 (Hangzhou) |
| Aktifkan Layanan | Detailed Logs |
| Lokasi Penyimpanan Log | Current Project |

### Membuat Logstore

Selain parameter berikut, isi sesuai kebutuhan dan biarkan sisanya default.

| Parameter | Contoh |
| :---: | :---: |
| Nama Logstore | hyperf-demo-api |
| Permanent Storage | false |
| Waktu Retensi Data | 60 |

### Mengakses Data

1. Pilih Docker File

2. Buat Machine Group

Jika Anda sudah membuat machine group, Anda dapat melewati langkah ini.

| Parameter | Contoh |
| :---: | :---: |
| Nama Machine Group | Hyperf |
| Identifikasi Machine Group | User-defined identifier |
| User-defined Identifier | Hyperf |

3. Konfigurasi Machine Group

Terapkan machine group yang baru saja dibuat.

4. Konfigurasi Logtail

Whitelist `Label` bisa diisi sesuai kebutuhan. Konfigurasikan sesuai nama proyek di bawah; nama proyek akan ditentukan saat container Docker berjalan.

| Parameter | Contoh | Contoh |
| :---: | :---: | :---: |
| Nama Konfigurasi | hyperf-demo-api | |
| Path Log | /opt/www/runtime/logs | *.log |
| Label Whitelist | app.name | hyperf-demo-api |
| Mode | Full Regex Mode | |
| Single-line Mode | false | |
| Contoh Log | `[2019-03-07 11:58:57] hyperf.WARNING: xxx` | |
| First-line Regex | `\[\d+-\d+-\d+\s\d+:\d+:\d+\]\s.*` | |
| Extract Fields | true | |
| Regex | `\[(\d+-\d+-\d+\s\d+:\d+:\d+)\]\s(\w+)\.(\w+):(.*)` | |
| Konten Ekstraksi Log | time name level content | |

5. Konfigurasi Analisis Query

Atribut Index Field

| Nama Field | Tipe | Alias | Chinese Word Segmentation | Aktifkan Statistik |
| :---: | :---: | :---: | :---: | :---: |
| name | text | name | false | true |
| level | text | level | false | true |
| time | text | time | false | false |
| content | text | content | true | false |

### Menjalankan Image

Saat menjalankan image, cukup atur `labels` Container.

| name | value |
| :---: | :---: |
| app.name | hyperf-demo-api |

Contoh, Dockerfile berikut:

```Dockerfile
# Default Dockerfile

FROM hyperf/hyperf:7.4-alpine-v3.11-swoole
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="hyperf-demo-api"

# Konten lainnya diabaikan
```

## Catatan

- Batasan driver storage Docker: saat ini hanya `overlay` dan `overlay2` yang didukung. Untuk driver lain, Anda perlu me-`mount` direktori log ke mesin lokal, lalu kumpulkan log dari `~/logtail_host/your_path` pada host.
