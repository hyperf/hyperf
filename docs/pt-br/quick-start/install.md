# Instalação

## Requisitos

O Hyperf só pode ser executado em ambientes de sistema Linux e MacOS. No entanto, devido ao desenvolvimento da tecnologia de virtualização do Docker, é possível usar o Windows como ambiente de sistema utilizando o Docker for Windows. Se você usa MacOS, recomendamos uma implantação local para evitar que o compartilhamento de disco do Docker cause tempos de inicialização lentos no Hyperf.

Diversos Dockerfiles foram preparados no projeto [hyperf/hyperf-docker](https://github.com/hyperf/hyperf-docker), ou você pode usar uma imagem pré-construída baseada em [hyperf/hyperf](https://hub.docker.com/r/hyperf/hyperf).

Se você não usar o Docker como base do seu ambiente de sistema, também pode considerar usar o [Box](pt-br/eco/box.md) como ambiente básico de execução. Se você deseja configurar o ambiente você mesmo, precisa garantir que seu ambiente nativo atenda aos seguintes requisitos:

 - PHP >= 8.2
 - Qualquer um dos seguintes motores de rede
   - [Extensão PHP Swoole](https://github.com/swoole/swoole-src) >= 5.0, com `swoole.use_shortname` definido como `Off` no seu `php.ini`
   - [Extensão PHP Swow](https://github.com/swow/swow) >= 1.4
 - Extensão PHP JSON
 - Extensão PHP Pcntl (apenas no motor Swoole)
 - Extensão PHP OpenSSL (se você precisar usar HTTPS)
 - Extensão PHP PDO (se você precisar usar o Client MySQL)
 - Extensão PHP Redis (se você precisar usar o Client Redis)
 - Extensão PHP Protobuf (se você precisar usar o Server ou Client gRPC)


## Instalar o Hyperf

O Hyperf usa o [Composer](https://getcomposer.org) para gerenciar as dependências do projeto. Antes de usar o Hyperf, certifique-se de que seu ambiente operacional tem o Composer instalado.

### Criar projeto via `Composer`

O projeto [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) é um projeto esqueleto que preparamos para você, com arquivos integrados para componentes comuns e configurações relacionadas. É um projeto web fundamental que pode ser usado rapidamente para começar o desenvolvimento profissional com o Hyperf. No momento da instalação, você pode escolher as dependências de componentes de acordo com suas próprias necessidades.
Execute o seguinte comando para criar um projeto hyperf-skeleton no local atual

Baseado no motor Swoole:
```
composer create-project hyperf/hyperf-skeleton 
```

Baseado no motor Swow:
```
composer create-project hyperf/swow-skeleton 
```

> Durante o processo de instalação, para as opções sobre as quais você não tem certeza, pressione Enter diretamente para evitar problemas em que o serviço não consiga iniciar devido à adição automática de alguns listeners sem a configuração adequada.

### Desenvolver no Docker

Se o seu ambiente nativo não atender aos requisitos do sistema do Hyperf, ou se você não estiver familiarizado com configuração de sistema, você pode executar e desenvolver o projeto Hyperf da seguinte forma usando o Docker.

- Executar o Container

No exemplo a seguir, o host será mapeado para o diretório local `/workspace/skeleton`:

> Se a opção `selinux-enabled` estiver habilitada quando o docker iniciar, o acesso a recursos do host dentro do container será restrito, então você deve adicionar a opção `--privileged -u root` ao iniciar o container.

```shell
docker run --name hyperf \
-v /workspace/skeleton:/data/project \
-p 9501:9501 -it \
--privileged -u root \
--entrypoint /bin/sh \
hyperf/hyperf:8.1-alpine-v3.18-swoole
```

- Criar o Projeto

```shell
cd /data/project
composer create-project hyperf/hyperf-skeleton
```

- Iniciar o projeto

```shell
cd hyperf-skeleton
php bin/hyperf.php start
```

Em seguida, você poderá ver seu projeto instalado em `/workspace/skeleton/hyperf-skeleton`. Como o Hyperf é um framework CLI persistente, quando você modificar seu código, deve encerrar a instância do processo em execução com `CTRL + C` e executar novamente o comando de inicialização `php bin/hyperf.php start` para reiniciar seu servidor e recarregar o código.

## Extensões incompatíveis

Como o Hyperf é baseado na funcionalidade de coroutines sem precedentes do Swoole, muitas extensões são incompatíveis. As seguintes extensões (incluindo, mas não se limitando a) são atualmente incompatíveis:

- xhprof
- xdebug (disponível a partir do PHP 8.2+ e Swoole >= 5.0.2)
- blackfire
- trace
- uopz