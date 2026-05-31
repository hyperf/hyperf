# Setup Cluster Docker Swarm

Saat ini, teknologi container sudah cukup matang. Bahkan perusahaan kecil dan menengah pun dapat dengan mudah membangun layanan cluster Docker mereka sendiri berdasarkan Gitlab, layanan image registry Aliyun, dan Docker Swarm.

## Menginstal Docker

```bash
curl -sSL https://get.docker.com/ | sh
```

Modifikasi file `/lib/systemd/system/docker.service` untuk mengizinkan koneksi `TCP` ke `Docker`:

> Cukup tambahkan `-H tcp://0.0.0.0:2375` di bagian akhir.

```
ExecStart=/usr/bin/dockerd -H fd:// --containerd=/run/containerd/containerd.sock -H tcp://0.0.0.0:2375
```

Jika Anda tidak menggunakan akun `root`, Anda dapat menggunakan perintah berikut agar tidak perlu menambahkan `sudo` setiap kali menjalankan `docker`:

```bash
usermod -aG docker $USER
```

### Mengonfigurasi Alamat Mirror Registry

Karena kecepatan akses yang lambat akibat rute lintas negara, kita dapat mengonfigurasi alamat mirror registry untuk Docker untuk mengatasi masalah jaringan ini. Misalnya, [Aliyun Docker Image Accelerator](https://help.aliyun.com/document_detail/60750.html). Kita dapat mengajukan akselerator `Docker` dan kemudian mengonfigurasinya di file `/etc/docker/daemon.json` di server. Tambahkan konten berikut, lalu restart `Docker`. Silakan isi alamat akselerator yang Anda peroleh di bawah ini.

```json
{"registry-mirrors": ["https://xxxxx.mirror.aliyuncs.com"]}
```

## Menyiapkan Layanan Gitlab

### Menginstal Gitlab

#### Memodifikasi Nomor Port SSHD Default

Pertama, kita perlu memodifikasi port layanan `sshd` server, mengubah port default `22` menjadi `2222` (atau port lain yang tidak terpakai), sehingga `gitlab` dapat menggunakan port `22` untuk koneksi `ssh`.

```bash
$ vim /etc/ssh/sshd_config

# Ubah Port default menjadi 2222
Port 2222

# Restart layanan
$ systemctl restart sshd.service
```

Login ulang ke mesin:

```bash
ssh -p 2222 root@host
```

#### Menginstal Gitlab

Kita menjalankan layanan Gitlab melalui Docker, sebagai berikut:

> Hostname harus ditambahkan; jika tidak ada domain, Anda dapat mengisi alamat IP publik secara langsung.

```bash
sudo docker run -d --hostname gitlab.xxx.cn \
--publish 443:443 --publish 80:80 --publish 22:22 \
--name gitlab --restart always --volume /srv/gitlab/config:/etc/gitlab \
--volume /srv/gitlab/logs:/var/log/gitlab \
--volume /srv/gitlab/data:/var/opt/gitlab \
gitlab/gitlab-ce:latest
```

Nama pengguna default adalah `root`, dan password awal diperoleh dengan cara berikut:

```shell
docker exec gitlab cat /etc/gitlab/initial_root_password
```

### Menginstal gitlab-runner

> Disarankan untuk men-deploy ini secara terpisah dari server `Gitlab` dan menggunakan server runner khusus.

Kita mengambil metode instalasi `CentOS` sebagai contoh. Untuk yang lain, silakan lihat [dokumentasi resmi Gitlab](https://docs.gitlab.com/runner/install/linux-repository.html).

```bash
curl -L https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.rpm.sh | sudo bash
yum install gitlab-runner
```

Tentu saja, Anda juga dapat menggunakan perintah `curl https://setup.ius.io | sh` untuk memperbarui ke sumber `git` terbaru, lalu menggunakan `yum` untuk menginstal `git` dan `gitlab-runner` secara langsung.

```bash
$ curl https://setup.ius.io | sh
$ yum -y install git2u
$ git version
$ yum install gitlab-runner
```

### Mendaftarkan gitlab-runner

Daftarkan `gitlab-runner` ke Gitlab menggunakan perintah `gitlab-runner register --clone-url http://your-ip/`. Perhatikan bahwa Anda harus mengganti `your-ip` dengan IP intranet Gitlab Anda, sebagai berikut:

```bash
$ sudo gitlab-runner register --clone-url http://your-ip/

Please enter the gitlab-ci coordinator URL (e.g. https://gitlab.com/):
http://gitlab.xxx.cc/
Please enter the gitlab-ci token for this runner:
xxxxx
Please enter the gitlab-ci description for this runner:
xxx
Please enter the gitlab-ci tags for this runner (comma separated):
builder
Please enter the executor: docker-ssh, shell, docker+machine, docker-ssh+machine, docker, parallels, ssh, virtualbox, kubernetes:
shell
```

### Memodifikasi Konkurensi gitlab-runner

```bash
$ vim /etc/gitlab-runner/config.toml
concurrent = 5
```

### Menambahkan Izin untuk gitlab-runner

- Izin untuk menjalankan docker tanpa sudo:

```shell
sudo usermod -aG docker gitlab-runner
```

- Izin untuk image registry:

```shell
su gitlab-runner
docker login -u username your-docker-repository
```

### Memodifikasi Email

Jika Anda memerlukan `Gitlab` untuk mengirim email (misalnya, email pembuatan pengguna), Anda dapat mencoba memodifikasi `/srv/gitlab/config/gitlab.rb`:

```ruby
gitlab_rails['smtp_enable'] = true
gitlab_rails['smtp_address'] = "smtp.exmail.qq.com"
gitlab_rails['smtp_port'] = 465
gitlab_rails['smtp_user_name'] = "git@xxxx.com"
gitlab_rails['smtp_password'] = "xxxx"
gitlab_rails['smtp_authentication'] = "login"
gitlab_rails['smtp_enable_starttls_auto'] = true
gitlab_rails['smtp_tls'] = true
gitlab_rails['gitlab_email_from'] = 'git@xxxx.com'
gitlab_rails['smtp_domain'] = "exmail.qq.com"
```

## Menginisialisasi Swarm Cluster

### Login ke mesin lain dan inisialisasi cluster

```bash
$ docker swarm init
```

### Membuat Custom Overlay Network

Berikut adalah tiga cara untuk membuat network segment; Anda hanya perlu menjalankan salah satunya:

1. Buat langsung custom Overlay network:

```shell
docker network create \
--driver overlay \
--subnet 10.0.0.1/8 \
--opt encrypted \
--attachable \
default-network
```

2. Terkadang, karena terjadi konflik network segment, deployment stack bisa gagal. Anda dapat mencoba memodifikasi `--subnet`, tetapi dengan cara ini, network segment saat ini hanya mendukung 65.535 IP:

```shell
docker network create \
--driver overlay \
--subnet 10.1.0.1/16 \
--opt encrypted \
--attachable \
default-network
```

3. Tentu saja, karena network segment default dari jaringan `ingress` sering bertentangan dengan yang baru dibuat, kita dapat menghapus jaringan `ingress` dan membuat yang baru:

```shell
docker network rm ingress
docker network create --ingress --subnet 192.168.0.1/16 --driver overlay ingress
```

Kemudian buat `network` dengan `--subnet` diatur ke `10.0.0.1/8`:

```shell
docker network create \
--driver overlay \
--subnet 10.0.0.1/8 \
--opt encrypted \
--attachable \
default-network
```

### Bergabung ke Cluster

```bash
# Tampilkan TOKEN dari node manager
$ docker swarm join-token manager
# Gabungkan node manager ke cluster
$ docker swarm join --token <token> ip:2377

# Tampilkan TOKEN dari node worker
$ docker swarm join-token worker
# Gabungkan node worker ke cluster
$ docker swarm join --token <token> ip:2377
```

### Mengonfigurasi gitlab-runner untuk Publishing

> Lainnya sama dengan builder, tetapi tag tidak boleh sama. Lingkungan online dapat diatur ke `tags`, dan lingkungan pengujian diatur ke `test`.

## Menginstal Aplikasi Lain

Berikut contoh menggunakan `Mysql`. Service ini langsung menggunakan `network` di atas dan mendukung panggilan antar container menggunakan nama:

```bash
docker run --name mysql -v /srv/mysql:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=xxxx -p 3306:3306 --rm --network default-network -d mysql:5.7
```

## Menginstal Portainer

[Portainer](https://github.com/portainer/portainer)

```bash
docker service create \
    --name portainer \
    --publish 9000:9000 \
    --replicas=1 \
    --constraint 'node.role == manager' \
    --mount type=volume,src=portainer_data,dst=/data \
    --mount type=bind,src=//var/run/docker.sock,dst=/var/run/docker.sock \
    portainer/portainer
```

### Mencadangkan Data Portainer

> `portainer_container` adalah nama container yang sesuai; isi sesuai dengan situasi aktual.

```bash
docker run -it --volumes-from portainer_container -v $(pwd):/backup --name backup --rm nginx tar -cf /backup/data.tar /data/
```

### Mengembalikan Data Portainer

Pertama, gunakan perintah pembuatan untuk membuat ulang layanan portainer.

Kemudian, gunakan metode berikut untuk memuat ulang cadangan ke dalam container:

```bash
docker run -it --volumes-from portainer_container -v $(pwd):/backup --name importer --rm nginx bash
cd /backup
tar xf data.tar -C /
```

Terakhir, Anda hanya perlu me-restart container.

## Membuat Proyek Demo

Login ke Gitlab untuk membuat proyek Demo, dan impor proyek kami [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton).

## Mengonfigurasi Image Registry

> Kami akan menggunakan milik Aliyun secara langsung.

Pertama, buat namespace `test_namespace`, kemudian buat image repository `demo`, dan gunakan repository lokal.

Kemudian pergi ke server yang kami gunakan untuk packaging dan login ke Aliyun Docker Registry:

```bash
usermod -aG docker gitlab-runner
su gitlab-runner
docker login --username=your_name registry.cn-shanghai.aliyuncs.com
```

Modifikasi `.gitlab-ci.yml` di proyek kami:

```yaml
variables:
  PROJECT_NAME: demo
  REGISTRY_URL: registry.cn-shanghai.aliyuncs.com/test_namespace
```

Ada juga `deploy.test.yml`, yang perlu dibandingkan dengan cermat dengan file berikut.

```yaml
version: '3.7'
services:
  demo:
    image: $REGISTRY_URL/$PROJECT_NAME:test
    environment:
      - "APP_PROJECT=demo"
      - "APP_ENV=test"
    ports:
      - 9501:9501
    deploy:
      replicas: 1
      restart_policy:
        condition: on-failure
        delay: 5s
        max_attempts: 5
      update_config:
        parallelism: 2
        delay: 5s
        order: start-first
    networks:
      - default-network
    configs:
      - source: demo_v1.0
        target: /opt/www/.env
configs:
  demo_v1.0:
    external: true
networks:
  default-network:
    external: true
```

Kemudian di Portainer kita, buat Config `demo_v1.0` yang sesuai. Tentu saja, parameter berikut perlu disesuaikan dengan situasi aktual. Karena tidak ada operasi I/O di Demo kami, Anda dapat mengisi nilai default:

```
APP_NAME=demo

DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hyperf
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
DB_PREFIX=

REDIS_HOST=localhost
REDIS_AUTH=
REDIS_PORT=6379
REDIS_DB=0
```

Karena `gitlab-ci.yml` yang kami konfigurasi akan mendeteksi branch `test` dan `tags`, kami menggabungkan konten yang dimodifikasi ke dalam branch `test` dan kemudian mendorongnya ke gitlab.

Selanjutnya, kita dapat mengunjungi port `9501` dari mesin mana pun di cluster untuk menguji:

```bash
curl http://127.0.0.1:9501/
```

## Menginstal KONG Gateway

Biasanya, cluster Docker Swarm tidak langsung diekspos untuk akses, jadi kita dapat membangun layanan gateway di lapisan atas. `KONG` direkomendasikan sebagai gateway di sini.
Alasan lainnya adalah karena `Ingress network` Docker Swarm memiliki cacat desain. Dalam kasus koneksi yang tidak digunakan kembali, akan ada bottleneck konkurensi. Untuk detailnya, silakan periksa [Issue #35082](https://github.com/moby/moby/issues/35082) yang sesuai.
Sebagai layanan gateway, `KONG` akan menggunakan kembali koneksi backend secara default, sehingga akan sangat mengurangi masalah di atas.

### Menginstal Database

```bash
docker run -d --name kong-database \
  --network=default-network \
  -p 5432:5432 \
  -e "POSTGRES_USER=kong" \
  -e "POSTGRES_DB=kong" \
  -e "POSTGRES_PASSWORD=kong" \
  postgres:9.6
```

### Menginstal Gateway

Inisialisasi database:

```bash
docker run --rm \
  --network=default-network \
  -e "KONG_DATABASE=postgres" \
  -e "KONG_PG_HOST=kong-database" \
  -e "KONG_PG_PASSWORD=kong" \
  -e "KONG_CASSANDRA_CONTACT_POINTS=kong-database" \
  kong:latest kong migrations bootstrap
```

Mulai:

```bash
docker run -d --name kong \
  --network=default-network \
  -e "KONG_DATABASE=postgres" \
  -e "KONG_PG_HOST=kong-database" \
  -e "KONG_PG_PASSWORD=kong" \
  -e "KONG_CASSANDRA_CONTACT_POINTS=kong-database" \
  -e "KONG_PROXY_ACCESS_LOG=/dev/stdout" \
  -e "KONG_ADMIN_ACCESS_LOG=/dev/stdout" \
  -e "KONG_PROXY_ERROR_LOG=/dev/stderr" \
  -e "KONG_ADMIN_ERROR_LOG=/dev/stderr" \
  -e "KONG_ADMIN_LISTEN=0.0.0.0:8001, 0.0.0.0:8444 ssl" \
  -p 8000:8000 \
  -p 8443:8443 \
  -p 8001:8001 \
  -p 8444:8444 \
  kong:latest
```

### Menginstal KONG Dashboard

> Saat ini `Docker` belum memperbarui `v3.6.0`, jadi versi terbaru `KONG` mungkin tidak dapat digunakan. Anda dapat menggunakan KONG versi 0.14.1.

```bash
docker run --rm --network=default-network -p 8080:8080 -d --name kong-dashboard pgbi/kong-dashboard start \
  --kong-url http://kong:8001 \
  --basic-auth user1=password1 user2=password2
```

### Mengonfigurasi Service

Selanjutnya, Anda hanya perlu mengekspos `IP` dari mesin tempat gateway `KONG` di-deploy untuk akses, lalu mengonfigurasi `Service` yang sesuai.
Jika mesin langsung diekspos untuk akses, sebaiknya hanya buka port `80` dan `443`, lalu petakan port `8000` dan `8443` dari container `Kong` ke port `80` dan `443`.
Tentu saja, jika layanan load balancing seperti `SLB` digunakan, petakan langsung port `80` dan `443` ke port `8000` dan `8443` dari mesin tempat `KONG` berada melalui load balancing.

## Cara Menggunakan Linux Crontab

Meskipun `Hyperf` menyediakan komponen `crontab`, komponen tersebut mungkin tidak selalu memenuhi kebutuhan semua orang. Berikut adalah script untuk digunakan di `Linux` guna mengeksekusi `Command` di dalam `Docker`:

```bash
#!/usr/bin/env bash
basepath=$(cd `dirname $0`; pwd)
docker pull registry-vpc.cn-shanghai.aliyuncs.com/namespace/project:latest
docker run --rm -i -v $basepath/.env:/opt/www/.env \
--entrypoint php registry-vpc.cn-shanghai.aliyuncs.com/namespace/project:latest \
/opt/www/bin/hyperf.php your_command
```

## Optimasi Kernel

> Konten dari bagian ini perlu diverifikasi; gunakan dengan hati-hati.

Saat menginstal gateway `KONG`, dijelaskan bahwa `Ingress network` memiliki cacat desain. Ini dapat ditangani dengan `mengoptimalkan kernel`.

- Tentukan sumber TLinux:

```bash
tee /etc/yum.repos.d/CentOS-TLinux.repo <<-'EOF'
[Tlinux]
name=Tlinux for redhat/centos $releasever - $basearch
failovermethod=priority
gpgcheck=0
gpgkey=http://mirrors.tencentyun.com/epel/RPM-GPG-KEY-EPEL-7
enabled=1
baseurl=https://mirrors.tencent.com/tlinux/2.4/tlinux/x86_64/
EOF
```

- Instal kernel yang ditentukan:

```bash
yum -y install kernel-devel-4.14.105-19.0012.tl2.x86_64 kernel-4.14.105-19.0013.tl2.x86_64 kernel-headers-4.14.105-19.0013.tl2.x86_64
```

- Aktifkan kernel:

```bash
sudo awk -F\' '$1=="menuentry " {print i++ " : " $2}' /etc/grub2.cfg
grub2-set-default 0
grub2-mkconfig -o /boot/grub2/grub.cfg
```

- Reboot mesin:

```bash
reboot
```

### Optimasi Parameter Container

> Memerlukan dukungan Docker 19.09.0 atau lebih tinggi, pada level yang sama dengan konfigurasi image.

```yaml
sysctls:
  # Pemilihan mode reuse koneksi jaringan
  - net.ipv4.vs.conn_reuse_mode=0
  # Ketika LVS meneruskan paket data dan menemukan bahwa RS tujuan tidak valid (dihapus), ia akan menjatuhkan paket data, tetapi tidak akan menghapus koneksi yang sesuai. Ketika nilainya adalah 1, koneksi yang sesuai akan segera dilepaskan
  - net.ipv4.vs.expire_nodest_conn=1
```

## Masalah Umum

### fatal: git fetch-pack: expected shallow list

Situasi ini terjadi karena versi `git` yang digunakan oleh `gitlab-runner` terlalu rendah. Cukup perbarui versi `git`, sebagai berikut:

```bash
$ curl https://setup.ius.io | sh
$ yum remove -y git
$ yum -y install git2u
$ git version

# Install ulang gitlab-runner dan daftarkan ulang gitlab-runner
$ yum install gitlab-runner
```

### Setelah Service restart, terkadang di intranet terjadi kasus di mana container tidak dapat dijangkau, misalnya saat mengakses interface dari layanan ini beberapa kali di container lain, muncul "Connection refused"

Ini karena IP tidak mencukupi. Anda dapat memodifikasi network segment untuk menambah IP yang tersedia.

Buat Network baru:

```bash
docker network create \
--driver overlay \
--subnet 10.0.0.0/8 \
--opt encrypted \
--attachable \
default-network
```

Tambahkan Network baru ke service:

```bash
docker service update --network-add default-network service_name
```

Hapus Network asli:

```bash
docker service update --network-rm old-network service_name
```

### Saat menambahkan node ke Service, ternyata stuck di tahap `create`

Penyebab dan solusinya sama dengan di atas.

### Ketika password repository diubah di Portainer, pembaruan Service gagal

Ini karena modifikasi di Portainer tidak berpengaruh pada service yang sudah dibuat, jadi perbarui secara manual:

```bash
docker service update --with-registry-auth service_name
```


## Lampiran

### Hanya Menginstal Docker Swarm

Jika Anda hanya perlu menginstal dan menggunakan Docker Swarm, Anda dapat mengikuti dokumentasi di bawah ini.

Asumsikan kita memiliki tiga mesin A, B, dan C. Kita menjadikan A sebagai Leader secara default.

#### Menginstal Docker

Ketiga mesin menginstal Docker dengan cara berikut:

```bash
curl -sSL https://get.docker.com/ | sh
```

Modifikasi file `/lib/systemd/system/docker.service` untuk mengizinkan koneksi `TCP` ke `Docker`:

> Cukup tambahkan `-H tcp://0.0.0.0:2375` di bagian akhir.

```
ExecStart=/usr/bin/dockerd -H fd:// --containerd=/run/containerd/containerd.sock -H tcp://0.0.0.0:2375
```

Jika Anda tidak menggunakan akun `root`, Anda dapat menggunakan perintah berikut sehingga Anda tidak perlu menambahkan `sudo` setiap kali menjalankan `docker`:

```bash
usermod -aG docker $USER
```

#### Menginisialisasi Docker Swarm

Masuk ke mesin A dan jalankan perintah inisialisasi:

```bash
$ docker swarm init
```

Karena network segment default dari jaringan `ingress` sering bertentangan dengan yang baru dibuat, kita hapus jaringan `ingress` dan buat yang baru:

```bash
docker network rm ingress
docker network create --ingress --subnet 192.168.0.1/16 --driver overlay ingress
```

Kemudian buat `network` dengan `--subnet` diatur ke `10.0.0.1/8`:

```bash
docker network create \
--driver overlay \
--subnet 10.0.0.1/8 \
--opt encrypted \
--attachable \
default-network
```

Jalankan perintah untuk menampilkan cara bergabung ke cluster:

> Karena kita hanya memiliki tiga mesin, coba jadikan semuanya sebagai manager.

```bash
$ docker swarm join-token manager
```

Jika Anda perlu bergabung dengan node worker baru nanti, jalankan perintah berikut untuk mendapatkan script yang sesuai:

```bash
$ docker swarm join-token worker
```

#### Menggabungkan dua node lainnya ke cluster

Pergi ke dua mesin B dan C dan jalankan perintah yang baru saja dibuat:

```bash
docker swarm join --token xxxx <ip>:2377
```

Kembali ke mesin A dan jalankan perintah untuk melihat apakah sudah berhasil bergabung:

```bash
docker node ls
```

Jika Anda dapat melihat node B dan C, itu berarti penggabungan berhasil.

#### Menggunakan Layanan Image Registry Cloud

Saya tidak akan menjelaskan cara menggunakannya secara detail di sini; silakan pergi ke layanan cloud yang sesuai untuk mengoperasikannya.

Dokumen ini mengasumsikan bahwa developer telah berhasil membuka layanan image registry yang sesuai, dan dokumentasi selanjutnya secara default menggunakan node Shanghai Aliyun untuk penjelasan.

[Aliyun](https://cr.console.aliyun.com/cn-shanghai/instances)

#### Login ke image

Ketiga mesin A, B, dan C menjalankan operasi login:

```bash
docker login --username=xxxx registry.cn-shanghai.aliyuncs.com
```

#### Packaging image

Di sini Anda dapat melakukan packaging di mesin mana pun, atau Anda dapat melakukannya di lingkungan pengembangan (di lingkungan selain tiga mesin di atas, Anda perlu menjalankan `docker login` untuk login):

```bash
docker build . -t registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker push registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
```

#### Membuat file stack yml

Kembali ke mesin A, masuk ke direktori `/opt/www/your_project`, dan edit file `deploy.yml`:

```yaml
version: '3.7'
services:
  your_project:
    image: registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
    ports:
      - "9501:9501"
    deploy:
      replicas: 3
      restart_policy:
        condition: on-failure
        delay: 5s
        max_attempts: 5
      update_config:
        parallelism: 2
        delay: 5s
        order: start-first
    networks:
      - default-network
    configs:
      - source: your_project_v1.1
        target: /opt/www/.env
configs:
  your_project_v1.1:
    file: /opt/www/your_project/.env
networks:
  default-network:
    external: true
```

Edit file `.env` untuk melengkapi konfigurasi. Catatan: jangan gunakan `127.0.0.1` untuk menghubungkan MySQL dan layanan lainnya.

#### Memulai Service

```bash
docker pull registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker stack deploy -c /opt/www/your_project/deploy.yml --with-registry-auth your_project
```

Periksa apakah berjalan normal; jalankan tiga perintah berikut, dan data yang sesuai harus ada:

```bash
docker stack ls
docker service ls
docker ps
```

#### Menguji apakah service tersedia

Pergi ke tiga mesin dan lakukan pengujian `curl`. Jika semuanya dapat mengembalikan data yang sesuai, itu berarti service berhasil dimulai:

```bash
curl http://127.0.0.1:9501/
```

#### Memperbarui Service

Mesin pengembangan melakukan packaging dan mendorong ke image registry:

```bash
docker build . -t registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker push registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
```

Kembali ke mesin A untuk me-restart:

```bash
docker pull registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker stack deploy -c /opt/www/your_project/deploy.yml --with-registry-auth your_project
```
