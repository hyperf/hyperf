# Rate limiter de token bucket

## Instalação

```bash
composer require hyperf/rate-limit
```

## Configuração

### Publicar config

```bash
php bin/hyperf.php vendor:publish hyperf/rate-limit
```

### Descrição da config

| Item de config  | Padrão | Observação                                   |
|:--------------:|:------:|:--------------------------------------------:|
| create         | 1      | Número de tokens gerados por segundo         |
| consume        | 1      | Número de tokens consumidos por requisição   |
| capacity       | 2      | Capacidade máxima do token bucket            |
| limitCallback  | `[]`   | Callback quando o limite atual é acionado    |
| waitTimeout    | 1      | Timeout na fila de espera                    |

## Uso

O componente fornece a anotação `Hyperf\RateLimit\Annotation\RateLimit`, que pode ser aplicada em classes e métodos de classe, e pode sobrescrever o arquivo de configuração. Por exemplo:

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

Prioridade de configuração: `Method Annotation > Class Annotation > Configuration File > Default Configuration`

## Acionar o limite atual

Quando o limite atual é acionado, a exceção `Hyperf\RateLimit\Exception\RateLimitException` será lançada por padrão.

Você pode usar [Exception Handler](pt-br/exception-handler.md) ou configurar `limitCallback` para tratar o callback do limite.

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
        // $seconds intervalo de tempo de geração de token, em segundos
        // $proceedingJoinPoint ponto de entrada para a execução desta requisição
        // Você pode tratar por conta própria ou continuar a execução chamando `$proceedingJoinPoint->process()`
        return $proceedingJoinPoint->process();
    }
}
```

## Customizar a chave do rate limit (token bucket)

A chave padrão é baseada na `url` da requisição atual. Quando um usuário aciona o rate limit, outros usuários também ficam restritos ao requisitar essa `url`.

Se for necessário aplicar rate limit em diferentes granularidades — por exemplo, por usuário — você pode usar o `ID` do usuário como base, de modo que o usuário A seja limitado e o usuário B possa requisitar normalmente:

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
        // Da mesma forma, é possível limitar tráfego com base em dimensões como número de telefone e endereço IP.
        return $request->input('user_id');
    }
}
```

