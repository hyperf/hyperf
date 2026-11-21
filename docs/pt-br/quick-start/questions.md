# FAQ

## Os nomes curtos de funções do Swoole não foram desativados

```
[ERROR] Swoole short function names must be disabled before the server starts, please set swoole.use_shortname = 'Off' in your php.ini.
```

Você precisa adicionar `swoole.use_shortname ='Off'` no arquivo de configuração php.ini

> Observe que esta configuração DEVE ser definida no php.ini e NÃO PODE ser sobrescrita pela função ini_set().

Você também pode iniciar o servidor com o comando abaixo, desativando os nomes curtos de funções do Swoole ao executar o comando PHP:

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## Perda de mensagens em filas assíncronas

Se o método `handle` não estiver sendo executado ao usar o componente `async-queue`, verifique as seguintes possibilidades:

1. O `Redis` está sendo compartilhado com outro projeto ou outros usuários, e as mensagens estão sendo consumidas por esses projetos ou usuários?
2. Existem processos antigos ainda em execução que podem estar consumindo as mensagens?

O procedimento abaixo oferece uma solução simples para ambos os problemas:
   
1. Execute o comando `killall php` no seu `console`
2. Modifique a configuração `channel` do seu `async-queue`
   
## Erro `Swoole\Error: API must be called in the coroutine` ao usar o componente `hyperf/amqp`
   
Defina o valor de configuração `close_on_destruct` como `false` no arquivo `config/autoload/amqp.php`.

## Todas as requisições retornam 404 ao usar o Swoole 4.5 e o componente `view`
    
Se você está usando o Swoole 4.5 e o componente `view` e está ocorrendo um erro `404`, você pode tentar remover o item de configuração `static_handler_locations` do arquivo `config/autoload/server.php`.
    
Esse valor de configuração contém um caminho que será considerado uma rota de `static file`; portanto, se o valor for `/`, todas as requisições serão tratadas como arquivos, resultando em erros 404.

## Alterações no código não têm efeito
   
Se nada mudar quando você modificar o código da sua aplicação `Hyperf`, execute o comando abaixo:
   
```bash
composer dump-autoload -o
```
   
Durante o desenvolvimento, NÃO defina o valor de configuração `scan_cacheable` como `true`, pois isso fará com que o arquivo não seja reprocessado quando o `collector cache` estiver sendo usado. Além disso, o `Dockerfile` do pacote oficial `hyperf-skeleton` deixa essa configuração habilitada por padrão. Ao desenvolver no ambiente `Docker`, defina `scan_cacheable` como `false`.

> Quando a variável de ambiente `SCAN_CACHEABLE` existir, essa configuração não pode ser modificada em nenhum arquivo `.env`.

## Erro de sintaxe ao iniciar o servidor

A exceção abaixo é lançada quando o servidor `Hyperf` inicia:

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

Execute `composer analyse` para inicializar uma varredura estática do código-fonte e localizar o problema.

Normalmente esse problema é causado por estar usando a versão `3.0.5` de [zircote/swagger](https://github.com/zircote/swagger-php); veja [#834](https://github.com/zircote/swagger-php/issues/834) para mais informações.

Se você instalou [hyperf/swagger](https://github.com/hyperf/swagger), fixe a versão de [zircote/swagger](https://github.com/zircote/swagger-php) em `3.0.4`.

## O `Hyperf` não inicia porque o memory_limit é pequeno demais

Por padrão, o `memory_limit` do `PHP` é definido como `128M`. Como o `Hyperf` usa o pacote `BetterReflection` para fazer análise de código, uma grande quantidade de memória pode ser consumida e o processo `PHP` pode lançar exceções fatais ao ficar sem memória.

Você pode executar comandos com um argumento para aumentar o limite de memória (`php -d memory_limit=-1 bin/hyperf.php start`) ou modificar o arquivo de configuração `php.ini`:

```ini
# Encontre o local do seu arquivo php.ini
php --ini

# Defina o memory_limit nesse arquivo
memory_limit=-1
```

## Erro `Error while injecting dependencies into... No entry or class found...` ao injetar traits usando `#[Inject]`

Esse erro aparece quando você injeta um trait usando namespaces via `Inject` e a classe que contém a sintaxe `use Trait;` usa um namespace conflitante. É um conceito complexo, mas os exemplos a seguir devem simplificar:

```php
use Hyperf\HttpServer\Contract\ResponseInterface; # Namespace contendo a classe ResponseInterface
use Hyperf\Di\Annotation\Inject;

trait TestTrait
{
    #[Inject]
    protected ResponseInterface $response;
}
```

No trait acima, a classe `Hyperf\HttpServer\Contract\ResponseInterface` é injetada. Se a subclasse (a classe que usa este trait) usar uma classe `ResponseInterface` com um namespace diferente — por exemplo `Psr\Http\Message\ResponseInterface` — isso fará com que o `ResponseInterfece` injetado seja sobrescrito.

```php
use Psr\Http\Message\ResponseInterface; # Um namespace conflitante que contém uma classe ResponseInterface

class IndexController
{
    use TestTrait;
    // Erro ao injetar dependências em App\Controller\IndexController: nenhuma entrada ou classe encontrada para 'Psr\Http\Message\ResponseInterface'
}
```

Esse problema pode ser resolvido com os métodos abaixo:

* Crie um alias na subclasse para evitar conflito: `use Psr\Http\Message\ResponseInterface as PsrResponseInterface;`
* No `PHP` `7.4`, você pode adicionar um tipo ao atributo dentro da classe do trait: `protected ResponseInterface $response;`

## O `Hyperf` não executa comandos porque as extensões `gprc` ou `pcntl` não estão instaladas

A versão `2.2` do `Hyperf` exige a extensão `pcntl`; você pode verificar se ela está instalada executando `php --ri pcntl`:

```
pcntl

pcntl support => enabled
```

Ao usar `grpc`, você precisa habilitar `fork support` para permitir a abertura de processos filhos adicionando o seguinte ao seu `php.ini`:

```
grpc.enable_fork_support=1;
```

## O valor de `open_websocket_protocol` é definido como `false` após o erro: `Swoole\Server::start(): require onReceive callback`

1. Verifique se o `Swoole` foi compilado com suporte a `http2`:

```
php --ri swoole | grep http2
http2 => enabled
```

Se o resultado desse comando estiver vazio, você precisa recompilar o `Swoole` com o parâmetro `--enabled-http2`

2. Verifique se o valor de configuração `open_http2_protocol` está definido como `true` no arquivo `config/autoload/server.php`

## O comando não pode ser encerrado corretamente

Depois de usar tecnologias de multiplexação como AMQP em um Command, ele pode não conseguir encerrar normalmente. Nesse caso, basta adicionar o código abaixo ao final da lógica de execução.

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;

CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
```

## O componente de upload OSS reporta erro de iconv

- corrigir charset incorreto no Aliyun OSS: https://github.com/aliyun/aliyun-oss-php-sdk/issues/101
- https://github.com/docker-library/php/issues/240#issuecomment-762438977
- https://github.com/docker-library/php/pull/1264

Ao usar o componente `aliyuncs/oss-sdk-php` para upload, um erro de iconv será reportado. Você pode tentar evitar isso usando os métodos abaixo:

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

## Falha ao coletar no DI Reflection Manager

Quando ocorre uma exceção durante a fase de coleta do DI (por exemplo, um erro de namespace), pode ser gerada a saída de log no formato abaixo.

- Código do serviço: verifique os arquivos e classes relacionados ao caminho no log.
- Código do framework: envie feedback via PR.
- Componentes de terceiros: dê feedback ao autor do componente.

```bash
[ERROR] DI Reflection Manager collecting class reflections failed. 
File: xxxx.
Exception: xxxx
```

## O serviço não inicia porque a versão do ambiente é inconsistente

Quando o projeto inicia, é lançado um erro semelhante ao seguinte

```
Hyperf\Engine\Channel::push(mixed $data, float $timeout = -1): bool must be compatible with Swoole\Coroutine\Channel::push($data, $timeout = -1)
```

Esse problema normalmente é causado por inconsistências entre a versão do Swoole usada na instalação dos frameworks/componentes e a versão do Swoole realmente usada em runtime.

Mantenha as versões de Swoole e PHP consistentes ao instalar e usar.

