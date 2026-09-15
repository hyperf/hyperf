# Script de criação de model

O Hyperf fornece comandos para criar models, e você pode facilmente criar os models correspondentes com base nas tabelas de dados. O comando gera o model via `AST`, então quando você adiciona certos métodos, você também pode facilmente resetar o model com o script.

```bash
php bin/hyperf.php gen:model table_name
```

## Criar um model

Os parâmetros opcionais são os seguintes:

|     Parâmetro      |  Tipo  |             Padrão               |                                             Observação                                             |
|:------------------:|:------:|:---------------------------------:|:----------------------------------------------------------------------------------------------:|
|       --pool       | string |             `default`             | Pool de conexões, o script será criado com base na configuração do pool de conexões atual |
|       --path       | string |            `app/Model`            |                                           caminho do model                                           |
|   --force-casts    |  bool  |              `false`              |                          Se deve forçar o reset do parâmetro `casts`                          |
|      --prefix      | string |           string vazia            |                                          prefixo da tabela                                          |
|   --inheritance    | string |              `Model`              |                                        A classe pai                                        |
|       --uses       | string | `Hyperf\DbConnection\Model\Model` |                                     Usar com `inheritance`                                     |
| --refresh-fillable |  bool  |              `false`              |                          Se deve atualizar o parâmetro `fillable`                           |
|  --table-mapping   | array  |               `[]`                |          Adiciona uma relação de mapeamento de nome de tabela -> model, por exemplo ['users:Account']          |
|  --ignore-tables   | array  |               `[]`                |            Não é necessário gerar o nome de tabela do model, por exemplo ['users']             |
|  --with-comments   |  bool  |              `false`              |                                 Se deve adicionar comentários dos campos                                  |
|  --property-case   |  int   |                `0`                |                              Tipo do campo: 0 Snakecase, 1 CamelCase                              |

Ao usar `--property-case` para converter o tipo do campo para camel case, você também precisa adicionar manualmente `Hyperf\Database\Model\Concerns\CamelCase` ao model.
A configuração correspondente também pode ser configurada em `databases.{pool}.commands.gen:model`, como segue

> Todos os itens tachados precisam ser convertidos em underscores

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // Ignore other configurations
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

O model criado é o seguinte

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
     * The table associated with the model.
     */
    protected ?string $table = 'user';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'gender' => 'integer'];
}
```

## Visitors

O framework fornece vários `Visitors` para os usuários estenderem as capacidades do script. O uso é muito simples, basta adicionar o `Visitor` correspondente na configuração `visitors`.

```php
<?php

declare(strict_types=1);

return [
    'default' => [
        // Ignore other configurations
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

Este `Visitor` pode gerar os correspondentes `$incrementing`, `$primaryKey` e `$keyType` de acordo com a chave primária no banco de dados.

- Hyperf\Database\Commands\Ast\ModelRewriteSoftDeletesVisitor

Este `Visitor` pode julgar se o model contém campos de soft delete de acordo com a constante `DELETED_AT`, e se sim, adiciona a Trait correspondente `SoftDeletes`.

- Hyperf\Database\Commands\Ast\ModelRewriteTimestampsVisitor

Este `Visitor` pode determinar automaticamente, com base em `created_at` e `updated_at`, se deve habilitar o registro padrão de `data de criação e modificação`.

- Hyperf\Database\Commands\Ast\ModelRewriteGetterSetterVisitor

Este `Visitor` pode gerar os `getters` e `setters` correspondentes com base nos campos do banco de dados.

## Sobrescrevendo o Visitor

No framework Hyperf, quando `gen:model` é usado. Por padrão, apenas `tinyint, smallint, mediumint, int, bigint` é declarado como tipo int, `bool, boolean` é declarado como tipo boolean, e outros tipos de dados são definidos por padrão como `string`. Você pode sobrescrever esses ajustes.

Como segue:

```php
<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $count
 * @property string $float_num // decimal
 * @property string $str
 * @property string $json
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserExt extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user_ext';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'count', 'float_num', 'str', 'json', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'count' => 'integer', 'float_num' => 'string', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

```

Neste ponto, podemos modificar essa funcionalidade sobrescrevendo `ModelUpdateVisitor`.

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
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
                // Set to decimal, and set the corresponding precision
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
            // If cast is decimal, @property is changed to string
            return 'string';
        }

        return $cast;
    }
}
```

Configure a relação de mapeamento `dependencies.php`

```php
<?php

return [
    Hyperf\Database\Commands\Ast\ModelUpdateVisitor::class => App\Kernel\Visitor\ModelUpdateVisitor::class,
];

```

Após executar novamente `gen:model`, o model correspondente é o seguinte:

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
     * The table associated with the model.
     */
    protected ?string $table = 'user_ext';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'count', 'float_num', 'str', 'json', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'count' => 'integer', 'float_num' => 'decimal:2', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
```
