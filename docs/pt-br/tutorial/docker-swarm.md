# Tutorial de criação de cluster Docker Swarm

Atualmente, a tecnologia de containers Docker está bem madura e até empresas pequenas e médias conseguem construir seus próprios serviços de cluster Docker com base em GitLab, serviço de imagens da Aliyun e Docker Swarm.

## Instalar o Docker

```
curl -sSL https://get.daocloud.io/docker | sh
```

## Montar seu próprio GitLab

### Instalar o GitLab

Primeiro, vamos modificar a porta: altere a porta `22` do serviço `sshd` para `2222`, para que o `gitlab` possa usar a porta `22`.

```
$ vim /etc/ssh/sshd_config

# Default Port changed to 2222
Port 2222

# restart the service
$ systemctl restart sshd.service
```

Faça login novamente na máquina

```
ssh -p 2222 root@host 
```

Instale o GitLab

```
sudo docker run -d --hostname gitlab.xxx.cn \
--publish 443:443 --publish 80:80 --publish 22:22 \
--name gitlab --restart always --volume /srv/gitlab/config:/etc/gitlab \
--volume /srv/gitlab/logs:/var/log/gitlab \
--volume /srv/gitlab/data:/var/opt/gitlab \
gitlab/gitlab-ce:latest
```

Ao entrar no `Gitlab` pela primeira vez, você redefinirá a senha, e o usuário é `root`.

### Instalar o gitlab-runner

[Official address](https://docs.gitlab.com/runner/install/linux-repository.html)

Use `CentOS` como exemplo

```
curl -L https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.rpm.sh | sudo bash
yum install gitlab-runner
```

Claro, você pode usar o comando `curl https://setup.ius.io | sh` para atualizar a fonte do `git` para a versão mais recente e então instalar git e gitlab-runner diretamente usando yum.

```
$ curl https://setup.ius.io | sh
$ yum -y install git2u
$ git version
$ yum install gitlab-runner
```

### Registrar o gitlab-runner

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

## Inicializar o cluster Swarm

Faça login em outra máquina e inicialize o cluster
```
$ docker swarm init
```

Crie uma rede overlay personalizada

```
docker network create \
--driver overlay \
--subnet 12.0.0.0/8 \
--opt encrypted \
--attachable \
default-network
```

Entre no cluster
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

Depois, configure o gitlab-runner para publicação

> O resto é igual ao builder, mas a tag não pode ser a mesma. O ambiente online pode usar tags e o ambiente de teste pode usar test

## Instalar o Portainer

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

## Criar um projeto de demonstração

Faça login no GitLab para criar um projeto de demonstração e importe o projeto [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton)


## Configurar o repositório de imagens

> Podemos usar o Alibaba Cloud diretamente

Primeiro crie um namespace `test_namespace`, depois crie um repositório de imagens `demo` e use o repositório local.

Depois, vá ao servidor que usamos para build e faça login no Alibaba Cloud Docker Registry

```
usermod -aG docker gitlab-runner
su gitlab-runner
docker login --username=your_name registry.cn-shanghai.aliyuncs.com
```

Modifique `.gitlab-ci.yml` no projeto

```
variables:
  PROJECT_NAME: demo
  REGISTRY_URL: registry.cn-shanghai.aliyuncs.com/test_namespace
```

Também existe `deploy.test.yml`; você precisa comparar os arquivos a seguir com cuidado.

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

Depois, no Portainer, crie o Config correspondente `demo_v1.0`. Claro, os parâmetros a seguir precisam ser ajustados de acordo com a situação real; como não há operações de IO no nosso Demo, preencha com os valores padrão.

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

Como o `.gitlab-ci.yml` que configuramos detecta a branch `test` e tags, nós mesclamos o conteúdo modificado na branch `test` e então fazemos push para o GitLab.

Em seguida, podemos acessar a porta 9501 de qualquer máquina no cluster.

```
curl http://127.0.0.1:9501/
```

## Problemas comuns

### fatal: git fetch-pack: expected shallow list

Nesse caso, a versão do `git` usada pelo `gitlab-runner` é muito antiga; você pode atualizá-la.

```
$ curl https://setup.ius.io | sh
$ yum remove -y git
$ yum -y install git2u
$ git version

# Reinstall gitlab-runner and re-register gitlab-runner
$ yum install gitlab-runner
```
