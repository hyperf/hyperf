# Circuit Breaker

## Instalação

```
composer require hyperf/circuit-breaker
```

## Por que você precisa de um Circuit Breaker?

Em sistemas distribuídos, é comum que o sistema inteiro fique indisponível devido à indisponibilidade de um serviço básico. Esse fenômeno é chamado de efeito de avalanche de serviço (service avalanche effect). Em resposta às avalanches de serviço, uma prática comum é degradar (downgrade) os serviços. O componente [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) foi projetado para resolver esse problema.

## Uso

## Por que você precisa de um Circuit Breaker?

Em sistemas distribuídos, é comum que o sistema inteiro fique indisponível devido à indisponibilidade de um serviço básico. Esse fenômeno é chamado de efeito de avalanche de serviço. Em resposta às avalanches de serviço, uma prática comum é degradar os serviços. O componente [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) foi projetado para resolver esse problema.

## Usando o Circuit Breaker

O uso do Circuit Breaker é muito simples, basta adicionar a annotation `Hyperf\CircuitBreaker\Annotation\CircuitBreaker`, e você poderá fazer o circuit break de acordo com a estratégia especificada.
Por exemplo, precisamos consultar a lista de usuários em outro serviço. A lista de usuários precisa ser associada a muitas tabelas. A eficiência da consulta é baixa, mas quando o volume de concorrência é normal, a velocidade de resposta ainda é razoável. Assim que o volume de concorrência aumenta, isso vai deixar a resposta mais lenta e causar lentidão no outro serviço. Neste momento, basta configurarmos o período de timeout do circuit break `timeout` para 0.05 segundos, o contador de falhas `failCounter` para disparar o circuit break após mais de 1 falha, e o `fallback` correspondente para o método `searchFallback` da classe `App\UserService`. Desta forma, quando a resposta expirar e disparar o circuit break, ele não fará mais requisições ao serviço do outro lado. Em vez disso, ele degradará diretamente o serviço a partir da aplicação atual, ou seja, retornará de acordo com o método especificado por `fallback`.

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

A policy padrão de circuit break é a `Timeout Policy`. Se você quiser implementar a policy de circuit break você mesmo, basta implementar o `Handler` herdando de `Hyperf\CircuitBreaker\Handler\AbstractHandler`.

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
