# 模型建立指令碼

Hyperf 提供了建立模型的命令，您可以很方便的根據資料表建立對應模型。命令通過 `AST` 生成模型，所以當您增加了某些方法後，也可以使用指令碼方便的重置模型。

```bash
php bin/hyperf.php gen:model table_name
```

## 建立模型

可選引數如下：

|        引數        |  型別  |              預設值               |                       備註                        |
| :----------------: | :----: | :-------------------------------: | :-----------------------------------------------: |
|       --pool       | string |             `default`             |       連線池，指令碼會根據當前連線池配置建立        |
|       --path       | string |            `app/Model`            |                     模型路徑                      |
|   --force-casts    |  bool  |              `false`              |             是否強制重置 `casts` 引數             |
|      --prefix      | string |             空字串              |                      表字首                       |
|   --inheritance    | string |              `Model`              |                       父類                        |
|       --uses       | string | `Hyperf\DbConnection\Model\Model` |              配合 `inheritance` 使用              |
| --refresh-fillable |  bool  |              `false`              |             是否重新整理 `fillable` 引數              |
|  --table-mapping   | array  |               `[]`                | 為表名 -> 模型增加對映關係 比如 ['users:Account'] |
|  --ignore-tables   | array  |               `[]`                |        不需要生成模型的表名 比如 ['users']        |
|  --with-comments   |  bool  |              `false`              |                 是否增加欄位註釋                  |
|  --property-case   |  int   |                `0`                |              欄位型別 0 蛇形 1 駝峰               |

當使用 `--property-case` 將欄位型別轉化為駝峰時，還需要手動在模型中加入 `Hyperf\Database\Model\Concerns\CamelCase`。

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

建立的模型如下

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
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'gender' => 'integer'];
}
```

## Visitors

框架提供了幾個 `Visitors`，方便使用者對指令碼能力進行擴充套件。使用方法很簡單，只需要在 `visitors` 配置中，新增對應的 `Visitor` 即可。

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

此 `Visitor` 可以根據資料庫中主鍵，生成對應的 `$incrementing` `$primaryKey` 和 `$keyType`。

- Hyperf\Database\Commands\Ast\ModelRewriteSoftDeletesVisitor

此 `Visitor` 可以根據 `DELETED_AT` 常量判斷該模型是否含有軟刪除欄位，如果存在，則新增對應的 Trait `SoftDeletes`。

- Hyperf\Database\Commands\Ast\ModelRewriteTimestampsVisitor

此 `Visitor` 可以根據 `created_at` 和 `updated_at` 自動判斷，是否啟用預設記錄 `建立和修改時間` 的功能。
