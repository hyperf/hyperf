# Snowflake

## Introdução ao algoritmo

`Snowflake` é um algoritmo distribuído de geração de IDs globais únicos proposto pelo Twitter. O resultado do algoritmo ao gerar um `ID` é um inteiro longo de `64bit`. No algoritmo padrão, sua estrutura é mostrada na figura abaixo:

![snowflake](imgs/snowflake.jpeg)

- `1 bit`, não utilizado.
  - O bit mais alto no sistema binário é o bit de sinal. O `ID` gerado normalmente é um inteiro positivo, então o bit mais alto fica fixo em 0.

- `41 bits` para registrar o timestamp (MS).
  - `41 bits` podem representar `2^41 - 1` números.
  - Em outras palavras, `41 bits` podem representar o valor de `2^41 - 1` milissegundos; convertendo para anos, `(2^41 - 1) / (1000 * 60 * 60 * 24 * 365)` dá aproximadamente `69` anos.

- `10 bits`, usados para registrar o `ID` da máquina de trabalho.
  - Pode ser implantado em `2^10` nós, incluindo `5` bits `DatacenterId` e `5` bits `WorkerId`.

- `12 bits`, número de série, usado para registrar diferentes `id` gerados no mesmo milissegundo.
  - `12 bits` podem representar o máximo de inteiros positivos `2^12 - 1`, totalizando `4095` números; isso representa `4095` IDs gerados pela mesma máquina no mesmo intervalo de tempo (MS).

O `Snowflake` consegue garantir que:

- Todos os `ID` gerados aumentem com a tendência do tempo.
  - Não haverá geração de `ID` duplicado em todo o sistema distribuído (pois há distinção entre `DatacenterId (5 bits)` e `WorkerId (5 bits)`.

O componente [hyperf/snowflake](https://github.com/hyperf/snowflake) oferece boa extensibilidade no design, permitindo implementar outras variações baseadas em snowflake com extensões simples.

## Instalação

```
composer require hyperf/snowflake
```

## Uso

O framework fornece `MetaGeneratorInterface` e `IdGeneratorInterface`. `MetaGeneratorInterface` gera arquivos `Meta` do `ID`, e `IdGeneratorInterface` gera `distributed ID` com base nos arquivos `Meta` correspondentes.

O `MetaGeneratorInterface` usado por padrão pelo framework é um `millisecond level generator` baseado em `Redis`.

O arquivo de configuração fica em `config/autoload/snowflake.php`. Se esse arquivo não existir, você pode executar `php bin/hyperf.php vendor:publish hyperf/snowflake` para criar uma configuração padrão. O conteúdo do arquivo de configuração é o seguinte:

```php
<?php

declare(strict_types=1);

use Hyperf\Snowflake\MetaGenerator\RedisMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGenerator\RedisSecondMetaGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;

return [
    'begin_second' => MetaGeneratorInterface::DEFAULT_BEGIN_SECOND,
    RedisMilliSecondMetaGenerator::class => [
        // Redis Pool
        'pool' => 'default',
        // To calculate the Key of WorkerId
        'key' => RedisMilliSecondMetaGenerator::DEFAULT_REDIS_KEY
    ],
    RedisSecondMetaGenerator::class => [
        // Redis Pool
        'pool' => 'default',
        // To calculate the Key of WorkerId
        'key' => RedisMilliSecondMetaGenerator::DEFAULT_REDIS_KEY
    ],
];

```

Usar `Snowflake` no framework é bem simples. Você só precisa obter o objeto `IdGeneratorInterface` do `DI`.

```php
<?php
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(IdGeneratorInterface::class);

$id = $generator->generate();
```

Quando você sabe que um `ID` precisa reverter o `Meta` correspondente, basta chamar `degenerate`.

```php
<?php
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(IdGeneratorInterface::class);

$meta = $generator->degenerate($id);
```

## Sobrescrever o gerador de `Meta`

Existem muitas formas de implementar um `distributed global unique ID`, e também existem muitas variantes baseadas no algoritmo `Snowflake`. Embora sejam todos algoritmos `Snowflake`, eles não são iguais. Por exemplo, alguém pode gerar um `Meta` com base em `UserId` em vez de `WorkerId`. A seguir, vamos implementar um `MetaGenerator` simples.

Em resumo, `UserId` certamente excederá `10 bits`. Portanto, o `DataCenterId` e `WorkerId` padrão não podem ser usados. Assim, precisamos usar um módulo do `UserId`.

```php
<?php

declare(strict_types=1);

use Hyperf\Snowflake\IdGenerator;

class UserDefinedIdGenerator
{
    /**
     * @var IdGenerator\SnowflakeIdGenerator
     */
    protected $idGenerator;

    public function __construct(IdGenerator\SnowflakeIdGenerator $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function generate(int $userId)
    {
        $meta = $this->idGenerator->getMetaGenerator()->generate();

        return $this->idGenerator->generate($meta->setWorkerId($userId % 31));
    }

    public function degenerate(int $id)
    {
        return $this->idGenerator->degenerate($id);
    }
}

use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(UserDefinedIdGenerator::class);
$userId = 20190620;

$id = $generator->generate($userId);

```

## Aplicação em modelos de banco de dados

Depois de configurar o `Snowflake`, podemos fazer um model de banco de dados usar diretamente o `ID` do `Snowflake` como chave primária.

```php
<?php

class User extends \Hyperf\Database\Model\Model {
    use \Hyperf\Snowflake\Concern\Snowflake;
}
```

Quando o model de usuário é criado, o algoritmo `Snowflake` será usado por padrão para gerar a chave primária.
