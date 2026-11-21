# Conhecimentos antes de começar a programar

Aqui está uma coleção de conhecimentos ou conteúdos que devem ser conhecidos antes de programar com o Hyperf.

## Não é possível obter/definir parâmetros de propriedades através de variáveis globais

No `PHP-FPM`, você pode obter os parâmetros da requisição através de variáveis globais, parâmetros de servidor, etc. No `Hyperf` e no `Swoole`, ** não é possível ** obter nenhum parâmetro de propriedade através de `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER` e outras variáveis que começam com `$_`.

## Classes obtidas através do container são singletons

Através do container de injeção de dependências, toda a persistência dentro do processo é compartilhada por múltiplas coroutines, então ela não pode conter nenhum dado que seja exclusivo da requisição ou exclusivo da coroutine. Esse tipo de dado é processado através do contexto da coroutine. Leia com atenção as seções de [Dependency Injection](pt-br/di.md) e [Coroutine](pt-br/coroutine.md).

## Implantação

> O Dockerfile oficial já configura essas operações.

Ao implantar o ambiente de produção, certifique-se de habilitar o `scan_cacheable`.

Após habilitar essa configuração, a classe proxy e o cache de annotations serão gerados durante a primeira análise, e o cache pode ser usado diretamente quando o processo for reiniciado, o que otimiza bastante o uso de memória e o tempo de inicialização. Como a etapa de análise é ignorada, o `Composer Class Map` será utilizado, então é necessário executar a opção `--optimize-autoloader` do comando composer para otimizar o índice de classes.

Em resumo, ao atualizar o código do ambiente de produção, você precisa executar os seguintes comandos antes de reiniciar o projeto

```bash
# Optimize the composer class index
composer dump-autoload -o
# Generate all proxy classes and the annotation cache
php bin/hyperf.php
```

## Evite trocar de coroutine em métodos mágicos

> Não inclui os métodos __call e __callStatic

Tente evitar a troca entre coroutines nos métodos `__get`, `__set` e `__isset`, pois isso pode levar a um comportamento inesperado.

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

Quando executamos o código acima, ele retornará os seguintes resultados

```shell
bool(false)
string(3) "xxx"
bool(true)
```