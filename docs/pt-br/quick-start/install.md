# Instalação

## Requisitos

O Hyperf só pode ser executado em ambientes Linux e macOS. No entanto, com a evolução da virtualização via Docker, é possível usar o Windows como ambiente de sistema utilizando o Docker for Windows. Se você usa macOS, recomendamos uma implantação local para evitar que o disco compartilhado do Docker deixe o Hyperf lento para iniciar.

Diversos Dockerfiles já estão preparados no projeto [hyperf/hyperf-docker](https://github.com/hyperf/hyperf-docker), ou você pode usar uma imagem pré-construída baseada em [hyperf/hyperf](https://hub.docker.com/r/hyperf/hyperf).

Se você não usa Docker como base do seu ambiente, também pode considerar usar o [Box](pt-br/eco/box.md) como ambiente básico de execução. Se você quiser configurar o ambiente manualmente, garanta que seu ambiente nativo atenda aos seguintes requisitos:

 - PHP >= 8.1
 - Qualquer um dos seguintes motores de rede
   - [Extensão PHP Swoole](https://github.com/swoole/swoole-src) >= 5.0, com `swoole.use_shortname` definido como `Off` no seu `php.ini`
   - [Extensão PHP Swow](https://github.com/swow/swow) >= 1.4
 - Extensão PHP JSON
 - Extensão PHP Pcntl (apenas no motor Swoole)
 - Extensão PHP OpenSSL ï¼ˆse você precisa usar HTTPSï¼‰
 - Extensão PHP PDO ï¼ˆse você precisa usar o MySQL Clientï¼‰
 - Extensão PHP Redis ï¼ˆse você precisa usar o Redis Clientï¼‰
 - Extensão PHP Protobuf ï¼ˆse você precisa usar gRPC Server ou Clientï¼‰


## Instalar o Hyperf

O Hyperf usa [Composer](https://getcomposer.org) para gerenciar as dependências do projeto. Antes de usar o Hyperf, certifique-se de que seu ambiente tenha o Composer instalado.

### Criar projeto via `Composer`

O projeto [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) é um projeto esqueleto preparado para você, com arquivos de componentes comuns e configurações relacionadas já incluídos. Ele é um projeto web base que permite começar rapidamente com um desenvolvimento profissional em Hyperf. No momento da instalação, você pode escolher as dependências de componentes conforme suas necessidades.
Execute o comando abaixo para criar um projeto hyperf-skeleton no diretório atual.

Baseado no motor Swoole:
```
composer create-project hyperf/hyperf-skeleton 
```

Baseado no motor Swow:
```
composer create-project hyperf/swow-skeleton 
```

> Durante o processo de instalação, para opções sobre as quais você não tem certeza, pressione Enter diretamente para evitar problemas em que o serviço não consegue iniciar por conta da adição automática de alguns listeners sem a configuração adequada.

### Desenvolver com Docker

Se o seu ambiente nativo não atende aos requisitos do Hyperf, ou se você não está familiarizado com a configuração do sistema, você pode executar e desenvolver o projeto Hyperf usando Docker da seguinte forma.

- Executar contêiner

No exemplo a seguir, o host será mapeado para o diretório local `/workspace/skeleton`:

> Se a opção `selinux-enabled` estiver habilitada quando o Docker iniciar, o acesso do contêiner a recursos do host será restringido; nesse caso, você deve adicionar a opção `--privileged -u root` ao iniciar o contêiner.

```shell
docker run --name hyperf \
-v /workspace/skeleton:/data/project \
-p 9501:9501 -it \
--privileged -u root \
--entrypoint /bin/sh \
hyperf/hyperf:8.1-alpine-v3.18-swoole
```

- Criar projeto

```shell
cd /data/project
composer create-project hyperf/hyperf-skeleton
```

- Iniciar o projeto

```shell
cd hyperf-skeleton
php bin/hyperf.php start
```

Em seguida, você poderá ver o projeto instalado em `/workspace/skeleton/hyperf-skeleton`. Como o Hyperf é um framework CLI persistente, quando você modificar seu código, você deve encerrar o processo em execução com `CTRL + C` e executar novamente o comando `php bin/hyperf.php start` para reiniciar o servidor e recarregar o código.

## Extensões incompatíveis

Como o Hyperf se baseia na funcionalidade inédita de corrotinas do Swoole, muitas extensões são incompatíveis. As seguintes extensões (incluindo, mas não se limitando a) são atualmente incompatíveis:

- xhprof
- xdebug (disponível no PHP 8.1+ e Swoole >= 5.0.2)
- blackfire
- trace
- uopz
