# Alibaba Cloud Log Service

Mengumpulkan log bisa menjadi masalah yang merepotkan ketika men-deploy proyek
dalam `Docker cluster`, namun Alibaba Cloud menyediakan `log collection system`
yang sangat berguna. Dokumen ini secara singkat memperkenalkan cara menggunakan
pengumpulan log Alibaba Cloud.

* [Membangun cluster Docker Swarm](id/tutorial/docker-swarm.md)

## Mengaktifkan Layanan Log

Langkah pertama adalah mengaktifkan `Log Service` pada Alibaba Cloud.

[Dokumentasi Log Service](https://help.aliyun.com/product/28958.html)

Tutorial berikut adalah panduan langkah demi langkah yang berurutan tentang cara
menggunakan layanan log.

## Menginstal kontainer Logtail

[Dokumen proses pengumpulan log Docker standar](https://help.aliyun.com/document_detail/66659.html)

| Parameter | Deskripsi |
| :-----------------------------------: | :-------------------------------------------: |
| ${your_region_name} | Region ID, misalnya wilayah East China 1 adalah cn-hangzhou |
| ${your_aliyun_user_id} | User ID, silakan ganti dengan ID pengguna akun utama Alibaba Cloud Anda. |
| ${your_machine_group_user_defined_id} | ID kustom grup mesin dari cluster. Berikut ini menggunakan Hyperf |

```
docker run -d -v /:/logtail_host:ro -v /var/run/docker.sock:/var/run/docker.sock \
--env ALIYUN_LOGTAIL_CONFIG=/etc/ilogtail/conf/${your_region_name}/ilogtail_config.json \
--env ALIYUN_LOGTAIL_USER_ID=${your_aliyun_user_id} \
--env ALIYUN_LOGTAIL_USER_DEFINED_ID=${your_machine_group_user_defined_id} \
registry.cn-hangzhou.aliyuncs.com/log-service/logtail
```

## Mengonfigurasi Pengumpulan Log

### Membuat Project

Masuk ke Alibaba Cloud Log Service, klik `Create Project`, dan isi informasi berikut

| Parameter | Contoh pengisian |
| :------------: | :------------------: |
| Project name | hyperf |
| Comments | Untuk demonstrasi sistem log |
| Region | East China 1 (Hangzhou) |
| Activate service | Detailed log |
| Log Storage Location | Current Project |

### Membuat Logstore

Kecuali untuk parameter berikut, isi sesuai kebutuhan, yang lain dapat menggunakan nilai default

| Parameter | Contoh pengisian |
| :------------: | :-------------: |
| Logstore name | hyperf-demo-api |
| save permanently | false |
| Data retention time | 60 |

### Mengakses Data

1. Pilih Docker file

2. Buat grup mesin

Jika Anda sudah membuat grup mesin, Anda dapat melewati langkah ini

| Parameter | Contoh pengisian |
| :------------: | :------------: |
| Machine Group Name | Hyperf |
| Machine group ID | User-defined ID |
| User Defined Logo | Hyperf |

3. Konfigurasikan grup mesin

Terapkan grup mesin yang baru saja Anda buat

4. Konfigurasikan Logtail

Whitelist `Label`, di sini Anda dapat mengisi sesuai kebutuhan, berikut ini
dikonfigurasi sesuai dengan nama proyek, dan nama proyek akan diatur saat
kontainer Docker berjalan.

| Parameter | Contoh pengisian | Contoh pengisian |
| :------------: | :------------------------------------------------: | :-------------: |
| Configuration Name | hyperf-demo-api | |
| Log Path | /opt/www/runtime/logs | *.log |
| Label whitelist | app.name | hyperf-demo-api |
| Pattern | Full Regular Pattern | |
| single-line mode | false | |
| Sample log | `[2019-03-07 11:58:57] hyperf.WARNING: xxx` | |
| First line regular expression | `\[\d+-\d+-\d+\s\d+:\d+:\d+\]\s.*` | |
| Extract fields | true | |
| Regular Expression | `\[(\d+-\d+-\d+\s\d+:\d+:\d+)\]\s(\w+)\.(\w+):(.*)` | |
| Log extraction content | time name level content | |

5. Konfigurasi analisis kueri

properti indeks field

| Field name | Type | Alias | Chinese word segmentation | Open statistics |
| :------: | :---: | :-----: | :------: | :------: |
| name | text | name | false | true |
| level | text | level | false | true |
| time | text | time | false | false |
| content | text | content | true | false |

### Menjalankan Image

Saat menjalankan image, Anda hanya perlu mengatur `labels` Kontainer.

| name | value |
| :------: | :-------------: |
| app.name | hyperf-demo-api |

Misalnya Dockerfile berikut

```Dockerfile
# Default Dockerfile

FROM hyperf/hyperf:7.4-alpine-v3.11-swoole
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="hyperf-demo-api"

#Other content omitted
```

## Hal yang Perlu Diperhatikan

- Batasan driver penyimpanan Docker: Saat ini, hanya `overlay` dan `overlay2`
  yang didukung. Untuk driver penyimpanan lainnya, Anda perlu melakukan `mount`
  pada direktori tempat log berada, lalu mengumpulkan log dari host
  `~/logtail_host/your_path` sebagai gantinya.
