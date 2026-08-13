# Docker Swarm Cluster Setup

At the current stage, container technology is quite mature. Even small and medium-sized companies can easily build their own Docker cluster services based on Gitlab, Aliyun image warehouse services, and Docker Swarm.

## Installing Docker

```bash
curl -sSL https://get.docker.com/ | sh
```

Modify the file `/lib/systemd/system/docker.service` to allow `TCP` connections to `Docker`:

> Just append `-H tcp://0.0.0.0:2375` at the end.

```
ExecStart=/usr/bin/dockerd -H fd:// --containerd=/run/containerd/containerd.sock -H tcp://0.0.0.0:2375
```

If you are not using the `root` account, you can use the following command so that you do not need to add `sudo` every time you execute `docker`:

```bash
usermod -aG docker $USER
```

### Configuring Warehouse Mirror Address

Due to slow access speeds caused by cross-border routes, we can configure warehouse mirror addresses for Docker to improve these network issues. For example, [Aliyun Docker Image Accelerator](https://help.aliyun.com/document_detail/60750.html). We can apply for a `Docker` accelerator and then configure it in the `/etc/docker/daemon.json` file on the server. Add the following content, then restart `Docker`. Please fill in the accelerator address you obtained below.

```json
{"registry-mirrors": ["https://xxxxx.mirror.aliyuncs.com"]}
```

## Setting up Gitlab Service

### Installing Gitlab

#### Modifying the Default SSHD Port Number

First, we need to modify the port number of the server's `sshd` service, changing the default `22` port to `2222` (or another unoccupied port), so that `gitlab` can use port `22` for `ssh` connections.

```bash
$ vim /etc/ssh/sshd_config

# Change default Port to 2222
Port 2222

# Restart the service
$ systemctl restart sshd.service
```

Re-login to the machine:

```bash
ssh -p 2222 root@host
```

#### Installing Gitlab

We start a Gitlab service via Docker, as follows:

> The hostname must be added; if there is no domain name, you can fill in the public network address directly.

```bash
sudo docker run -d --hostname gitlab.xxx.cn \
--publish 443:443 --publish 80:80 --publish 22:22 \
--name gitlab --restart always --volume /srv/gitlab/config:/etc/gitlab \
--volume /srv/gitlab/logs:/var/log/gitlab \
--volume /srv/gitlab/data:/var/opt/gitlab \
gitlab/gitlab-ce:latest
```

The default username is `root`, and the initial password is obtained in the following way:

```shell
docker exec gitlab cat /etc/gitlab/initial_root_password
```

### Installing gitlab-runner

> It is recommended to deploy this separately from the `Gitlab` server and use a dedicated runner server.

We take the `CentOS` installation method as an example. For others, please refer to the [Gitlab official documentation](https://docs.gitlab.com/runner/install/linux-repository.html).

```bash
curl -L https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.rpm.sh | sudo bash
yum install gitlab-runner
```

Of course, you can also use the `curl https://setup.ius.io | sh` command to update to the latest `git` source, and then use `yum` to install `git` and `gitlab-runner` directly.

```bash
$ curl https://setup.ius.io | sh
$ yum -y install git2u
$ git version
$ yum install gitlab-runner
```

### Registering gitlab-runner

Register the `gitlab-runner` to Gitlab using the `gitlab-runner register --clone-url http://your-ip/` command. Note that you should replace `your-ip` with your Gitlab's intranet IP, as follows:

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

### Modifying gitlab-runner Concurrency

```bash
$ vim /etc/gitlab-runner/config.toml
concurrent = 5
```

### Adding Permissions for gitlab-runner

- Permission to execute docker without sudo:

```shell
sudo usermod -aG docker gitlab-runner
```

- Permission for the image registry:

```shell
su gitlab-runner
docker login -u username your-docker-repository
```

### Modifying Email

If you need `Gitlab` to send emails (e.g., user creation emails), you can try modifying `/srv/gitlab/config/gitlab.rb`:

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

## Initializing Swarm Cluster

### Log in to another machine and initialize the cluster

```bash
$ docker swarm init
```

### Creating a Custom Overlay Network

The following provides three ways to create a network segment; you only need to execute one of them:

1. Directly create a custom Overlay network:

```shell
docker network create \
--driver overlay \
--subnet 10.0.0.1/8 \
--opt encrypted \
--attachable \
default-network
```

2. Sometimes, because the network segment conflicts, the stack launch might fail. You can try modifying `--subnet`, but in this way, the current network segment only supports 65,535 IPs:

```shell
docker network create \
--driver overlay \
--subnet 10.1.0.1/16 \
--opt encrypted \
--attachable \
default-network
```

3. Of course, because the `ingress` network's default network segment conflicts with the newly created one in most cases, we can delete the `ingress` network and create a new one:

```shell
docker network rm ingress
docker network create --ingress --subnet 192.168.0.1/16 --driver overlay ingress
```

Then create a `network` with `--subnet` set to `10.0.0.1/8`:

```shell
docker network create \
--driver overlay \
--subnet 10.0.0.1/8 \
--opt encrypted \
--attachable \
default-network
```

### Joining the Cluster

```bash
# Display the TOKEN of the manager node
$ docker swarm join-token manager
# Join the manager node to the cluster
$ docker swarm join --token <token> ip:2377

# Display the TOKEN of the worker node
$ docker swarm join-token worker
# Join the worker node to the cluster
$ docker swarm join --token <token> ip:2377
```

### Configuring gitlab-runner for Publishing

> Others are consistent with the builder, but the tag cannot be the same. Online environments can be set to `tags`, and test environments set to `test`.

## Installing Other Applications

The following takes `Mysql` as an example. It directly uses the above `network` and supports inter-container calls using names:

```bash
docker run --name mysql -v /srv/mysql:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=xxxx -p 3306:3306 --rm --network default-network -d mysql:5.7
```

## Installing Portainer

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

### Backing up Portainer Data

> `portainer_container` is the corresponding container name; fill it in according to the actual situation.

```bash
docker run -it --volumes-from portainer_container -v $(pwd):/backup --name backup --rm nginx tar -cf /backup/data.tar /data/
```

### Restoring Portainer Data

First, use the creation command to recreate the portainer service.

Then, use the following method to reload the backup into the container:

```bash
docker run -it --volumes-from portainer_container -v $(pwd):/backup --name importer --rm nginx bash
cd /backup
tar xf data.tar -C /
```

Finally, you just need to restart the container.

## Creating a Demo Project

Log in to Gitlab to create a Demo project, and import our project [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton).

## Configuring the Image Registry

> We will use Aliyun's directly.

First, create a namespace `test_namespace`, then create an image repository `demo`, and use the local repository.

Then go to the server we use for packaging and log in to the Aliyun Docker Registry:

```bash
usermod -aG docker gitlab-runner
su gitlab-runner
docker login --username=your_name registry.cn-shanghai.aliyuncs.com
```

Modify `.gitlab-ci.yml` in our project:

```yaml
variables:
  PROJECT_NAME: demo
  REGISTRY_URL: registry.cn-shanghai.aliyuncs.com/test_namespace
```

There is also `deploy.test.yml`, which needs to be carefully compared with the following file.

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

Then in our Portainer, create the corresponding Config `demo_v1.0`. Of course, the following parameters need to be adjusted according to the actual situation. Because there is no I/O operation in our Demo, you can just fill in the default values:

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

Because our configured `gitlab-ci.yml` will detect the `test` branch and `tags`, we merge the modified content into the `test` branch and then push it to gitlab.

Next, we can visit port `9501` of any machine in the cluster to test:

```bash
curl http://127.0.0.1:9501/
```

## Installing KONG Gateway

Normally, the Docker Swarm cluster is not directly exposed for access, so we can build a gateway service on the upper layer. `KONG` is recommended as the gateway here.
Another reason is that Docker Swarm's `Ingress network` has design defects. In the case of non-reused connections, there will be concurrency bottlenecks. For details, please check the corresponding [Issue #35082](https://github.com/moby/moby/issues/35082).
As a gateway service, `KONG` will reuse backend connections by default, so it will greatly alleviate the above problems.

### Installing the Database

```bash
docker run -d --name kong-database \
  --network=default-network \
  -p 5432:5432 \
  -e "POSTGRES_USER=kong" \
  -e "POSTGRES_DB=kong" \
  -e "POSTGRES_PASSWORD=kong" \
  postgres:9.6
```

### Installing the Gateway

Initialize the database:

```bash
docker run --rm \
  --network=default-network \
  -e "KONG_DATABASE=postgres" \
  -e "KONG_PG_HOST=kong-database" \
  -e "KONG_PG_PASSWORD=kong" \
  -e "KONG_CASSANDRA_CONTACT_POINTS=kong-database" \
  kong:latest kong migrations bootstrap
```

Start:

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

### Installing KONG Dashboard

> Temporarily `Docker` has not updated `v3.6.0`, so the latest version of `KONG` may not be usable. You can use KONG version 0.14.1.

```bash
docker run --rm --network=default-network -p 8080:8080 -d --name kong-dashboard pgbi/kong-dashboard start \
  --kong-url http://kong:8001 \
  --basic-auth user1=password1 user2=password2
```

### Configuring Service

Next, you just need to expose the `IP` of the machine where the `KONG` gateway is deployed for access, and then configure the corresponding `Service`.
If the machine is directly exposed for access, it is best to only open ports `80` and `443`, and then map the `8000` and `8443` ports of the `Kong` container to the `80` and `443` ports.
Of course, if a load balancing service such as `SLB` is used, directly map ports `80` and `443` to ports `8000` and `8443` of the machine where `KONG` is located through load balancing.

## How to use Linux Crontab

Although `Hyperf` provides a `crontab` component, it may not necessarily meet everyone's needs. Here is a script for use under `Linux` to execute `Command` inside `Docker`:

```bash
#!/usr/bin/env bash
basepath=$(cd `dirname $0`; pwd)
docker pull registry-vpc.cn-shanghai.aliyuncs.com/namespace/project:latest
docker run --rm -i -v $basepath/.env:/opt/www/.env \
--entrypoint php registry-vpc.cn-shanghai.aliyuncs.com/namespace/project:latest \
/opt/www/bin/hyperf.php your_command
```

## Kernel Optimization

> The content of this section needs to be verified; use with caution.

When installing the `KONG` gateway, it was introduced that the `Ingress network` has design defects. This can be handled by `optimizing the kernel`.

- Specify TLinux source:

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

- Install specified kernel:

```bash
yum -y install kernel-devel-4.14.105-19.0012.tl2.x86_64 kernel-4.14.105-19.0013.tl2.x86_64 kernel-headers-4.14.105-19.0013.tl2.x86_64
```

- Make the kernel effective:

```bash
sudo awk -F\' '$1=="menuentry " {print i++ " : " $2}' /etc/grub2.cfg
grub2-set-default 0
grub2-mkconfig -o /boot/grub2/grub.cfg
```

- Reboot machine:

```bash
reboot
```

### Container Parameter Optimization

> Requires Docker 19.09.0 or higher support, at the same level as the image configuration.

```yaml
sysctls:
  # Selection of network connection reuse mode
  - net.ipv4.vs.conn_reuse_mode=0
  # When LVS forwards data packets and finds that the destination RS is invalid (deleted), it will drop the data packet, but will not delete the corresponding connection. When the value is 1, the corresponding connection will be released immediately
  - net.ipv4.vs.expire_nodest_conn=1
```

## Common Problems

### fatal: git fetch-pack: expected shallow list

This situation occurs because the `git` version used by `gitlab-runner` is too low. Just update the `git` version, as follows:

```bash
$ curl https://setup.ius.io | sh
$ yum remove -y git
$ yum -y install git2u
$ git version

# Reinstall gitlab-runner and re-register gitlab-runner
$ yum install gitlab-runner
```

### After Service restarts, there are occasional occurrences on the intranet where the container cannot be reached, such as accessing the interface of this service multiple times in other containers, resulting in "Connection refused"

This is because the IP is not enough. You can modify the network segment to increase available IPs.

Create a new Network:

```bash
docker network create \
--driver overlay \
--subnet 10.0.0.0/8 \
--opt encrypted \
--attachable \
default-network
```

Add a new Network to the service:

```bash
docker service update --network-add default-network service_name
```

Delete the original Network:

```bash
docker service update --network-rm old-network service_name
```

### When adding a node to Service, it is found that it is stuck in the `create` stage

The cause and solution are the same as above.

### When the repository password is modified in Portainer, updating Service fails

This is because the modification in Portainer cannot act on the service that has already been created, so just update it manually:

```bash
docker service update --with-registry-auth service_name
```


## Appendix

### Only Installing Docker Swarm

If you only need to install and use Docker Swarm, you can follow the documentation below.

Assume we have three machines A, B, and C. We default to A as the Leader.

#### Installing Docker

All three machines install Docker in the following way:

```bash
curl -sSL https://get.docker.com/ | sh
```

Modify the file `/lib/systemd/system/docker.service` to allow `TCP` connections to `Docker`:

> Just append `-H tcp://0.0.0.0:2375` at the end.

```
ExecStart=/usr/bin/dockerd -H fd:// --containerd=/run/containerd/containerd.sock -H tcp://0.0.0.0:2375
```

If you are not using the `root` account, you can use the following command so that you do not need to add `sudo` every time you execute `docker`:

```bash
usermod -aG docker $USER
```

#### Initializing Docker Swarm

Enter machine A and execute the initialization command:

```bash
$ docker swarm init
```

Because the `ingress` network's default network segment conflicts with the newly created one in most cases, we delete the `ingress` network and create a new one:

```bash
docker network rm ingress
docker network create --ingress --subnet 192.168.0.1/16 --driver overlay ingress
```

Then create a `network` with `--subnet` set to `10.0.0.1/8`:

```bash
docker network create \
--driver overlay \
--subnet 10.0.0.1/8 \
--opt encrypted \
--attachable \
default-network
```

Execute the command to display joining the cluster:

> Because we only have three machines, try to declare all of them as managers.

```bash
$ docker swarm join-token manager
```

If you need to join a new worker node later, execute the following command to get the corresponding script:

```bash
$ docker swarm join-token worker
```

#### Joining the other two nodes to the cluster

Go to the two machines B and C and execute the command just generated:

```bash
docker swarm join --token xxxx <ip>:2377
```

Return to machine A and execute the command to see if it has been successfully joined:

```bash
docker node ls
```

If you can see the nodes B and C, it means the joining was successful.

#### Using Cloud Service Image Service

I won't explain how to use it in detail here; please go to the corresponding cloud service to operate.

This document assumes that the developer has successfully opened the corresponding image service, and the subsequent documentation defaults to using Aliyun's Shanghai node for explanation.

[Aliyun](https://cr.console.aliyun.com/cn-shanghai/instances)

#### Logging in to the image

All three machines A, B, and C execute the login operation:

```bash
docker login --username=xxxx registry.cn-shanghai.aliyuncs.com
```

#### Packaging the image

Here you can package it on any machine, or you can package it in the development environment (in an environment other than the three machines above, you need to execute `docker login` to log in):

```bash
docker build . -t registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker push registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
```

#### Making stack yml file

Return to machine A, go to the `/opt/www/your_project` directory, and edit the `deploy.yml` file:

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

Edit the `.env` file to complete the configuration. Note: do not use `127.0.0.1` to link MySQL and other services.

#### Starting Service

```bash
docker pull registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker stack deploy -c /opt/www/your_project/deploy.yml --with-registry-auth your_project
```

Check if it starts normally; execute the following three instructions, and corresponding data should exist:

```bash
docker stack ls
docker service ls
docker ps
```

#### Testing if the service is available

Go to the three machines and perform `curl` tests. If they can all return the corresponding data, it means the service started successfully:

```bash
curl http://127.0.0.1:9501/
```

#### Updating Service

The development machine packages and pushes to the image registry:

```bash
docker build . -t registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker push registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
```

Return to machine A to restart:

```bash
docker pull registry.cn-shanghai.aliyuncs.com/your_namespace/your_project:latest
docker stack deploy -c /opt/www/your_project/deploy.yml --with-registry-auth your_project
```
