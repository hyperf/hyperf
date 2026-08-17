# Conhecimentos antes de começar a programar

Aqui está uma coleção de conhecimentos e conteúdos que devem ser conhecidos antes de programar com Hyperf.

## Não é possível obter/definir parâmetros de propriedades via variáveis globais

Em `PHP-FPM`, você pode obter os parâmetros da requisição por variáveis globais, parâmetros do servidor, etc. Já no `Hyperf` e no `Swoole`, **não** é possível obter parâmetros de atributos por `$_GET/$_POST/$_REQUEST/$ _SESSION/$_COOKIE/$_SERVER` e outras variáveis que começam com `$_`.

## Classes obtidas pelo container são singletons

Via o container de injeção de dependências, tudo o que persiste no processo é compartilhado por múltiplas corrotinas; portanto, não pode conter nenhum dado exclusivo da requisição ou exclusivo da corrotina. Esse tipo de dado deve ser tratado via contexto de corrotina. Leia com atenção as seções [Dependency Injection](pt-br/di.md) e [Coroutine](pt-br/coroutine.md).

## Implantação

> O Dockerfile oficial já configurou essas operações.

Ao implantar em ambiente de produção, certifique-se de habilitar `scan_cacheable`.

Depois de habilitar essa configuração, a classe proxy e o cache de anotações serão gerados durante a primeira varredura, e o cache poderá ser usado diretamente quando o projeto for reiniciado, o que otimiza bastante o uso de memória e o tempo de inicialização. Como a etapa de varredura é pulada, o `Composer Class Map` será utilizado; portanto, precisamos executar a opção `--optimize-autoloader` do comando do composer para otimizar o índice de classes.

Em resumo: ao atualizar o código no ambiente de produção, você precisa executar os seguintes comandos antes de reiniciar o projeto:

```bash
# Otimizar o índice de classes do composer
composer dump-autoload -o
# Gerar todas as classes proxy e o cache de anotações
php bin/hyperf.php
```

## Evite alternar corrotinas em métodos mágicos

> Não inclui os métodos __call e __callStatic.

Tente evitar alternar entre corrotinas em `__get`, `__set` e `__isset`, pois isso pode levar a comportamentos inesperados.

```php
<?php

require_once 'vendor/autoload.php';

use function Hyperf\Coroutine\go;

Swoole\Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

class Foo
{
    public function __get(string $name)
    {
        sleep(1);
        return $name;
    }

    public function __set(string $name, mixed $value)
    {
        sleep(1);
        var_dump($name, $value);
    }

    public function __isset(string $name): bool
    {
        sleep(1);
        var_dump($name);
        return true;
    }
}

$foo = new Foo();
go(static function () use ($foo) {
    var_dump(isset($foo->xxx));
});

go(static function () use ($foo) {
    var_dump(isset($foo->xxx));
});

\Swoole\Event::wait();
```

Quando executamos o código acima, ele retornará os seguintes resultados:

```shell
bool(false)
string(3) "xxx"
bool(true)
```
