# Limitador de taxa por token bucket

## Instalação

```bash
composer require hyperf/rate-limit
```
## Configuração

### Publicar configuração

```bash
php bin/hyperf.php vendor:publish hyperf/rate-limit
```

### Descrição da configuração

|  item de configuração   | padrão |         observação        |
|:--------------:|:-------:|:---------------------:|
| create         | 1       | Número de tokens gerados por segundo            |
| consume        | 1       | Número de tokens consumidos por requisição            |
| capacity       | 2       | Capacidade máxima do token bucket                 |
| limitCallback  | `[]`    | Método de callback quando o limite atual é disparado  |
| waitTimeout    | 1       | timeout na fila de espera                            |

## Uso

O componente fornece a annotation `Hyperf\RateLimit\Annotation\RateLimit`, que atua em classes e métodos de classe, e pode sobrescrever os arquivos de configuração. Por exemplo:

```php
<?php

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

#[Controller(prefix: "rate-limit")]
class RateLimitController
{
    #[RequestMapping(path: "test")]
    #[RateLimit(create: 1, capacity: 3)]
    public function test()
    {
        return ["QPS 1, Peek3"];
    }

    #[RequestMapping(path: "test2")]
    #[RateLimit(create: 2, consume: 2, capacity: 4)]
    public function test2()
    {
        return ["QPS 2, Peek2"];
    }
}
``` 
Prioridade de configuração: `Annotation de Método > Annotation de Classe > Arquivo de Configuração > Configuração Padrão`

## Disparo do limite atual
Quando o limite atual é disparado, a exceção `Hyperf\RateLimit\Exception\RateLimitException` será lançada por padrão.

Você pode usar o [Exception Handler](pt-br/exception-handler.md) ou configurar o `limitCallback` para tratar o callback de limite atual.

Por exemplo:
```php
<?php

namespace App\Controller;

use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

#[Controller(prefix: "rate-limit")]
#[RateLimit(limitCallback: {RateLimitController::class, "limitCallback"})]
class RateLimitController
{
    #[RequestMapping(path: "test")]
    #[RateLimit(create: 1, capacity: 3)]
    public function test()
    {
        return ["QPS 1, Peek3"];
    }
    
    public static function limitCallback(float $seconds, ProceedingJoinPoint $proceedingJoinPoint)
    {
        // $seconds Token generation time interval, in seconds
        // $proceedingJoinPoint The entry point for the execution of this request
        // You can handle it by yourself, or continue its execution by calling `$proceedingJoinPoint->process()`
        return $proceedingJoinPoint->process();
    }
}
```

## Chave de limitação personalizada do token bucket

A chave padrão é baseada na `url` da requisição atual. Quando um usuário dispara o limite atual, outros usuários também ficarão restritos a requisitar essa `url`;

Se for necessária a limitação atual em diferentes granularidades, como limitação atual na dimensão do usuário, a limitação atual pode ser feita com base no `ID` do usuário, de modo que o usuário A seja restringido e o usuário B possa requisitar normalmente:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;

class TestController
{
    #[RateLimit(create: 1, capacity: 3, key: {TestController::class, "getUserId"})]
    public function test()
    {
        return ["QPS 1, 峰值3"];
    }

    public static function getUserId(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        // In the same way, traffic can be limited based on different dimensions such as mobile phone number and IP address.
        return $request->input('user_id');
    }
}
```
