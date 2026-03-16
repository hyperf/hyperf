# 模型创建脚本

Hyperf 提供了创建模型的命令，您可以很方便的根据数据表创建对应模型。命令通过 `AST` 生成模型，所以当您增加了某些方法后，也可以使用脚本方便的重置模型。

```bash
php bin/hyperf.php gen:model table_name
```

## 创建模型

可选参数如下：

|        参数        |  类型  |              默认值               |                       备注                        |
| :----------------: | :----: | :-------------------------------: | :-----------------------------------------------: |
|       --pool       | string |             `default`             |       连接池，脚本会根据当前连接池配置创建        |
|       --path       | string |            `app/Model`            |                     模型路径                      |
|   --force-casts    |  bool  |              `false`              |             是否强制重置 `casts` 参数             |
|      --prefix      | string |             空字符串              |                      表前缀                       |
|   --inheritance    | string |              `Model`              |                       父类                        |
|       --uses       | string | `Hyperf\DbConnection\Model\Model` |              配合 `inheritance` 使用              |
| --refresh-fillable |  bool  |              `false`              |             是否刷新 `fillable` 参数              |
|  --table-mapping   | array  |               `[]`                | 为表名 -> 模型增加映射关系 比如 ['users:Account'] |
|  --ignore-tables   | array  |               `[]`                |        不需要生成模型的表名 比如 ['users']        |
|  --with-comments   |  bool  |              `false`              |                 是否增加字段注释                  |
|  --property-case   |  int   |                `0`                |              字段类型 0 蛇形 1 驼峰               |

当使用 `--property-case` 将字段类型转化为驼峰时，还需要手动在模型中加入 `Hyperf\Database\Model\Concerns\CamelCase`。

对应配置也可以配置到 `databases.{pool}.commands.gen:model` 中，如下

> 中划线都需要转化为下划线

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // 忽略其他配置
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

创建的模型如下

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

框架提供了几个 `Visitors`，方便用户对脚本能力进行扩展。使用方法很简单，只需要在 `visitors` 配置中，添加对应的 `Visitor` 即可。

```php
<?php

declare(strict_types=1);

return [
    'default' => [
        // 忽略其他配置
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

### 可选 Visitors

- Hyperf\Database\Commands\Ast\ModelRewriteKeyInfoVisitor

此 `Visitor` 可以根据数据库中主键，生成对应的 `$incrementing` `$primaryKey` 和 `$keyType`。

- Hyperf\Database\Commands\Ast\ModelRewriteSoftDeletesVisitor

此 `Visitor` 可以根据 `DELETED_AT` 常量判断该模型是否含有软删除字段，如果存在，则添加对应的 Trait `SoftDeletes`。

- Hyperf\Database\Commands\Ast\ModelRewriteTimestampsVisitor

此 `Visitor` 可以根据 `created_at` 和 `updated_at` 自动判断，是否启用默认记录 `创建和修改时间` 的功能。

- Hyperf\Database\Commands\Ast\ModelRewriteGetterSetterVisitor

此 `Visitor` 可以根据数据库字段生成对应的 `getter` 和 `setter`。

## 覆盖 Visitor

Hyperf 框架中，当使用 `gen:model` 时，默认只会将 `tinyint, smallint, mediumint, int, bigint` 声明为 `int` 类型，`bool, boolean` 声明为 `boolean` 类型，其他数据类型默认为 `string` ，可以通过重写覆盖调整。

如下：

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

这时候，我们就可以通过重写 `ModelUpdateVisitor`，修改这一特性。

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
    /**
     * Used by `casts` attribute.
     */
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
                // 设置为 decimal，并设置对应精度
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

    /**
     * Used by `@property` docs.
     */
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
            // 如果 cast 为 decimal，则 @property 改为 string
            return 'string';
        }

        return $cast;
    }
}
```

配置映射关系 `dependencies.php`

```php
<?php

return [
    Hyperf\Database\Commands\Ast\ModelUpdateVisitor::class => App\Kernel\Visitor\ModelUpdateVisitor::class,
];

```

重新执行 `gen:model` 后，对应模型如下：

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
