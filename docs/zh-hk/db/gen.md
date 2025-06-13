# 模型創建腳本

Hyperf 提供了創建模型的命令，您可以很方便的根據數據表創建對應模型。命令通過 `AST` 生成模型，所以當您增加了某些方法後，也可以使用腳本方便的重置模型。

```bash
php bin/hyperf.php gen:model table_name
```

## 創建模型

可選參數如下：

|        參數        |  類型  |              默認值               |                       備註                        |
| :----------------: | :----: | :-------------------------------: | :-----------------------------------------------: |
|       --pool       | string |             `default`             |       連接池，腳本會根據當前連接池配置創建        |
|       --path       | string |            `app/Model`            |                     模型路徑                      |
|   --force-casts    |  bool  |              `false`              |             是否強制重置 `casts` 參數             |
|      --prefix      | string |             空字符串              |                      表前綴                       |
|   --inheritance    | string |              `Model`              |                       父類                        |
|       --uses       | string | `Hyperf\DbConnection\Model\Model` |              配合 `inheritance` 使用              |
| --refresh-fillable |  bool  |              `false`              |             是否刷新 `fillable` 參數              |
|  --table-mapping   | array  |               `[]`                | 為表名 -> 模型增加映射關係 比如 ['users:Account'] |
|  --ignore-tables   | array  |               `[]`                |        不需要生成模型的表名 比如 ['users']        |
|  --with-comments   |  bool  |              `false`              |                 是否增加字段註釋                  |
|  --property-case   |  int   |                `0`                |              字段類型 0 蛇形 1 駝峯               |

當使用 `--property-case` 將字段類型轉化為駝峯時，還需要手動在模型中加入 `Hyperf\Database\Model\Concerns\CamelCase`。

對應配置也可以配置到 `databases.{pool}.commands.gen:model` 中，如下

> 中劃線都需要轉化為下劃線

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

創建的模型如下

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

框架提供了幾個 `Visitors`，方便用户對腳本能力進行擴展。使用方法很簡單，只需要在 `visitors` 配置中，添加對應的 `Visitor` 即可。

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

### 可選 Visitors

- Hyperf\Database\Commands\Ast\ModelRewriteKeyInfoVisitor

此 `Visitor` 可以根據數據庫中主鍵，生成對應的 `$incrementing` `$primaryKey` 和 `$keyType`。

- Hyperf\Database\Commands\Ast\ModelRewriteSoftDeletesVisitor

此 `Visitor` 可以根據 `DELETED_AT` 常量判斷該模型是否含有軟刪除字段，如果存在，則添加對應的 Trait `SoftDeletes`。

- Hyperf\Database\Commands\Ast\ModelRewriteTimestampsVisitor

此 `Visitor` 可以根據 `created_at` 和 `updated_at` 自動判斷，是否啓用默認記錄 `創建和修改時間` 的功能。

- Hyperf\Database\Commands\Ast\ModelRewriteGetterSetterVisitor

此 `Visitor` 可以根據數據庫字段生成對應的 `getter` 和 `setter`。

## 覆蓋 Visitor

Hyperf 框架中，當使用 `gen:model` 時，默認只會將 `tinyint, smallint, mediumint, int, bigint` 聲明為 `int` 類型，`bool, boolean` 聲明為 `boolean` 類型，其他數據類型默認為 `string` ，可以通過重寫覆蓋調整。

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

這時候，我們就可以通過重寫 `ModelUpdateVisitor`，修改這一特性。

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
                // 設置為 decimal，並設置對應精度
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
            // 如果 cast 為 decimal，則 @property 改為 string
            return 'string';
        }

        return $cast;
    }
}
```

配置映射關係 `dependencies.php`

```php
<?php

return [
    Hyperf\Database\Commands\Ast\ModelUpdateVisitor::class => App\Kernel\Visitor\ModelUpdateVisitor::class,
];

```

重新執行 `gen:model` 後，對應模型如下：

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
