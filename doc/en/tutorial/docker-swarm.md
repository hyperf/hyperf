# Docker Swarm Cluster building

At this stage, Docker container technology is quite mature, even small and medium-sized companies can easily build their own Docker cluster services based on Gitlab, Aliyun image service, and Docker Swarm.

## Install Docker

```
curl -sSL https://get.daocloud.io/docker | sh
```

## Build your own Gitlab

### Install Gitlab

First we modify the port number, change the `22` port of the `sshd` service to `2222`, so that `gitlab` can use the `22` port.

```
$ vim /etc/ssh/sshd_config

# default Port change to 2222
Port 2222

# reload service
$ systemctl restart sshd.service
```

Log back in to the machine

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

Logging in to `Gitlab` for the first time resets the password, and the username is `root`.

### Install gitlab-runner

[Official address](https://docs.gitlab.com/runner/install/linux-repository.html)

Take `CentOS` as an example

```
curl -L https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.rpm.sh | sudo bash
yum install gitlab-runner
```

Of course, you can update to the latest `git` source with the `curl https://setup.ius.io | sh` command, then install git and gitlab-runner directly using yum.

```
$ curl https://setup.ius.io | sh
$ yum -y install git2u
$ git version
$ yum install gitlab-runner
```

### Registered gitlab-runner

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

### Modify the number of gitlab-runner concurrent executions

```
$ vim /etc/gitlab-runner/config.toml
concurrent = 5
```

## Initialize the Swarm cluster

Log in to another machine and initialize the cluster
```
$ docker swarm init
```

Create a custom Overlay network
```
docker network create \
--driver overlay \
--subnet 10.0.0.0/24 \
--opt encrypted \
--attachable \
default-network
```

Join the cluster
```
# Display the TOKEN of the manager node
$ docker swarm join-token manager
# Join the manager node to the cluster
$ docker swarm join --token <token> ip:2377

# Display the TOKEN of the worker node
$ docker swarm join-token worker
# Join the worker node to the cluster
$ docker swarm join --token <token> ip:2377
```

Then configure the gitlab-runner for publishing

> Others are consistent with builders, but tags are not the same. The online environment can be set to tags and the test environment is set to test

## Install other apps

The following example uses `Mysql` to directly use the above `network` to support the use of name intermodulation in the container.

```
docker run --name mysql -v /srv/mysql:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=xxxx -p 3306:3306 --rm --network default-network -d mysql:5.7
```

## Install Portainer

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

## Create a Demo project

Log in to Gitlab to create a Demo project. And import our project [hyperf-skeleton](https://github.com/hyperf-cloud/hyperf-skeleton)


## Configuring mirrored warehouse

> We can use Alibaba Cloud directly.

First create a namespace test_namespace, then create a mirrored warehouse demo and use the local repository.

Then go to the server we are directly packaging, log in to Alibaba Cloud Docker Registry

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

There is also deploy.test.yml, you need to carefully compare the following files.

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

Then in our portainer, create the corresponding Config demo_v1.0. Of course, the following parameters need to be adjusted according to the actual situation, because there is no IO operation in our Demo, so fill in the default.

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

Because our configured gitlab-ci.yml will detect the test branch and tags, we merge the changes into the test branch and push it to gitlab.

Next we can access the 9501 port of any machine in the cluster. Tested

```
curl http://127.0.0.1:9501/
```

## Install KONG Gateway

Normally, the Swarm cluster is not directly external, so we recommend using `KONG` as the gateway.
There is another reason, that is, `Swarm`'s `Ingress network` is flawed in design, so there will be a concurrency bottleneck when the connection is not reused. Please refer to the corresponding `Issue` [#35082](https://github.com/moby/moby/issues/35082)
And `KONG` as a gateway, the backend connection will be reused by default, so it will greatly slow down the above problem.

### Install the database

```
docker run -d --name kong-database \
  --network=default-network \
  -p 5432:5432 \
  -e "POSTGRES_USER=kong" \
  -e "POSTGRES_DB=kong" \
  postgres:9.6
```

### Install gateway

Initialize the database

```
docker run --rm \
  --network=default-network \
  -e "KONG_DATABASE=postgres" \
  -e "KONG_PG_HOST=kong-database" \
  -e "KONG_CASSANDRA_CONTACT_POINTS=kong-database" \
  kong:latest kong migrations bootstrap
```

start up

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

### Install KONG Dashboard

> Temporary `Docker` is not updated in `v3.6.0` so the latest version of `KONG` may not be available

```
docker run --rm --network=default-network -p 8080:8080 -d --name kong-dashboard pgbi/kong-dashboard start \
  --kong-url http://kong:8001 \
  --basic-auth user1=password1 user2=password2
```

### Configuration

Next, you only need to deploy `KONG` machine `IP` to the outside, and then configure `Service`.
If the machine is directly external, it is best to only open the `80` `443` port, and then map the `8000` and `8443` of the `Kong` container to `80` and `443`.
Of course, if you use load balancing such as `SLB`, you can directly map `80` and `443` to `8000` `8443` of several machines where `KONG` is located through load balancing.

## Accidents

### fatal: git fetch-pack: expected shallow list

In this case, the `git` version used by `gitlab-runner` is too low, and the `git` version can be updated.

```shell
$ curl https://setup.ius.io | sh
$ yum remove -y git
$ yum -y install git2u
$ git version

# Reinstall gitlab-runner and re-register gitlab-runner
$ yum install gitlab-runner
```

