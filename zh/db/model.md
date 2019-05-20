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

|    参数    |  类型  |  默认值   |         备注         |
|:----------:|:------:|:---------:|:--------------------:|
| connection | string | 'default' |  数据库连接  |
|   table    | string |    无     |      数据表名称      |
| primaryKey | string |   'id'    |       模型主键       |
|  keyType   | string |   'int'   |       主键类型       |
|  fillable  | array  |    []     | 允许被批量复制的属性 |
|   casts    | string |    无     |    数据格式化配置    |
| timestamps |  bool  |   true    |  是否自动维护时间戳  |

### 数据表名称

如果我们没有指定模型对应的 table，它将使用类的复数形式「蛇形命名」来作为表名。因此，在这种情况下，Hyperf 将假设 User 模型存储的是 users 数据表中的数据。你可以通过在模型上定义 table 属性来指定自定义数据表：

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected $table = 'user';
}
```

### 主键

Hyperf 会假设每个数据表都有一个名为 id 的主键列。你可以定义一个受保护的 $primaryKey 属性来重写约定。

此外，Hyperf 假设主键是一个自增的整数值，这意味着默认情况下主键会自动转换为 int 类型。如果您希望使用非递增或非数字的主键则需要设置公共的 $incrementing 属性设置为 false。如果你的主键不是一个整数，你需要将模型上受保护的 $keyType 属性设置为 string。

### 时间戳

默认情况下，Hyperf 预期你的数据表中存在 created_at 和 updated_at 。如果你不想让 Hyperf 自动管理这两个列， 请将模型中的 $timestamps 属性设置为 false：

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public $timestamps = false;
}
```

如果需要自定义时间戳的格式，在你的模型中设置 $dateFormat 属性。这个属性决定日期属性在数据库的存储方式，以及模型序列化为数组或者 JSON 的格式：

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected $dateFormat = 'U';
}
```

如果你需要自定义存储时间戳的字段名，可以在模型中设置 CREATED_AT 和 UPDATED_AT 常量的值来实现：

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    const CREATED_AT = 'creation_date';

    const UPDATED_AT = 'last_update';
}
```

### 数据库连接

默认情况下，Hyperf 模型将使用你的应用程序配置的默认数据库连接 default。如果你想为模型指定一个不同的连接，设置 $connection 属性：当然，connection-name 作为 key，必须在 `databases.php` 配置文件中存在。

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected $connection = 'connection-name';
}
```

### 默认属性值

如果要为模型的某些属性定义默认值，可以在模型上定义 $attributes 属性：

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected $attributes = [
        'delayed' => false,
    ];
}
```

## 模型查询

```php
use App\Models\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();

```

### 重新加载模型

你可以使用 `fresh` 和 `refresh` 方法重新加载模型。 `fresh` 方法会重新从数据库中检索模型。现有的模型实例不受影响：

```php
use App\Models\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

`refresh` 方法使用数据库中的新数据重新赋值现有模型。此外，已经加载的关系会被重新加载：

```php
use App\Models\User;

/** @var User $user */
$user = User::query()->where('name','Hyperf')->first();

$user->name = 'Hyperf2';

$user->refresh();

echo $user->name; // Hyperf
```

### 集合

对于模型中的 `all` 和 `get` 方法可以查询多个结果，返回一个 `Hyperf\Database\Model\Collection` 实例。 Collection 类提供了 很多辅助函数 来处理查询结果：


