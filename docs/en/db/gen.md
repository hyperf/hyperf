# Model creation script

Hyperf provides commands to create models, and you can easily create corresponding models based on data tables. The command generates the model via `AST`, so when you add certain methods, you can also easily reset the model with the script.

```bash
php bin/hyperf.php gen:model table_name
```

## Create a model

The optional parameters are as follows:

|     Parameter      |  Type  |             Defaults              |                                             Remark                                             |
|:------------------:|:------:|:---------------------------------:|:----------------------------------------------------------------------------------------------:|
|       --pool       | string |             `default`             | Connection pool, the script will be created based on the current connection pool configuration |
|       --path       | string |            `app/Model`            |                                           model path                                           |
|   --force-casts    |  bool  |              `false`              |                          Whether to force reset the `casts` parameter                          |
|      --prefix      | string |           empty string            |                                          table prefix                                          |
|   --inheritance    | string |              `Model`              |                                        The parent class                                        |
|       --uses       | string | `Hyperf\DbConnection\Model\Model` |                                     Use with `inheritance`                                     |
| --refresh-fillable |  bool  |              `false`              |                          Whether to refresh the `fillable` parameter                           |
|  --table-mapping   | array  |               `[]`                |          Add a mapping relationship for table name -> model such as ['users:Account']          |
|  --ignore-tables   | array  |               `[]`                |            There is no need to generate the table name of the model e.g. ['users']             |
|  --with-comments   |  bool  |              `false`              |                                 Whether to add field comments                                  |
|  --property-case   |  int   |                `0`                |                              Field Type: 0 Snakecase, 1 CamelCase                              |

When using `--property-case` to convert the field type to camel case, you also need to manually add `Hyperf\Database\Model\Concerns\CamelCase` to the model.
The corresponding configuration can also be configured in `databases.{pool}.commands.gen:model`, as follows

> All struck-through need to be converted to underscores

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

The created model is as follows

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

The framework provides several `Visitors` for users to extend the scripting capabilities. The usage is very simple, just add the corresponding `Visitor` in the `visitors` configuration.

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

### Optional Visitors

- Hyperf\Database\Commands\Ast\ModelRewriteKeyInfoVisitor

This `Visitor` can generate the corresponding `$incrementing` `$primaryKey` and `$keyType` according to the primary key in the database.

- Hyperf\Database\Commands\Ast\ModelRewriteSoftDeletesVisitor

This `Visitor` can judge whether the model contains soft delete fields according to the `DELETED_AT` constant, and if so, add the corresponding Trait `SoftDeletes`.

- Hyperf\Database\Commands\Ast\ModelRewriteTimestampsVisitor

This `Visitor` can automatically determine, based on `created_at` and `updated_at`, whether to enable the default recording of `created and modified times`.

- Hyperf\Database\Commands\Ast\ModelRewriteGetterSetterVisitor

This `Visitor` can generate corresponding `getters` and `setters` based on database fields.

## Override Visitor

In the Hyperf framework, when `gen:model` is used. By default, only `tinyint, smallint, mediumint, int, bigint` is declared as type int, `bool, boolean` is declared as type boolean, and other data types are defaulted to `string`. You can override adjustments. 

as follows:

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

At this point, we can modify this feature by overriding `ModelUpdateVisitor`.

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

Configure the mapping relationship `dependencies.php`

```php
<?php

return [
    Hyperf\Database\Commands\Ast\ModelUpdateVisitor::class => App\Kernel\Visitor\ModelUpdateVisitor::class,
];

```

After re-executing `gen:model`, the corresponding model is as follows:

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
