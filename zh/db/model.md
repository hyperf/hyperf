# 模型

模型组件衍生于 [Eloquent ORM](https://laravel.com/docs/5.8/eloquent)，相关操作均可参考 Eloquent ORM 的文档。

## 创建模型

Hyperf 提供了创建模型的命令，您可以很方便的根据数据表创建对应模型。命令通过 `AST` 生成模型，所以当您增加了某些方法后，也可以使用脚本方便的重置模型。

```
$ php bin/hyperf.php db:model table_name
```

创建的模型如下
```php
<?php

declare(strict_types=1);

namespace App\Models;

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

## 模型参数

|    参数    |  类型   |  默认值 |         备注        |
|:----------:|:------:|:------:|:------------------:|
|   table    | string |   无   |  模型对应的table名   |
| primaryKey | string |  'id'  |       模型主键      |
|  fillable  | array  |   []   | 允许被批量复制的属性  |
|   casts    | string |   无   |    数据格式化配置    |
| timestamps |  bool  |  true  |  是否自动维护时间戳   |

## 模型查询

```php
use App\Models\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();

```
