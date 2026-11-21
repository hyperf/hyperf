# Script de criação de model

O Hyperf fornece comandos para criar models, e você pode criar facilmente os models correspondentes com base em tabelas. O comando gera o model via `AST`, então, mesmo quando você adiciona alguns métodos, também é fácil “resetar” o model com o script.

```bash
php bin/hyperf.php gen:model table_name
```

## Criar um model

Os parâmetros opcionais são os seguintes:

|     Parâmetro     | Tipo   | Padrão                            | Observação                                                                 |
|:-----------------:|:------:|:---------------------------------:|:--------------------------------------------------------------------------:|
| --pool            | string | `default`                         | Pool de conexão; o script será gerado com base na configuração do pool atual |
| --path            | string | `app/Model`                       | Caminho do model                                                          |
| --force-casts     | bool   | `false`                           | Se deve forçar o reset do parâmetro `casts`                                |
| --prefix          | string | string vazia                      | Prefixo da tabela                                                         |
| --inheritance     | string | `Model`                           | Classe pai                                                                |
| --uses            | string | `Hyperf\DbConnection\Model\Model` | Usar em conjunto com `inheritance`                                         |
| --refresh-fillable| bool   | `false`                           | Se deve atualizar o parâmetro `fillable`                                   |
| --table-mapping   | array  | `[]`                              | Adiciona um mapeamento table name -> model, por exemplo `['users:Account']` |
| --ignore-tables   | array  | `[]`                              | Tabelas que não precisam gerar model, por exemplo `['users']`              |
| --with-comments   | bool   | `false`                           | Se deve adicionar comentários dos campos                                   |
| --property-case   | int    | `0`                               | Tipo do campo: 0 Snakecase, 1 CamelCase                                    |

Ao usar `--property-case` para converter o tipo de campo para camelCase, você também precisa adicionar manualmente `Hyperf\Database\Model\Concerns\CamelCase` ao model.
A configuração correspondente também pode ser configurada em `databases.{pool}.commands.gen:model`, como a seguir:

> Tudo o que estiver com traços precisa ser convertido para underscore

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // Ignora outras configurações
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
                'uses' => '',
                'refresh_fillable' => true,
                'table_mapping' => [],
                'with_comments' => true,
                'property_case' => ModelOption::PROPERTY_SNAKE_CASE,
            ],
        ],
    ],
];
```

O model criado é o seguinte:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property $id
 * @property $name
 * @property $gender
 * @property $created_at
 * @property $updated_at
 */
class User extends Model
{
    /**
     * A tabela associada ao model.
     */
    protected ?string $table = 'user';

    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected array $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     */
    protected array $casts = ['id' => 'integer', 'gender' => 'integer'];
}
```

## Visitors

O framework fornece vários `Visitors` para que usuários possam estender as capacidades do script. O uso é bem simples: basta adicionar o `Visitor` correspondente na configuração `visitors`.

```php
<?php

declare(strict_types=1);

return [
    'default' => [
        // Ignora outras configurações
        'commands' => [
            'gen:model' => [
                'visitors' => [
                    Hyperf\Database\Commands\Ast\ModelRewriteKeyInfoVisitor::class
                ],
            ],
        ],
    ],
];
```

### Visitors opcionais

- Hyperf\Database\Commands\Ast\ModelRewriteKeyInfoVisitor

Esse `Visitor` pode gerar os valores correspondentes de `$incrementing`, `$primaryKey` e `$keyType` de acordo com a chave primária no banco de dados.

- Hyperf\Database\Commands\Ast\ModelRewriteSoftDeletesVisitor

Esse `Visitor` pode determinar se o model contém campos de soft delete de acordo com a constante `DELETED_AT` e, se for o caso, adicionar o Trait `SoftDeletes`.

- Hyperf\Database\Commands\Ast\ModelRewriteTimestampsVisitor

Esse `Visitor` pode determinar automaticamente, com base em `created_at` e `updated_at`, se deve habilitar o registro padrão de `horários de criação e modificação`.

- Hyperf\Database\Commands\Ast\ModelRewriteGetterSetterVisitor

Esse `Visitor` pode gerar `getters` e `setters` correspondentes com base em campos do banco.

## Sobrescrever Visitor

No framework Hyperf, ao usar `gen:model`, por padrão apenas `tinyint, smallint, mediumint, int, bigint` são declarados como tipo int, `bool, boolean` são declarados como tipo boolean, e outros tipos de dados são padronizados como `string`. Você pode sobrescrever esse comportamento.

Como a seguir:

```php
<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $count
 * @property string $float_num // decimal (ponto flutuante)
 * @property string $str
 * @property string $json
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserExt extends Model
{
    /**
     * A tabela associada ao model.
     */
    protected ?string $table = 'user_ext';

    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected array $fillable = ['id', 'count', 'float_num', 'str', 'json', 'created_at', 'updated_at'];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     */
    protected array $casts = ['id' => 'integer', 'count' => 'integer', 'float_num' => 'string', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

```

Nesse ponto, podemos modificar esse recurso sobrescrevendo `ModelUpdateVisitor`.

```php
<?php

declare(strict_types=1);
/**
 * Este arquivo faz parte do Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Kernel\Visitor;

use Hyperf\Database\Commands\Ast\ModelUpdateVisitor as Visitor;
use Hyperf\Stringable\Str;

class ModelUpdateVisitor extends Visitor
{
    protected function formatDatabaseType(string $type): ?string
    {
        switch ($type) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                return 'integer';
            case 'decimal':
                // Define como decimal e define a precisão correspondente
                return 'decimal:2';
            case 'float':
            case 'double':
            case 'real':
                return 'float';
            case 'bool':
            case 'boolean':
                return 'boolean';
            default:
                return null;
        }
    }

    protected function formatPropertyType(string $type, ?string $cast): ?string
    {
        if (! isset($cast)) {
            $cast = $this->formatDatabaseType($type) ?? 'string';
        }

        switch ($cast) {
            case 'integer':
                return 'int';
            case 'date':
            case 'datetime':
                return '\Carbon\Carbon';
            case 'json':
                return 'array';
        }

        if (Str::startsWith($cast, 'decimal')) {
            // Se o cast for decimal, o @property é alterado para string
            return 'string';
        }

        return $cast;
    }
}
```

Configure o relacionamento de mapeamento em `dependencies.php`

```php
<?php

return [
    Hyperf\Database\Commands\Ast\ModelUpdateVisitor::class => App\Kernel\Visitor\ModelUpdateVisitor::class,
];

```

Após reexecutar `gen:model`, o model correspondente ficará assim:

```php
<?php

declare (strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $count
 * @property string $float_num
 * @property string $str
 * @property string $json
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserExt extends Model
{
    /**
     * A tabela associada ao model.
     */
    protected ?string $table = 'user_ext';
    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected array $fillable = ['id', 'count', 'float_num', 'str', 'json', 'created_at', 'updated_at'];
    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     */
    protected array $casts = ['id' => 'integer', 'count' => 'integer', 'float_num' => 'decimal:2', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
```
