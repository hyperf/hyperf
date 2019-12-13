# Docker Swarm 叢集搭建

現階段，Docker 容器技術已經相當成熟，就算是中小型公司也可以基於 Gitlab、Aliyun 映象服務、Docker Swarm 輕鬆搭建自己的 Docker 叢集服務。

## 安裝 Docker

```
curl -sSL https://get.daocloud.io/docker | sh
```

修改檔案 `/lib/systemd/system/docker.service`，允許使用 `TCP` 連線 `Docker`

```
ExecStart=/usr/bin/dockerd -H unix:// -H tcp://0.0.0.0:2375
```

## 搭建自己的 Gitlab

### 安裝 Gitlab

首先我們修改一下埠號，把 `sshd` 服務的 `22` 埠改為 `2222`，讓 `gitlab` 可以使用 `22` 埠。

```
$ vim /etc/ssh/sshd_config

# 預設 Port 改為 2222
Port 2222

# 重啟服務
$ systemctl restart sshd.service
```

重新登入機器

```
ssh -p 2222 root@host 
```

安裝 Gitlab

```
sudo docker run -d --hostname gitlab.xxx.cn \
--publish 443:443 --publish 80:80 --publish 22:22 \
--name gitlab --restart always --volume /srv/gitlab/config:/etc/gitlab \
--volume /srv/gitlab/logs:/var/log/gitlab \
--volume /srv/gitlab/data:/var/opt/gitlab \
gitlab/gitlab-ce:latest
```

首次登入 `Gitlab` 會重置密碼，使用者名稱是 `root`。

### 安裝 gitlab-runner

[官方地址](https://docs.gitlab.com/runner/install/linux-repository.html)

以 `CentOS` 為例

```
curl -L https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.rpm.sh | sudo bash
yum install gitlab-runner
```

當然，可以用 `curl https://setup.ius.io | sh` 命令，更新為最新的 `git` 源，然後直接使用 yum 安裝 git 和 gitlab-runner。

```
$ curl https://setup.ius.io | sh
$ yum -y install git2u
$ git version
$ yum install gitlab-runner
```

### 註冊 gitlab-runner

```
$ gitlab-runner register --clone-url http://內網ip/

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

### 修改 gitlab-runner 併發執行個數

```
$ vim /etc/gitlab-runner/config.toml
concurrent = 5
```

## 初始化 Swarm 叢集

登入另外一臺機器，初始化叢集
```
$ docker swarm init
```

建立自定義 Overlay 網路
```
docker network create \
--driver overlay \
--subnet 10.0.0.0/24 \
--opt encrypted \
--attachable \
default-network
```

加入叢集
```
# 顯示manager節點的TOKEN
$ docker swarm join-token manager
# 加入manager節點到叢集
$ docker swarm join --token <token> ip:2377

# 顯示worker節點的TOKEN
$ docker swarm join-token worker
# 加入worker節點到叢集
$ docker swarm join --token <token> ip:2377
```

然後配置釋出用的 gitlab-runner

> 其他與 builder 一致，但是 tag 卻不能一樣。線上環境可以設定為 tags，測試環境設定為 test

## 安裝其他應用 

以下以 `Mysql` 為例，直接使用上述 `network`，支援容器內使用 name 互調。

```
docker run --name mysql -v /srv/mysql:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=xxxx -p 3306:3306 --rm --network default-network -d mysql:5.7
```

## 安裝 Portainer

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

## 建立一個 Demo 專案

登入 Gitlab 建立一個 Demo 專案。並匯入我們的專案 [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton)


## 配置映象倉庫

> 我們直接使用阿里雲的即可

首先建立一個名稱空間 test_namespace，然後建立一個映象倉庫 demo，並使用本地倉庫。

然後到我們直接打包用的伺服器中，登入阿里雲 Docker Registry

```
usermod -aG docker gitlab-runner
su gitlab-runner
docker login --username=your_name registry.cn-shanghai.aliyuncs.com
```

修改我們專案裡的 .gitlab-ci.yml

```
variables:
  PROJECT_NAME: demo
  REGISTRY_URL: registry.cn-shanghai.aliyuncs.com/test_namespace
```

還有 deploy.test.yml，需要仔細對比以下檔案哦。

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

然後在我們的 portainer 中，建立對應的 Config demo_v1.0。當然，以下引數需要根據實際情況調整，因為我們的 Demo 中，沒有任何 IO 操作，所以填預設的即可。

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

因為我們配置的 gitlab-ci.yml 會檢測 test 分支和 tags，所以我們把修改的內容合併到 test 分支，然後推到 gitlab 上。

接下來我們就可以訪問叢集任意一臺機器的 9501 埠。進行測試了

```
curl http://127.0.0.1:9501/
```

## 安裝 KONG 閘道器

通常情況下，Swarm 叢集是不會直接對外的，所以我們這裡推薦使用 `KONG` 作為閘道器。
還有另外一個原因，那就是 `Swarm` 的 `Ingress 網路` 設計上有缺陷，所以在連線不復用的情況下，會有併發瓶頸，具體請檢視對應 `Issue` [#35082](https://github.com/moby/moby/issues/35082)
而 `KONG` 作為閘道器，預設情況下就會複用後端的連線，所以會極大減緩上述問題。

### 安裝資料庫

```
docker run -d --name kong-database \
  --network=default-network \
  -p 5432:5432 \
  -e "POSTGRES_USER=kong" \
  -e "POSTGRES_DB=kong" \
  postgres:9.6
```

### 安裝閘道器 

初始化資料庫

```
docker run --rm \
  --network=default-network \
  -e "KONG_DATABASE=postgres" \
  -e "KONG_PG_HOST=kong-database" \
  -e "KONG_CASSANDRA_CONTACT_POINTS=kong-database" \
  kong:latest kong migrations bootstrap
```

啟動

```
docker run -d --name kong \
  --network=default-network \
  -e "KONG_DATABASE=postgres" \
  -e "KONG_PG_HOST=kong-database" \
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

### 安裝 KONG Dashboard

> 暫時 `Docker` 中沒有更新 `v3.6.0` 所以最新版的 `KONG` 可能無法使用

```
docker run --rm --network=default-network -p 8080:8080 -d --name kong-dashboard pgbi/kong-dashboard start \
  --kong-url http://kong:8001 \
  --basic-auth user1=password1 user2=password2
```

### 配置

接下來只需要把部署 `KONG` 的機器 `IP` 對外，然後配置 `Service` 即可。
如果機器直接對外，最好只開放 `80` `443` 埠，然後把 `Kong` 容器的 `8000` 和 `8443` 對映到 `80` 和 `443` 上。
當然，如果使用了 `SLB` 等負載均衡，就直接通過負載均衡，把 `80` 和 `443` 對映到 `KONG` 所在幾臺機器的 `8000` `8443` 上。

## 如何使用 Linux Crontab

`Hyperf` 雖然提供了 `crontab` 元件，但是不一定可以滿足所有人的需求，這裡提供一個 `Linux` 使用的指令碼，執行 `Docker` 內的 `Command`。

```bash
#!/usr/bin/env bash
basepath=$(cd `dirname $0`; pwd)
docker pull registry-vpc.cn-shanghai.aliyuncs.com/namespace/project:latest
docker run --rm -i -v $basepath/.env:/opt/www/.env \
--entrypoint php registry-vpc.cn-shanghai.aliyuncs.com/namespace/project:latest \
/opt/www/bin/hyperf.php your_command
```

## 意外情況

### fatal: git fetch-pack: expected shallow list

這種情況是 `gitlab-runner` 使用的 `git` 版本過低，更新 `git` 版本即可。

```
$ curl https://setup.ius.io | sh
$ yum remove -y git
$ yum -y install git2u
$ git version

# 重新安裝 gitlab-runner 並重新註冊 gitlab-runner
$ yum install gitlab-runner
```
