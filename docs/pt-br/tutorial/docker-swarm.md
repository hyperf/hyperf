# Tutorial de criação de cluster com Docker Swarm

Nos dias de hoje, a tecnologia de containers Docker já está bastante madura, e até empresas de pequeno e médio porte conseguem construir facilmente seus próprios serviços de cluster Docker baseados em Gitlab, no serviço de imagens da Aliyun e no Docker Swarm.

## Instalação do Docker

```
curl -sSL https://get.daocloud.io/docker | sh
```

## Construindo seu próprio Gitlab

### Instalação do Gitlab

Primeiro, vamos modificar o número da porta e alterar a porta `22` do serviço `sshd` para `2222`, para que o `gitlab` possa usar a porta `22`.

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

Instale o Gitlab

```
sudo docker run -d --hostname gitlab.xxx.cn \
--publish 443:443 --publish 80:80 --publish 22:22 \
--name gitlab --restart always --volume /srv/gitlab/config:/etc/gitlab \
--volume /srv/gitlab/logs:/var/log/gitlab \
--volume /srv/gitlab/data:/var/opt/gitlab \
gitlab/gitlab-ce:latest
```

Ao fazer login no `Gitlab` pela primeira vez, será necessário redefinir a senha, e o nome de usuário é `root`.

### Instalação do gitlab-runner

[Endereço oficial](https://docs.gitlab.com/runner/install/linux-repository.html)

Tomando o `CentOS` como exemplo

```
curl -L https://packages.gitlab.com/install/repositories/runner/gitlab-runner/script.rpm.sh | sudo bash
yum install gitlab-runner
```

Claro, você também pode usar o comando `curl https://setup.ius.io | sh`, atualizar para a fonte mais recente do `git`, e então instalar o git e o gitlab-runner diretamente usando o yum.

```
$ curl https://setup.ius.io | sh
$ yum -y install git2u
$ git version
$ yum install gitlab-runner
```

### Registrando o gitlab-runner

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

## Inicializando o cluster Swarm

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

Em seguida, configure o gitlab-runner para publicação

> O restante é igual ao builder, mas a tag não pode ser a mesma. O ambiente de produção pode ser configurado com a tag tags, e o ambiente de teste pode ser configurado com a tag test

## Instalação do Portainer

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

## Criando um projeto de demonstração

Faça login no Gitlab para criar um projeto de demonstração e importe nosso projeto [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton)


## Configurando o repositório de imagens (mirror)

> Podemos usar diretamente a Alibaba Cloud

Primeiro crie um namespace test_namespace, depois crie um repositório de imagens demo, e use o repositório local.

Em seguida, vá para o servidor que usamos diretamente para empacotamento e faça login no Alibaba Cloud Docker Registry

```
usermod -aG docker gitlab-runner
su gitlab-runner
docker login --username=your_name registry.cn-shanghai.aliyuncs.com
```

Modifique o .gitlab-ci.yml em nosso projeto

```
variables:
  PROJECT_NAME: demo
  REGISTRY_URL: registry.cn-shanghai.aliyuncs.com/test_namespace
```

Há também o deploy.test.yml, você precisa comparar os arquivos a seguir com atenção.

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

Depois, no nosso portainer, crie o Config correspondente demo_v1.0. É claro que os parâmetros a seguir precisam ser ajustados de acordo com a situação real, porque não há operação de IO em nosso Demo, então preencha com os valores padrão.

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

Como o gitlab-ci.yml que configuramos vai detectar a branch e as tags de teste, mesclamos o conteúdo modificado na branch de teste e depois enviamos (push) para o gitlab.

Em seguida, podemos acessar a porta 9501 de qualquer máquina do cluster.

```
curl http://127.0.0.1:9501/
```

## Acidentes

### fatal: git fetch-pack: expected shallow list

Nesse caso, a versão do `git` usada pelo `gitlab-runner` está muito baixa, e a versão do `git` pode ser atualizada.

```
$ curl https://setup.ius.io | sh
$ yum remove -y git
$ yum -y install git2u
$ git version

# Reinstall gitlab-runner and re-register gitlab-runner
$ yum install gitlab-runner
```
