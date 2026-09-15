# Retry

A comunicação em rede é inerentemente instável, portanto, em um sistema distribuído, é necessário um bom design de tolerância a falhas. Retentativas indiscriminadas são muito perigosas. Quando há um problema com a comunicação, cada requisição é retentada uma vez, o que equivale a um aumento de 100% na carga de IO do sistema, algo que facilmente pode induzir acidentes de avalanche. A retentativa também deve considerar a causa do erro. Se for um problema que não pode ser resolvido por retentativa, então a retentativa é apenas um desperdício de recursos. Além disso, se a interface retentada não for idempotente, isso também pode causar problemas de inconsistência de dados, entre outros.

Este componente fornece um mecanismo de retentativa rico para atender às necessidades de retentativa em diversos cenários.


## Instalação

```bash
composer require hyperf/retry
```

## Hello World

Adicione a annotation `#[Retry]` ao método que precisa de retentativa.

```php
/**
 * Retry the method on exception
 */
#[Retry]
public function foo()
{
    // make a remote call
}
```

A estratégia padrão de Retry pode atender à maioria das necessidades diárias de retentativa sem causar avalanches por retentativas excessivas.

## Personalização profunda

Este componente alcança a capacidade de plugin combinando múltiplas estratégias de retentativa. Cada estratégia foca em um aspecto diferente do processo de retentativa, como o julgamento da retentativa, o intervalo de retentativa e o processamento do resultado. Ao ajustar a estratégia usada na annotation, você pode configurar o aspecto de retentativa adequado para qualquer cenário.

Recomenda-se construir seus próprios apelidos (aliases) de annotation de acordo com as necessidades específicas do negócio. Abaixo, demonstramos como criar uma nova annotation com um número máximo de tentativas igual a 3.

> Na annotation `Retry` padrão, você pode controlar o número máximo de retentativas com `#[Retry(maxAttempts=3)]`. Para fins de demonstração, finja que isso não existe.

Primeiro, você precisa criar uma `classe de annotation` e herdar de `\Hyperf\Retry\Annotations\AbstractRetry`.

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

Sobrescreva a propriedade `$policies` de acordo com suas necessidades. Para limitar o número de retentativas, use `MaxAttemptsRetryPolicy`. `MaxAttemptsRetryPolicy` também precisa de um parâmetro, que é o limite do número máximo de tentativas, `$maxAttempts`. Adicione essas duas propriedades à classe acima.

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

Agora que a annotation `#[MyRetry]` fará com que qualquer método seja executado três vezes em loop, também precisamos adicionar uma nova policy, `ClassifierRetryPolicy`, para controlar quais erros podem ser retentados. Adicionar `ClassifierRetryPolicy` fará com que a retentativa só ocorra após o lançamento de `Throwable` por padrão.

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

Você pode continuar refinando a annotation até que ela atenda às suas necessidades personalizadas. Por exemplo, configure para retentar apenas a exceção `TimeoutException` definida pelo usuário, e use a retentativa para dormir pelo menos 100ms de intervalo de comprimento variável, como a seguir:

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

Apenas certifique-se de que o arquivo seja escaneado pelo Hyperf, e você poderá usar a annotation `#[MyRetry]` no método para retentar erros de timeout.

## Configuração padrão

As propriedades padrão completas da annotation `#[Retry]` são as seguintes:

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

### Policy de número máximo de tentativas `MaxAttemptsRetryPolicy`

| Parâmetro | Tipo | Descrição |
| ---------- | --- | --- |
| maxAttempts | int | Número máximo de tentativas |


### Policy de classificação de erros `ClassifierRetryPolicy`

Passe o classificador para determinar se o erro pode ser retentado.

| Parâmetro | Tipo | Descrição |
| ---------- | --- | --- |
| ignoreThrowables | array | Nomes de classes `Throwable` a serem ignoradas. Tem precedência sobre `retryThrowables` |
| retryThrowables | array | Nomes de classes `Throwable` a serem retentadas. Tem precedência sobre `retryOnThrowablePredicate` |
| retryOnThrowablePredicate | callable | Passe uma função para determinar se o `Throwable` pode ser retentado. Retorna true se a retentativa é possível, false caso contrário. |
| retryOnResultPredicate | callable | Use uma função para determinar se o valor de retorno pode ser retentado. Retorna true se for possível retentar, false caso contrário. |

### Policy de fallback `FallbackRetryPolicy`

Executa um método alternativo após o esgotamento dos recursos de retentativa.

| Parâmetro | Tipo | Descrição |
| ---------- | --- | --- |
| fallback | callable | método de fallback |

Além do código reconhecido por `is_callable`, `fallback` também pode ser preenchido no formato `class@method`; o framework obterá a `class` correspondente do `Container` e então executará seu método `method`.

### Policy de sleep `SleepRetryPolicy`

Fornece duas estratégias de intervalo de retentativa. Intervalo de retentativa igual (FlatStrategy) e intervalo de retentativa variável (BackoffStrategy).

| Parâmetro | Tipo | Descrição |
| ---------- | --- | --- |
| base | int | Tempo base de sleep (ms) |
| strategy | string | Qualquer nome de classe que implemente `Hyperf\Retry\SleepStrategyInterface`, como `Hyperf\Retry\BackoffStrategy` |

### Policy de timeout `TimeoutRetryPolicy`

Sai da sessão de retentativa após o tempo total de execução exceder o tempo determinado.

| Parâmetro | Tipo | Descrição |
| ---------- | --- | --- |
| timeout | float | timeout (segundos) |

### Policy de circuit breaker `CircuitBreakerRetryPolicy`

Após a falha da retentativa, a sessão de retentativa é marcada diretamente como em circuit break por um período de tempo, e nenhuma nova tentativa será feita.

| Parâmetro | Tipo | Descrição |
| ---------- | --- | --- |
| circuitBreakerState.resetTimeout | float | Tempo necessário para recuperação (segundos) |

### Policy de orçamento `BudgetRetryPolicy`

Cada annotation `#[Retry]` gerará um token bucket correspondente, e sempre que o método anotado for chamado, um token com um tempo de expiração (ttl) é colocado no token bucket. Se ocorrer um erro retentável, o número correspondente de tokens (percentCanRetry) deve ser consumido antes de retentar; caso contrário, não será retentado (o erro continua a se propagar). Por exemplo, quando percentCanRetry=0.2, cada retentativa consome 5 tokens. Dessa forma, quando o outro lado está indisponível, no máximo 20% de consumo adicional de retentativa será incorrido, o que deve ser aceitável para a maioria dos sistemas.

Para cuidar de alguns métodos usados com menor frequência, um certo número de tokens de "mini-garantia" (minRetriesPerSec) também é gerado por segundo para garantir a estabilidade do sistema.

| Parâmetro | Tipo | Descrição |
| ---------- | --- | --- |
| retryBudget.ttl | int | Tempo de expiração do token de recuperação (segundos) |
| retryBudget.minRetriesPerSec | int | Número mínimo de retentativas por segundo para "mini-garantia" |
| retryBudget.percentCanRetry | float | O número de retentativas não excede a porcentagem do total de requisições |

> O token bucket do componente de retry não é compartilhado entre os workers, então o número final de retentativas é multiplicado pelo número de workers.

## Apelido de annotation

Como a configuração da annotation de retry é mais complicada, aqui são fornecidos alguns apelidos (aliases) predefinidos para facilitar a escrita.

* `#[RetryThrowable]` retenta apenas `Throwable`. Igual ao `#[Retry]` padrão.

* `#[RetryFalsy]` retenta apenas erros cujo valor de retorno é fracamente igual a false ($result == false), não exceções.

* `#[BackoffRetryThrowable]` Uma versão com intervalo de retentativa de comprimento variável do `#[RetryThrowable]`, com um intervalo de retentativa de pelo menos 100ms.

* `#[BackoffRetryFalsy]` Versão com intervalo de retentativa de comprimento variável do `#[RetryFalsy]`, o intervalo de retentativa é de pelo menos 100ms.

## Chamada fluente (fluent chain)

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
Para melhorar a legibilidade, a seguinte escrita fluente também pode ser usada.

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
