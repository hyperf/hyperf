# Docker Swarm 集群搭建

现阶段，Docker容器技术已经相当成熟，就算是中小型公司也可以基于 Gitlab、Aliyun镜像服务、Docker Swarm 轻松搭建自己的 Docker集群服务。

## 安装 Docker

```
curl -sSL https://get.daocloud.io/docker | sh
```

修改文件 `/lib/systemd/system/docker.service`，允许使用 `TCP` 连接 `Docker`

```
ExecStart=/usr/bin/dockerd -H unix:// -H tcp://0.0.0.0:2375
```

## 搭建自己的Gitlab

### 安装Gitlab

首先我们修改一下端口号，把 `sshd` 服务的 `22` 端口改为 `2222`，让 `gitlab` 可以使用 `22` 端口。

```
$ vim /etc/ssh/sshd_config

# 默认 Port 改为 2222
Port 2222

# 重启服务
$ systemctl restart sshd.service
```

重新登录机器

```
ssh -p 2222 root@host 
```

安装 Gitlab

```
sudo docker run -d --hostname gitlab.xxx.cn \
--publish 443:443 --publish 80:80 --publish 22:22 \
--name gitlab --restart always --volume /srv/gitlab/config:/etc/gitlab \
--volume /srv/gitlab/logs:/var/log/gitlab \
--volume /srv/gitlab/data:/var/opt/gitlab \
gitlab/gitlab-ce:latest
```

首次登录 `Gitlab` 会重置密码，用户名是 `root`。

### 安装gitlab-runner

[官方地址](https://docs.gitlab.com/runner/install/linux-repository.html)

以 `CentOS` 为例

```
curl -L https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.rpm.sh | sudo bash
yum install gitlab-runner
```

当然，可以用 `curl https://setup.ius.io | sh` 命令，更新为最新的 `git` 源，然后直接使用 yum 安装 git 和 gitlab-runner。

```
$ curl https://setup.ius.io | sh
$ yum -y install git2u
$ git version
$ yum install gitlab-runner
```

### 注册 gitlab-runner

```
$ gitlab-runner register --clone-url http://内网ip/

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

### 修改 gitlab-runner 并发执行个数

```
$ vim /etc/gitlab-runner/config.toml
concurrent = 5
```

## 初始化 Swarm 集群

登录另外一台机器，初始化集群
```
$ docker swarm init
```

创建自定义 Overlay 网络
```
docker network create \
--driver overlay \
--subnet 10.0.0.0/24 \
--opt encrypted \
--attachable \
default-network
```

加入集群
```
# 显示manager节点的TOKEN
$ docker swarm join-token manager
# 加入manager节点到集群
$ docker swarm join --token <token> ip:2377

# 显示worker节点的TOKEN
$ docker swarm join-token worker
# 加入worker节点到集群
$ docker swarm join --token <token> ip:2377
```

然后配置发布用的 gitlab-runner

> 其他与 builder 一致，但是 tag 却不能一样。线上环境可以设置为 tags，测试环境设置为 test

## 安装其他应用 

以下以 `Mysql` 为例，直接使用上述 `network`，支持容器内使用 name 互调。

```
docker run --name mysql -v /srv/mysql:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=xxxx -p 3306:3306 --rm --network default-network -d mysql:5.7
```

## 安装 Portainer

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

## 创建一个Demo项目

登录 Gitlab 创建一个 Demo 项目。并导入我们的项目 [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton)


## 配置镜像仓库

> 我们直接使用阿里云的即可

首先创建一个命名空间 test_namespace，然后创建一个镜像仓库 demo，并使用本地仓库。

然后到我们直接打包用的服务器中，登录阿里云 Docker Registry

```
usermod -aG docker gitlab-runner
su gitlab-runner
docker login --username=your_name registry.cn-shanghai.aliyuncs.com
```

修改我们项目里的 .gitlab-ci.yml

```
variables:
  PROJECT_NAME: demo
  REGISTRY_URL: registry.cn-shanghai.aliyuncs.com/test_namespace
```

还有 deploy.test.yml，需要仔细对比以下文件哦。

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

然后在我们的 portainer 中，创建对应的 Config demo_v1.0。当然，以下参数需要根据实际情况调整，因为我们的Demo中，没有任何IO操作，所以填默认的即可。

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

因为我们配置的 gitlab-ci.yml 会检测 test 分支和 tags，所以我们把修改的内容合并到test分支，然后推到gitlab上。

接下来我们就可以访问集群任意一台机器的 9501 端口。进行测试了

```
curl http://127.0.0.1:9501/
```

## 安装 KONG 网关

通常情况下，Swarm集群是不会直接对外的，所以我们这里推荐使用 `KONG` 作为网关。
还有另外一个原因，那就是 `Swarm` 的 `Ingress网络` 设计上有缺陷，所以在连接不复用的情况下，会有并发瓶颈，具体请查看对应 `Issue` [#35082](https://github.com/moby/moby/issues/35082)
而 `KONG` 作为网关，默认情况下就会复用后端的连接，所以会极大减缓上述问题。

### 安装数据库

```
docker run -d --name kong-database \
  --network=default-network \
  -p 5432:5432 \
  -e "POSTGRES_USER=kong" \
  -e "POSTGRES_DB=kong" \
  postgres:9.6
```

### 安装网关 

初始化数据库

```
docker run --rm \
  --network=default-network \
  -e "KONG_DATABASE=postgres" \
  -e "KONG_PG_HOST=kong-database" \
  -e "KONG_CASSANDRA_CONTACT_POINTS=kong-database" \
  kong:latest kong migrations bootstrap
```

启动

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

### 安装 KONG Dashboard

> 暂时 `Docker` 中没有更新 `v3.6.0` 所以最新版的 `KONG` 可能无法使用

```
docker run --rm --network=default-network -p 8080:8080 -d --name kong-dashboard pgbi/kong-dashboard start \
  --kong-url http://kong:8001 \
  --basic-auth user1=password1 user2=password2
```

### 配置

接下来只需要把部署 `KONG` 的机器 `IP` 对外，然后配置 `Service` 即可。
如果机器直接对外，最好只开放 `80` `443` 端口，然后把 `Kong` 容器的 `8000` 和 `8443` 映射到 `80` 和 `443` 上。
当然，如果使用了 `SLB` 等负载均衡，就直接通过负载均衡，把 `80` 和 `443` 映射到 `KONG` 所在几台机器的 `8000` `8443` 上。

## 如何使用 Linux Crontab

`Hyperf` 虽然提供了 `crontab` 组件，但是不一定可以满足所有人的需求，这里提供一个 `Linux` 使用的脚本，执行 `Docker` 内的 `Command`。

```bash
#!/usr/bin/env bash
basepath=$(cd `dirname $0`; pwd)
docker pull registry-vpc.cn-shanghai.aliyuncs.com/namespace/project:latest
docker run --rm -i -v $basepath/.env:/opt/www/.env \
--entrypoint php registry-vpc.cn-shanghai.aliyuncs.com/namespace/project:latest \
/opt/www/bin/hyperf.php your_command
```

## 意外情况

### fatal: git fetch-pack: expected shallow list

这种情况是 `gitlab-runner` 使用的 `git` 版本过低，更新 `git` 版本即可。

```
$ curl https://setup.ius.io | sh
$ yum remove -y git
$ yum -y install git2u
$ git version

# 重新安装 gitlab-runner 并重新注册 gitlab-runner
$ yum install gitlab-runner
```
