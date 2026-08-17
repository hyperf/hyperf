# Retry

A comunicação de rede é inerentemente instável, então em um sistema distribuído é necessário um bom design tolerante a falhas. Retentar indiscriminadamente é muito perigoso. Quando há um problema de comunicação e cada requisição é tentada novamente uma vez, isso equivale a um aumento de 100% na carga de I/O do sistema — o que pode facilmente induzir incidentes em cascata. Ao retentar, também é preciso considerar a causa do erro. Se for um problema que não pode ser resolvido com retentativas, retentar será apenas desperdício de recursos. Além disso, se a interface que está sendo retentada não for idempotente, isso também pode causar inconsistência de dados e outros problemas.

Este componente fornece um mecanismo de retry rico para atender aos requisitos de retentativa de vários cenários.


## Instalação

```bash
composer require hyperf/retry
```

## Hello World

Adicione a anotação `#[Retry]` ao método que precisa ser retentado.

```php
/**
 * Faz a retentativa em caso de exceção
 */
#[Retry]
public function foo()
{
    // faz uma chamada remota
}
```

A estratégia padrão de Retry atende a maioria das necessidades do dia a dia sem retentativas excessivas que causem avalanches.

## Personalização avançada

Este componente alcança “plugabilidade” combinando múltiplas estratégias de retry. Cada estratégia foca em diferentes aspectos do processo de retry, como decisão de retentar, intervalo entre tentativas e tratamento de resultado. Ajustando a estratégia usada na anotação, você consegue configurar o aspecto de retry adequado para qualquer cenário.

Recomenda-se construir seus próprios aliases de anotação conforme as necessidades específicas do negócio. Abaixo demonstramos como criar uma nova anotação com número máximo de tentativas igual a 3.

> Na anotação padrão `Retry`, você pode controlar o número máximo de retentativas com `#[Retry(maxAttempts=3)]`. Para fins de demonstração, finja que isso não existe.

Primeiro, você precisa criar uma `classe de anotação` e herdar `\Hyperf\Retry\Annotations\AbstractRetry`.

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\AbstractRetry
{
}
```

Sobrescreva a propriedade `$policies` conforme sua necessidade. Para limitar o número de retentativas, use `MaxAttemptsRetryPolicy`. `MaxAttemptsRetryPolicy` também precisa de um parâmetro, que é o limite do número máximo de tentativas, `$maxAttempts`. Adicione essas duas propriedades na classe acima.

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\AbstractRetry
{
    public $policies = [
        MaxAttemptsRetryPolicy::class,
    ];
    public $maxAttempts = 3;
}
```

Agora que a anotação `#[MyRetry]` fará com que qualquer método seja executado três vezes em loop, também precisamos adicionar uma nova policy `ClassifierRetryPolicy` para controlar quais erros podem ser retentados. Ao adicionar `ClassifierRetryPolicy`, por padrão ele só retentará quando lançar `Throwable`.

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\AbstractRetry
{
    public $policies = [
        MaxAttemptsRetryPolicy::class,
        ClassifierRetryPolicy::class,
    ];
    public $maxAttempts = 3;
}
```

Você pode continuar refinando a anotação até ela atender às suas necessidades personalizadas. Por exemplo, configurar para retentar apenas uma `TimeoutException` definida pelo usuário e usar um intervalo variável com pelo menos 100ms entre tentativas, como a seguir:

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\Retry
{
    public $policies = [
        MaxAttemptsRetryPolicy::class,
        ClassifierRetryPolicy::class,
        SleepRetryPolicy::class,
    ];
    public $maxAttempts = 3;
    public $base = 100;
    public $strategy = \Hyperf\Retry\BackoffStrategy::class;
    public $retryThrowables = [\App\Exception\TimeoutException::class];
}
```

Basta garantir que o arquivo seja escaneado pelo Hyperf, e você poderá usar a anotação `#[MyRetry]` no método para retentar erros de timeout.

## Configuração padrão

As propriedades padrão completas da anotação `#[Retry]` são as seguintes:

```php
/**
 * Array of retry policies. Think of these as stacked middlewares.
 * @var string[]
 */
public $policies = [
    FallbackRetryPolicy::class,
    ClassifierRetryPolicy::class,
    BudgetRetryPolicy::class,
    MaxAttemptsRetryPolicy::class,
    SleepRetryPolicy::class,
];

/**
 * The algorithm for retry intervals.
 */
public string $sleepStrategyClass = SleepStrategyInterface::class;

/**
 * Max Attampts.
 */
public int $maxAttempts = 10;

/**
 * Retry Budget.
 * ttl: Seconds of token lifetime.
 * minRetriesPerSec: Base retry token generation speed.
 * percentCanRetry: Generate new token at this ratio of the request volume.
 *
 * @var array|RetryBudgetInterface
 */
public $retryBudget = [
    'ttl' => 10,
    'minRetriesPerSec' => 1,
    'percentCanRetry' => 0.2,
];

/**
 * Base time inteval (ms) for each try. For backoff strategy this is the interval for the first try
 * while for flat strategy this is the interval for every try.
 */
public int $base = 0;

/**
 * Configures a Predicate which evaluates if an exception should be retried.
 * The Predicate must return true if the exception should be retried, otherwise it must return false.
 *
 * @var callable|string
 */
public $retryOnThrowablePredicate = '';

/**
 * Configures a Predicate which evaluates if an result should be retried.
 * The Predicate must return true if the result should be retried, otherwise it must return false.
 *
 * @var callable|string
 */
public $retryOnResultPredicate = '';

/**
 * Configures a list of Throwable classes that are recorded as a failure and thus are retried.
 * Any Throwable matching or inheriting from one of the list will be retried, unless ignored via ignoreExceptions.
 *
 * Ignoring an Throwable has priority over retrying an exception.
 *
 * @var array<string|\Throwable>
 */
public $retryThrowables = [\Throwable::class];

/**
 * Configures a list of error classes that are ignored and thus are not retried.
 * Any exception matching or inheriting from one of the list will not be retried, even if marked via retryExceptions.
 *
 * @var array<string|\Throwable>
 */
public $ignoreThrowables = [];

/**
 * The fallback callable when all attempts exhausted.
 *
 * @var callable|string
 */
public $fallback = '';
```

## Estratégias opcionais

### Maximum Attempts Policy `MaxAttemptsRetryPolicy`

| Parâmetros | Tipo | Descrição |
| ---------- | --- | --- |
| maxAttempts | int | Número máximo de tentativas |


### Error classification policy `ClassifierRetryPolicy`

Passe o classifier para determinar se o erro pode ser retentado.

| Parâmetros | Tipo | Descrição |
| ---------- | --- | --- |
| ignoreThrowables | array | Nomes de classes `Throwable` a ignorar. Tem prioridade sobre `retryThrowables` |
| retryThrowables | array | Nomes de classes `Throwable` a retentar. Tem prioridade sobre `retryOnThrowablePredicate` |
| retryOnThrowablePredicate | callable | Passe uma função para determinar se um `Throwable` pode ser retentado. Retorna true se for possível retentar, caso contrário false. |
| retryOnResultPredicate | callable | Use uma função para determinar se o valor de retorno pode ser retentado. Retorna true se for possível retentar, caso contrário false. |

### Fallback policy `FallbackRetryPolicy`

Executa um método alternativo após esgotar as tentativas.

| Parâmetros | Tipo | Descrição |
| ---------- | --- | --- |
| fallback | callable | método de fallback |

Além do código reconhecido por `is_callable`, `fallback` também pode ser preenchido no formato `class@method`. O framework obterá a `class` correspondente pelo `Container` e então executará o método `method`.

### Sleep policy `SleepRetryPolicy`

Fornece duas estratégias de intervalo entre retentativas: intervalo fixo (FlatStrategy) e intervalo variável (BackoffStrategy).

| Parâmetros | Tipo | Descrição |
| ---------- | --- | --- |
| base | int | Tempo base de sleep (ms) |
| strategy | string | Qualquer nome de classe que implemente `Hyperf\Retry\SleepStrategyInterface`, como `Hyperf\Retry\BackoffStrategy` |

### Timeout policy `TimeoutRetryPolicy`

Encerra a sessão de retry quando o tempo total de execução exceder o limite.

| Parâmetros | Tipo | Descrição |
| ---------- | --- | --- |
| timeout | float | timeout (segundos) |

### Circuit breaker policy `CircuitBreakerRetryPolicy`

Após o retry falhar, a sessão de retry é marcada como circuit breaker por um período de tempo, e nenhuma nova tentativa será feita.

| Parâmetros | Tipo | Descrição |
| ---------- | --- | --- |
| circuitBreakerState.resetTimeout | float | Tempo necessário para recuperação (segundos) |

### Budget Policy `BudgetRetryPolicy`

Cada anotação `#[Retry]` gera um token bucket correspondente. Sempre que o método anotado é chamado, um token com tempo de expiração (ttl) é colocado no token bucket. Se ocorrer um erro retentável, a quantidade correspondente de tokens (percentCanRetry) precisa ser consumida antes de retentar; caso contrário, não haverá retentativa (o erro continuará propagando). Por exemplo, quando percentCanRetry=0.2, cada retentativa consome 5 tokens. Assim, quando o serviço do outro lado estiver indisponível, no máximo 20% de consumo adicional devido a retentativas ocorrerá, o que deve ser aceitável para a maioria dos sistemas.

Para contemplar alguns métodos menos frequentemente usados, também é gerada por segundo uma certa quantidade de tokens de “mini-garantia” (minRetriesPerSec) para assegurar a estabilidade do sistema.

| Parâmetros | Tipo | Descrição |
| ---------- | --- | --- |
| retryBudget.ttl | int | Tempo de expiração do token de recuperação (segundos) |
| retryBudget.minRetriesPerSec | int | Número mínimo de retentativas por segundo para “mini-garantia” |
| retryBudget.percentCanRetry | float | Retentativas não excedem a porcentagem do total de requisições |

> O token bucket do componente de retry não é compartilhado entre workers, então o número final de retentativas é multiplicado pelo número de workers.

## Alias de anotação

Como a configuração da anotação de retry é mais complexa, alguns aliases predefinidos são fornecidos aqui para facilitar a escrita.

* `#[RetryThrowable]` retenta apenas `Throwable`. É o mesmo que o `#[Retry]` padrão.

* `#[RetryFalsy]` retenta apenas quando o valor de retorno é fracamente igual a false ($result == false), não exceções.

* `#[BackoffRetryThrowable]` é uma versão com intervalo de retry variável do `#[RetryThrowable]`, com intervalo mínimo de 100ms.

* `#[BackoffRetryFalsy]` é uma versão com intervalo de retry variável do `#[ã€]RetryFalsy]`, com intervalo mínimo de 100ms.

## Chamada encadeada (fluent)

Além de usar este componente com métodos anotados, você também pode usá-lo com funções PHP comuns.

```php
<?php

$result = \Hyperf\Retry\Retry::with(
    new \Hyperf\Retry\Policy\ClassifierRetryPolicy(), // Retry all Throwables by default
    new \Hyperf\Retry\Policy\MaxAttemptsRetryPolicy(5) //Retry up to 5 times
)->call(function(){
    if (rand(1, 100) >= 20){
        return true;
    }
    throw new Exception;
});
```
Para melhorar a legibilidade, a escrita fluent abaixo também pode ser usada.

```php
<?php

$result = \Hyperf\Retry\Retry::whenReturns(false) // Retry when false is returned
    ->max(3) // up to 3 times
    ->inSeconds(5) // up to 5 seconds
    ->sleep(1) // 1ms interval
    ->fallback(function(){return true;}) // fallback function
    ->call(function(){
        if (rand(1, 100) >= 20){
            return true;
        }
        return false;
    });
```
