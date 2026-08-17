# Perguntas Frequentes

## Nomes curtos de funções do Swoole não foram desabilitados

```
[ERROR] Swoole short function names must be disabled before the server starts, please set swoole.use_shortname = 'Off' in your php.ini.
```

Você precisa adicionar `swoole.use_shortname ='Off'` ao seu arquivo de configuração php.ini

> Observe que essa configuração DEVE ser configurada no php.ini e NÃO PODE ser sobrescrita pela função ini_set().

Você também pode iniciar o servidor através do seguinte comando, desabilitando os nomes curtos de funções do Swoole ao executar o comando PHP:

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## Perda de mensagens em filas assíncronas

Se o método `handle` não estiver sendo executado ao usar o componente `async-queue`, verifique as seguintes possibilidades:

1. O `Redis` está sendo compartilhado com outro projeto ou outros usuários, e as mensagens estão sendo consumidas por esses projetos ou usuários?
2. Você tem resquícios de processos antigos ainda em execução que possam estar consumindo as mensagens?

A seguir, uma solução simples para ambos os problemas:
   
1. Execute o comando `killall php` no seu `console`
2. Modifique a configuração `channel` da sua `async-queue`
   
## Erro `Swoole\Error: API must be called in the coroutine` ao usar o componente `hyperf/amqp`
   
Defina o valor de configuração `close_on_destruct` como `false` no arquivo de configuração `config/autoload/amqp.php`.

## Todas as requisições retornam erros 404 ao usar a versão 4.5 do Swoole e o componente `view`
    
Se você estiver usando a versão 4.5 do Swoole e o componente `view` e houver um problema de erro `404`, você pode tentar remover o item de configuração `static_handler_locations` do arquivo de configuração `config/autoload/server.php`.
    
Esse valor de configuração contém um caminho que será considerado uma rota de `arquivo estático`, então se o valor for `/`, todas as requisições são processadas como arquivos, resultando em erros 404.

## Alterações no código não têm efeito
   
Se não houver alteração quando você modificar o código da sua aplicação `Hyperf`, execute o seguinte comando:
   
```bash
composer dump-autoload -o
```
   
Durante o desenvolvimento, NÃO defina o valor de configuração `scan_cacheable` como `true`, pois isso fará com que o arquivo não seja reanalisado quando o `cache do collector` estiver sendo usado. Além disso, o `Dockerfile` do pacote oficial `hyperf-skeleton` tem essa configuração habilitada por padrão. Ao desenvolver em ambiente `Docker`, defina `scan_cacheable` como `false`.

> Quando a variável de ambiente `SCAN_CACHEABLE` existe, essa configuração não pode ser modificada em nenhum arquivo `.env`.

## Erro de sintaxe ao iniciar o servidor

A seguinte exceção é lançada quando o servidor `Hyperf` inicia:

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

Execute `composer analyse` para inicializar uma análise estática do código-fonte a fim de localizar o problema.

Normalmente esse problema é causado pela execução da versão `3.0.5` do [zircote/swagger](https://github.com/zircote/swagger-php); consulte [#834](https://github.com/zircote/swagger-php/issues/834) para mais informações.

Se você instalou o [hyperf/swagger](https://github.com/hyperf/swagger), fixe a versão do [zircote/swagger](https://github.com/zircote/swagger-php) em `3.0.4`.

## O `Hyperf` não consegue iniciar porque o memory_limit é muito pequeno

Por padrão, o `memory_limit` do `PHP` é definido como `128M`. Como o `Hyperf` utiliza o pacote `BetterReflection` para realizar a análise de código, uma grande quantidade de memória pode ser consumida, e o processo `PHP` pode lançar exceções fatais quando a memória se esgotar.

Você pode executar os comandos com um argumento para aumentar o limite de memória `php -d memory_limit=-1 bin/hyperf.php start` ou modificar o arquivo de configuração `php.ini`:

```ini
# Look for the location of your php.ini file
php --ini

# Set the memory_limit within that file
memory_limit=-1
```

## Erro `Error while injecting dependencies into... No entry or class found...` ao injetar traits usando `#[Inject]`

Esse erro aparece quando você injeta uma trait usando namespaces via `Inject` e a classe que contém a sintaxe `use Trait;` usa um namespace conflitante. Este é um conceito complexo, mas os exemplos a seguir devem torná-lo simples:

```php
use Hyperf\HttpServer\Contract\ResponseInterface; # Namespace containing ResponseInterface class
use Hyperf\Di\Annotation\Inject;

trait TestTrait
{
    #[Inject]
    protected ResponseInterface $response;
}
```

Na trait acima, a classe `Hyperf\HttpServer\Contract\ResponseInterface` é injetada. Se a subclasse (a classe que usa essa trait) usar uma classe `ResponseInterface` com um namespace diferente, por exemplo `Psr\Http\Message\ResponseInterface`, isso fará com que a `ResponseInterface` injetada seja sobrescrita.

```php
use Psr\Http\Message\ResponseInterface; # A conflicting namespace containing a ResponseInterface class

class IndexController
{
    use TestTrait;
    // Error while injecting dependencies into App\Controller\IndexController: No entry or class found for 'Psr\Http\Message\ResponseInterface'
}
```

Esse problema pode ser corrigido usando os seguintes métodos:

* Crie um alias na subclasse para evitar conflitos: `use Psr\Http\Message\ResponseInterface as PsrResponseInterface;`
* Na versão `7.4` do `PHP`, você pode adicionar um tipo ao atributo dentro da classe trait: `protected ResponseInterface $response;`

## O `Hyperf` não executará comandos porque as extensões `gprc` ou `pcntl` não estão instaladas

A versão `2.2` do `Hyperf` requer a extensão `pcntl`; você pode verificar se ela está instalada executando o comando `php --ri pcntl`:

```
pcntl

pcntl support => enabled
```

Ao usar `grpc`, você deve habilitar o `fork support` para permitir a abertura de processos filhos adicionando o seguinte ao seu `php.ini`:

```
grpc.enable_fork_support=1;
```

## O valor de `open_websocket_protocol` é definido como `false` após receber o erro: `Swoole\Server::start(): require onReceive callback`

1. Verifique se o `Swoole` foi compilado com suporte a `http2`:

```
php --ri swoole | grep http2
http2 => enabled
```

Se o resultado desse comando estiver vazio, você precisa recompilar o `Swoole` com o parâmetro `--enabled-http2`

2. Verifique se o valor de configuração `open_http2_protocol` está definido como `true` no arquivo de configuração `config/autoload/server.php`

## O Comando não consegue ser encerrado corretamente

Após usar tecnologias de multiplexação como AMQP em um Comando, ele não conseguirá ser encerrado normalmente. Nesse caso, basta adicionar o seguinte código ao final da lógica de execução.

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;

CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
```

## Componente de upload OSS reporta erro de iconv

- correção do charset incorreto do Aliyun oss: https://github.com/aliyun/aliyun-oss-php-sdk/issues/101
- https://github.com/docker-library/php/issues/240#issuecomment-762438977
- https://github.com/docker-library/php/pull/1264

Ao usar o componente `aliyuncs/oss-sdk-php` para upload, um erro de iconv será reportado. Você pode tentar evitá-lo usando os seguintes métodos:

Ao usar a imagem `hyperf/hyperf:8.0-alpine-v3.12-swoole`

```
RUN apk --no-cache --allow-untrusted --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ add gnu-libiconv=1.15-r2
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so
```

Ao usar a imagem `hyperf/hyperf:8.0-alpine-v3.13-swoole`

```dockerfile
RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.13/community/gnu-libiconv=1.15-r3
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
```

## Falha na coleta do DI Reflection Manager

Quando ocorre uma exceção durante a fase de coleta do DI (por exemplo, um erro de namespace), pode ser gerada uma saída de log no seguinte formato.

- Código do serviço: verifique os arquivos e classes relacionados ao caminho no log.
- Código do framework: envie um PR com feedback.
- Componentes de terceiros: envie feedback ao autor do componente.

```bash
[ERROR] DI Reflection Manager collecting class reflections failed. 
File: xxxx.
Exception: xxxx
```

## O serviço não consegue iniciar porque a versão do ambiente está inconsistente

Quando o projeto inicia, um erro semelhante ao seguinte é lançado

```
Hyperf\Engine\Channel::push(mixed $data, float $timeout = -1): bool must be compatible with Swoole\Coroutine\Channel::push($data, $timeout = -1)
```

Esse problema geralmente é causado por inconsistências entre a versão do Swoole usada ao instalar frameworks/componentes e a versão real do Swoole usada em tempo de execução.

Você deve manter a versão do Swoole e do PHP consistentes ao instalar e usar.