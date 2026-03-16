# Docker Swarm cluster building tutorial

At this stage, the Docker container technology is quite mature, and even small and medium-sized companies can easily build their own Docker cluster services based on Gitlab, Aliyun image service, and Docker Swarm.

## Installation Docker

```
curl -sSL https://get.daocloud.io/docker | sh
```

## Build your own Gitlab

### Installation Gitlab

First, let's modify the port number and change the `22` port of the `sshd` service to `2222`, so that `gitlab` can use the `22` port.

```
$ vim /etc/ssh/sshd_config

# Default Port changed to 2222
Port 2222

# restart the service
$ systemctl restart sshd.service
```

Re-login to the machine

```
ssh -p 2222 root@host 
```

Install Gitlab

```
sudo docker run -d --hostname gitlab.xxx.cn \
--publish 443:443 --publish 80:80 --publish 22:22 \
--name gitlab --restart always --volume /srv/gitlab/config:/etc/gitlab \
--volume /srv/gitlab/logs:/var/log/gitlab \
--volume /srv/gitlab/data:/var/opt/gitlab \
gitlab/gitlab-ce:latest
```

Logging into `Gitlab` for the first time will reset the password, and the username is `root`.

### Installation gitlab-runner

[Official address](https://docs.gitlab.com/runner/install/linux-repository.html)

Take `CentOS` as an example

```
curl -L https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.rpm.sh | sudo bash
yum install gitlab-runner
```

Of course, you can use the `curl https://setup.ius.io | sh` command, update to the latest `git` source, and then install git and gitlab-runner directly using yum.

```
$ curl https://setup.ius.io | sh
$ yum -y install git2u
$ git version
$ yum install gitlab-runner
```

### Register gitlab-runner

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

## Initialize the Swarm cluster

Login to another machine and initialize the cluster
```
$ docker swarm init
```

Create a custom overlay network

```
docker network create \
--driver overlay \
--subnet 12.0.0.0/8 \
--opt encrypted \
--attachable \
default-network
```

Join the cluster
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

Then configure the gitlab-runner for publishing

> Others are the same as builder, but tag cannot be the same. The online environment can be set to tags, and the test environment can be set to test

## Installation Portainer

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

## Create a demo project

Login to Gitlab to create a demo project. and import our project [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton)


## Configure the mirror repository

> We can use Alibaba Cloud directly

First create a namespace test_namespace, then create a mirror warehouse demo, and use the local warehouse.

Then go to the server we use directly for packaging and login to Alibaba Cloud Docker Registry

```
usermod -aG docker gitlab-runner
su gitlab-runner
docker login --username=your_name registry.cn-shanghai.aliyuncs.com
```

Modify .gitlab-ci.yml in our project

```
variables:
  PROJECT_NAME: demo
  REGISTRY_URL: registry.cn-shanghai.aliyuncs.com/test_namespace
```

There is also deploy.test.yml, you need to compare the following files carefully.

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

Then in our portainer, create the corresponding Config demo_v1.0. Of course, the following parameters need to be adjusted according to the actual situation, because there is no IO operation in our Demo, so fill in the default ones.

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

Because the gitlab-ci.yml we configured will detect the test branch and tags, we merge the modified content into the test branch, and then push it to gitlab.

Next we can access port 9501 of any machine in the cluster.

```
curl http://127.0.0.1:9501/
```

## Accidents

### fatal: git fetch-pack: expected shallow list

In this case, the version of `git` used by `gitlab-runner` is too low, and the version of `git` can be updated.

```
$ curl https://setup.ius.io | sh
$ yum remove -y git
$ yum -y install git2u
$ git version

# Reinstall gitlab-runner and re-register gitlab-runner
$ yum install gitlab-runner
```
