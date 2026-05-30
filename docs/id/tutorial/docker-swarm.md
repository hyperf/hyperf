# Tutorial Membangun Cluster Docker Swarm

Pada tahap ini, teknologi Docker container sudah cukup matang, dan bahkan
perusahaan skala kecil dan menengah pun dapat dengan mudah membangun layanan
Docker cluster mereka sendiri berbasis Gitlab, layanan image Aliyun, dan Docker
Swarm.

## Instalasi Docker

```
curl -sSL https://get.daocloud.io/docker | sh
```

## Membangun Gitlab Sendiri

### Instalasi Gitlab

Pertama, mari kita modifikasi nomor port dan ubah port `22` dari layanan `sshd`
menjadi `2222`, sehingga `gitlab` dapat menggunakan port `22`.

```
$ vim /etc/ssh/sshd_config

# Default Port changed to 2222
Port 2222

# restart the service
$ systemctl restart sshd.service
```

Login kembali ke mesin

```
ssh -p 2222 root@host 
```

Instal Gitlab

```
sudo docker run -d --hostname gitlab.xxx.cn \
--publish 443:443 --publish 80:80 --publish 22:22 \
--name gitlab --restart always --volume /srv/gitlab/config:/etc/gitlab \
--volume /srv/gitlab/logs:/var/log/gitlab \
--volume /srv/gitlab/data:/var/opt/gitlab \
gitlab/gitlab-ce:latest
```

Masuk (log in) ke `Gitlab` untuk pertama kalinya akan mereset kata sandi, dan
username-nya adalah `root`.

### Instalasi gitlab-runner

[Alamat resmi](https://docs.gitlab.com/runner/install/linux-repository.html)

Ambil `CentOS` sebagai contoh

```
curl -L https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.rpm.sh | sudo bash
yum install gitlab-runner
```

Tentu saja, Anda dapat menggunakan perintah `curl https://setup.ius.io | sh`,
memperbarui ke sumber `git` terbaru, dan kemudian menginstal git serta
gitlab-runner secara langsung menggunakan yum.

```
$ curl https://setup.ius.io | sh
$ yum -y install git2u
$ git version
$ yum install gitlab-runner
```

### Mendaftarkan gitlab-runner

```
$ gitlab-runner register --clone-url http://intranet-ip/

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

## Menginisialisasi Swarm Cluster

Login ke mesin lain dan inisialisasi cluster
```
$ docker swarm init
```

Buat overlay network kustom

```
docker network create \
--driver overlay \
--subnet 12.0.0.0/8 \
--opt encrypted \
--attachable \
default-network
```

Bergabung ke cluster
```
# Display the token of the manager node
$ docker swarm join-token manager
# Add the manager node to the cluster
$ docker swarm join --token <token> ip:2377

# Display the token of the worker node
$ docker swarm join-token worker
# Join the worker node to the cluster
$ docker swarm join --token <token> ip:2377
```

Kemudian konfigurasi gitlab-runner untuk proses publishing

> Yang lainnya sama dengan builder, tetapi tag tidak boleh sama. Lingkungan
> (environment) online dapat diatur ke tags, dan lingkungan pengujian dapat
> diatur ke test

## Instalasi Portainer

[Portainer](https://github.com/portainer/portainer)

```
docker service create \
    --name portainer \
    --publish 9000:9000 \
    --replicas=1 \
    --constraint 'node.role == manager' \
    --mount type=volume,src=portainer_data,dst=/data \
    --mount type=bind,src=//var/run/docker.sock,dst=/var/run/docker.sock \
    portainer/portainer
```

## Membuat Project Demo

Login ke Gitlab untuk membuat project demo, dan impor project kami
[hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton)

## Konfigurasi Mirror Repository

> Kita dapat menggunakan Alibaba Cloud secara langsung

Pertama buat namespace test_namespace, kemudian buat mirror repository demo, dan
gunakan local repository.

Kemudian buka server yang kita gunakan langsung untuk packaging dan login ke
Alibaba Cloud Docker Registry

```
usermod -aG docker gitlab-runner
su gitlab-runner
docker login --username=your_name registry.cn-shanghai.aliyuncs.com
```

Modifikasi file .gitlab-ci.yml pada project kita

```
variables:
  PROJECT_NAME: demo
  REGISTRY_URL: registry.cn-shanghai.aliyuncs.com/test_namespace
```

Terdapat juga file deploy.test.yml, Anda perlu membandingkan file berikut
dengan cermat.

```yml
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

Kemudian pada portainer kita, buat Config demo_v1.0 yang sesuai. Tentu saja,
parameter berikut perlu disesuaikan dengan situasi aktual. Karena tidak ada
operasi IO dalam Demo kita, isi saja dengan nilai default.

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

Karena file gitlab-ci.yml yang kita konfigurasi akan mendeteksi branch test dan
tags, kita lakukan merge konten yang dimodifikasi ke branch test, lalu push ke
gitlab.

Selanjutnya kita dapat mengakses port 9501 dari mesin mana pun di dalam cluster.

```
curl http://127.0.0.1:9501/
```

## Masalah Umum

### fatal: git fetch-pack: expected shallow list

Dalam kasus ini, versi `git` yang digunakan oleh `gitlab-runner` terlalu rendah,
dan versi `git` tersebut dapat diperbarui.

```
$ curl https://setup.ius.io | sh
$ yum remove -y git
$ yum -y install git2u
$ git version

# Reinstall gitlab-runner and re-register gitlab-runner
$ yum install gitlab-runner
```
