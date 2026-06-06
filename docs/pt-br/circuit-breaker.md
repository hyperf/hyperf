# Circuit Breaker (Disjuntor de circuito)

## Instalação

```
composer require hyperf/circuit-breaker
```

## Por que você precisa de um Circuit Breaker?

Em sistemas distribuídos, é comum que todo o sistema fique indisponível por conta da indisponibilidade de um serviço básico. Esse fenômeno é chamado de efeito avalanche de serviço. Para responder a avalanches de serviço, uma prática comum é fazer downgrade de serviços. O componente [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) foi projetado para resolver esse problema.

## Uso

## Por que você precisa de um Circuit Breaker?

Em sistemas distribuídos, é comum que todo o sistema fique indisponível por conta da indisponibilidade de um serviço básico. Esse fenômeno é chamado de efeito avalanche de serviço. Para responder a avalanches de serviço, uma prática comum é fazer downgrade de serviços. O componente [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) foi projetado para resolver esse problema.

## Usando Circuit Breaker

O uso do Circuit Breaker é muito simples: basta adicionar a anotação `Hyperf\CircuitBreaker\Annotation\CircuitBreaker` e você poderá aplicar o circuit break conforme a estratégia especificada.

Por exemplo: precisamos consultar a lista de usuários em outro serviço. A lista de usuários precisa se relacionar com muitas tabelas. A eficiência da consulta é baixa, mas quando a concorrência é normal, a velocidade é razoável. Quando a concorrência aumenta, a resposta fica mais lenta e pode fazer o outro serviço desacelerar. Nesse momento, basta configurar o tempo limite do circuit break `timeout` como 0.05 segundos, o contador de falhas `failCounter` para estourar após mais de 1 falha, e o `fallback` como o método `searchFallback` da classe `App\UserService`. Assim, quando a resposta estourar o tempo limite e disparar o circuit break, ele não fará mais requisições ao serviço remoto; em vez disso, fará o downgrade do serviço na aplicação atual, retornando o resultado conforme o método definido em `fallback`.

```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\UserServiceClient;
use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use Hyperf\Di\Annotation\Inject;

class UserService
{
    #[Inject]
    private UserServiceClient $client;

    #[CircuitBreaker(options: ['timeout' => 0.05], failCounter: 1, successCounter: 1, fallback: [UserService::class, 'searchFallback'])]
    public function search($offset, $limit)
    {
        return $this->client->users($offset, $limit);
    }

    public function searchFallback($offset, $limit)
    {
        return [];
    }
}

```

A política padrão de circuit break é `Timeout Policy`. Se você quiser implementar sua própria política, basta implementar um `Handler` que herde `Hyperf\CircuitBreaker\Handler\AbstractHandler`.

```php
<?php
declare(strict_types=1);

namespace Hyperf\CircuitBreaker\Handler;

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker as Annotation;
use Hyperf\CircuitBreaker\CircuitBreaker;
use Hyperf\CircuitBreaker\Exception\TimeoutException;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class DemoHandler extends AbstractHandler
{
    const DEFAULT_TIMEOUT = 5;

    protected function process(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation)
    {
        $result = $proceedingJoinPoint->process();

        if (is_break()) {
            throw new TimeoutException('timeout, use ' . $use . 's', $result);
        }

        return $result;
    }
}

```

