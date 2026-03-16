# Docker Swarm 集群搭建

现阶段，容器技术已经相当成熟了，就算是中小型公司，也可以基于 Gitlab、Aliyun 镜像仓库服务 和 Docker Swarm 轻松搭建自己的 Docker 集群服务。

## 安装 Docker

```
curl -sSL https://get.docker.com/ | sh
```

修改文件 `/lib/systemd/system/docker.service`，允许使用 `TCP` 连接 `Docker`

> 只需要追加后面的 -H tcp://0.0.0.0:2375 即可

```
ExecStart=/usr/bin/dockerd -H fd:// --containerd=/run/containerd/containerd.sock -H tcp://0.0.0.0:2375
```

如果不是使用的 `root` 账户，可以通过以下命令，让每次执行 `docker` 时，不需要增加 `sudo`

```
usermod -aG docker $USER
```

### 配置仓库镜像地址

基于跨国线路访问速度过慢等问题，我们可以为 Docker 配置仓库镜像地址，来改善这些网络问题，如 [阿里云(Aliyun) Docker 镜像加速器](https://help.aliyun.com/document_detail/60750.html)，我们可以申请一个 `Docker` 加速器，然后配置到服务器上的 `/etc/docker/daemon.json` 文件，添加以下内容，然后重启 `Docker`，下面的地址请填写您自己获得的加速器地址。

```json
{"registry-mirrors": ["https://xxxxx.mirror.aliyuncs.com"]}
```

## 搭建 Gitlab 服务

### 安装 Gitlab

#### 修改 sshd 默认端口号

首先我们需要修改一下服务器的 `sshd` 服务的端口号，把默认的 `22` 端口改为 `2222` 端口(或其它未被占用的端口)，这样可以让 `gitlab` 通过使用 `22` 端口来进行 `ssh` 连接。

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

#### 安装 Gitlab

我们来通过 Docker 启动一个 Gitlab 服务，如下：

> hostname 一定要加，如果没有域名可以直接填外网地址

```
sudo docker run -d --hostname gitlab.xxx.cn \
--publish 443:443 --publish 80:80 --publish 22:22 \
--name gitlab --restart always --volume /srv/gitlab/config:/etc/gitlab \
--volume /srv/gitlab/logs:/var/log/gitlab \
--volume /srv/gitlab/data:/var/opt/gitlab \
gitlab/gitlab-ce:latest
```

默认用户名为 `root`，初始密码通过以下方式获得

```shell
docker exec gitlab cat /etc/gitlab/initial_root_password
```

### 安装 gitlab-runner

> 这里建议与 `Gitlab` 服务器分开部署，专门提供单独的 runner 服务器。

我们以 `CentOS` 的的安装方式为例，其余可参考 [Gitlab 官网文档](https://docs.gitlab.com/runner/install/linux-repository.html)

```
curl -L https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.rpm.sh | sudo bash
yum install gitlab-runner
```

当然，也可以用 `curl https://setup.ius.io | sh` 命令，更新为最新的 `git` 源，然后直接使用 yum 安装 git 和 gitlab-runner。

```
$ curl https://setup.ius.io | sh
$ yum -y install git2u
$ git version
$ yum install gitlab-runner
```

### 注册 gitlab-runner

通过 `gitlab-runner register --clone-url http://your-ip/` 命令来将 gitlab-runner 注册到 Gitlab 上，注意要替换 `your-ip` 为您的 Gitlab 的内网 IP，如下：

```
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

### 修改 gitlab-runner 并发执行个数

```
$ vim /etc/gitlab-runner/config.toml
concurrent = 5
```

### 为 gitlab-runner 增加权限

- 免 sudo 执行 docker 的权限

```shell
sudo usermod -aG docker gitlab-runner
```

- 镜像仓库的权限

```shell
su gitlab-runner
docker login -u username your-docker-repository
```

###

### 修改邮箱

如果需要 `Gitlab` 发送邮件（比如用户创建的邮件等），可以尝试修改 `/srv/gitlab/config/gitlab.rb`

```
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

## 初始化 Swarm 集群

### 登录另外一台机器，初始化集群

```
$ docker swarm init
```

### 创建自定义 Overlay 网络

以下提供三种方式创建网段，只需要执行其一即可

1. 直接创建自定义 Overlay 网络

```shell
docker network create \
--driver overlay \
--subnet 10.0.0.1/8 \
--opt encrypted \
--attachable \
default-network
```

2. 有时可能因为网段冲突，导致 stack 启动失败，可以尝试修改 `--subnet`，不过这种方式，当前网段就只支持 65535 个 ip

```shell
docker network create \
--driver overlay \
--subnet 10.1.0.1/16 \
--opt encrypted \
--attachable \
default-network
```

3. 当然，因为大多数是 ingress 网络默认的网段与我们新建的网段冲突，所以我们可以删掉 ingress 网络，然后重新创建一个

```shell
docker network rm ingress
docker network create --ingress --subnet 192.168.0.1/16 --driver overlay ingress
```

然后再创建 `--subnet` 为 `10.0.0.1/8` 的 `network`

```shell
docker network create \
--driver overlay \
--subnet 10.0.0.1/8 \
--opt encrypted \
--attachable \
default-network
```

### 加入集群

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

### 配置发布用的 gitlab-runner

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

### 备份 Portainer 的数据

> portainer_container 为对应的容器名，按实际情况填写

```
docker run -it --volumes-from portainer_container -v $(pwd):/backup --name backup --rm nginx tar -cf /backup/data.tar /data/
```

### 恢复 Portainer 的数据

首先使用创建命令，重新创建 portainer 服务

然后使用以下方法，将备份重载到容器中

```
docker run -it --volumes-from portainer_container -v $(pwd):/backup --name importer --rm nginx bash
cd /backup
tar xf data.tar -C /
```

最后只需要重启容器即可

## 创建一个 Demo 项目

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

然后在我们的 Portainer 中，创建对应的 Config `demo_v1.0`。当然，以下参数需要根据实际情况调整，因为我们的 Demo 中，没有任何 I/O 操作，所以填默认的即可。

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

因为我们配置的 gitlab-ci.yml 会检测 test 分支和 tags，所以我们把修改的内容合并到 test 分支，然后推到 gitlab 上。

接下来我们就可以访问集群任意一台机器的 9501 端口。进行测试了

```
curl http://127.0.0.1:9501/
```

## 安装 KONG 网关

通常情况下，Docker Swarm 集群是不会直接对外暴露提供访问的，所以我们可以在上层构建一个网关服务，这里推荐使用 `KONG` 作为网关。
还有另外一个原因是 Docker Swarm 的 `Ingress 网络` 存在设计的缺陷，在连接不复用的情况下，会有并发瓶颈，具体细节请查看对应的 `Issue` [#35082](https://github.com/moby/moby/issues/35082)
而 `KONG` 作为网关服务，默认情况下会复用后端的连接，所以会极大减缓上述问题。

### 安装数据库

```
docker run -d --name kong-database \
  --network=default-network \
  -p 5432:5432 \
  -e "POSTGRES_USER=kong" \
  -e "POSTGRES_DB=kong" \
  -e "POSTGRES_PASSWORD=kong" \
  postgres:9.6
```

### 安装网关

初始化数据库

```
docker run --rm \
  --network=default-network \
  -e "KONG_DATABASE=postgres" \
  -e "KONG_PG_HOST=kong-database" \
  -e "KONG_PG_PASSWORD=kong" \
  -e "KONG_CASSANDRA_CONTACT_POINTS=kong-database" \
  kong:latest kong migrations bootstrap
```

启动

```
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

### 安装 KONG Dashboard

> 暂时 `Docker` 中没有更新 `v3.6.0` 所以最新版的 `KONG` 可能无法使用，可以使用 0.14.1 版本的 KONG

```
docker run --rm --network=default-network -p 8080:8080 -d --name kong-dashboard pgbi/kong-dashboard start \
  --kong-url http://kong:8001 \
  --basic-auth user1=password1 user2=password2
```

### 配置 Service

接下来只需要把部署 `KONG` 网关的机器 `IP` 对外暴露访问，然后配置对应的 `Service` 即可。
如果机器直接对外暴露访问，那么最好只开放 `80` 和 `443` 端口，然后把 `Kong` 容器的 `8000` 和 `8443` 端口映射到 `80` 和 `443` 端口上。
当然，如果使用了 `SLB` 等负载均衡服务，也直接通过负载均衡，把 `80` 和 `443` 端口映射到 `KONG` 所在机器的 `8000` `8443` 端口上。

## 如何使用 Linux Crontab

`Hyperf` 虽然提供了 `crontab` 组件，但可能并不一定可以满足所有人的需求，这里提供一个 `Linux` 下使用的脚本，来执行 `Docker` 内的 `Command`。

```bash
#!/usr/bin/env bash
basepath=$(cd `dirname $0`; pwd)
docker pull registry-vpc.cn-shanghai.aliyuncs.com/namespace/project:latest
docker run --rm -i -v $basepath/.env:/opt/www/.env \
--entrypoint php registry-vpc.cn-shanghai.aliyuncs.com/namespace/project:latest \
/opt/www/bin/hyperf.php your_command
```

## 内核优化

> 本小节内容，有待验证，谨慎使用

安装 `KONG` 网关时，有介绍 `Ingress 网络` 存在设计的缺陷，这块可以通过 `优化内核` 处理。

- 指定 TLinux 源

```
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

- 安装指定内核

```
yum -y install kernel-devel-4.14.105-19.0012.tl2.x86_64 kernel-4.14.105-19.0013.tl2.x86_64 kernel-headers-4.14.105-19.0013.tl2.x86_64
```

- 使内核生效

```
sudo awk -F\' '$1=="menuentry " {print i++ " : " $2}' /etc/grub2.cfg
grub2-set-default 0
grub2-mkconfig -o /boot/grub2/grub.cfg
```

- 重启机器

```
reboot
```

### 容器参数优化

> 需要 Docker 19.09.0 以上支持，与 image 配置同级

```yaml
sysctls:
  # 网络连接复用模式的选择
  - net.ipv4.vs.conn_reuse_mode=0
  # 当LVS转发数据包，发现目的RS无效（删除）时，会丢弃该数据包，但不删除相应连接。值为1时，则马上释放相应连接
  - net.ipv4.vs.expire_nodest_conn=1
```

## 常见问题

### fatal: git fetch-pack: expected shallow list

这种情况是 `gitlab-runner` 使用的 `git` 版本过低，更新 `git` 版本即可，如下：

```
$ curl https://setup.ius.io | sh
$ yum remove -y git
$ yum -y install git2u
$ git version

# 重新安装 gitlab-runner 并重新注册 gitlab-runner
$ yum install gitlab-runner
```

### Service 重启后，内网出现偶发的，容器无法触达的问题，比如多次在其他容器，访问此服务的接口，会出现 Connection refused

这是由于 IP 不够用导致，可以修改网段，增加可用 IP

创建新的 Network

```
docker network create \
--driver overlay \
--subnet 10.0.0.0/8 \
--opt encrypted \
--attachable \
default-network
```

为服务增加新的 Network

```
docker service update --network-add default-network service_name
```

删除原来的 Network

```
docker service update --network-rm old-network service_name
```

### 为 Service 增加节点，发现一直卡在 create 阶段

原因和解决办法同上

### 当在 Portainer 中修改了仓库密码后，更新 Service 失败

这是因为 Portainer 修改后，不能作用于已经创建的服务，所以手动更新即可

```
docker service update --with-registry-auth service_name
```


## 附录

### 只安装 Docker Swarm

如果你只需要安装并使用 Docker Swarm，可以根据以下文档进行操作。

假设我们有三台机器 A B C，我们默认将 A 作为 Leader

#### 安装 Docker

三台机器都按照以下方式安装 Docker

```
curl -sSL https://get.docker.com/ | sh
```

修改文件 `/lib/systemd/system/docker.service`，允许使用 `TCP` 连接 `Docker`

> 只需要追加后面的 -H tcp://0.0.0.0:2375 即可

```
ExecStart=/usr/bin/dockerd -H fd:// --containerd=/run/containerd/containerd.sock -H tcp://0.0.0.0:2375
```

如果不是使用的 `root` 账户，可以通过以下命令，让每次执行 `docker` 时，不需要增加 `sudo`

```
usermod -aG docker $USER
```

#### 初始化 Docker Swarm

进入 A 机器，执行初始化命令

```
$ docker swarm init
```

因为大多数 ingress 网络默认的网段与我们新建的网段冲突，所以我们删掉 ingress 网络，然后重新创建一个

```shell
docker network rm ingress
docker network create --ingress --subnet 192.168.0.1/16 --driver overlay ingress
```

然后再创建 `--subnet` 为 `10.0.0.1/8` 的 `network`

```shell
docker network create \
--driver overlay \
--subnet 10.0.0.1/8 \
--opt encrypted \
--attachable \
default-network
```

执行展示加入集群的命令

> 因为我们只有三台机器，所以尽量都声明为 manager

```
$ docker swarm join-token manager
```

若后期需要加入新的 worker 节点，则执行以下命令得到对应的脚本

```
$ docker swarm join-token worker
```

#### 将另外两台节点加入到集群

到 B C 两台机器中执行刚刚生成的命令

```shell
docker swarm join --token xxxx <ip>:2377
```

回到 A 机器，执行命令查看是否已经成功加入

```shell
docker node ls
```

如果能看到 B 和 C 的节点，则代表加入成功

#### 使用云服务的镜像服务

这里不详细说明如何使用了，请自己去对应云服务进行操作

本文档默认开发者已经成功开通了对应的镜像服务，之后的文档全部默认使用阿里云的上海节点来讲述

[阿里云](https://cr.console.aliyun.com/cn-shanghai/instances)

#### 登录镜像

三台机器 A B C 全部执行登录操作

```shell
docker login --username=xxxx registry.cn-shanghai.aliyuncs.com
```

#### 打包镜像

这里可以在任何一台机器进行打包，也可以在开发环境打包（非上述三台机器的环境下，需要执行 docker login 进行登录）

```shell
docker build . -t registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker push registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
```

#### 制作 stack yml 文件

回到 A 机器上，到 /opt/www/your_project 目录下，编辑 deploy.yml 文件

```shell
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

编辑 .env 文件，完成配置，注意，不要使用 127.0.0.1 链接 MySQL 等服务

#### 启动服务

```shell
docker pull registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker stack deploy -c /opt/www/your_project/deploy.yml --with-registry-auth your_project
```

查看是否正常启动，执行下述三个指令，都应该存在对应的数据

```shell
docker stack ls
docker service ls
docker ps
```

#### 测试服务是否可用

到三台机器上，全部进行 curl 测试，如果都能返回对应数据，代表服务启动成功

```shell
curl http://127.0.0.1:9501/
```

#### 更新服务

开发机打包，并推送到镜像仓库中

```shell
docker build . -t registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker push registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
```

会到 A 机器，进行重启

```shell
docker pull registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker stack deploy -c /opt/www/your_project/deploy.yml --with-registry-auth your_project
```
