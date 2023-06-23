# 模型

模型組件衍生於 [Eloquent ORM](https://laravel.com/docs/5.8/eloquent)，相關操作均可參考 Eloquent ORM 的文檔。

## 創建模型

Hyperf 提供了創建模型的命令，您可以很方便的根據數據表創建對應模型。命令通過 `AST` 生成模型，所以當您增加了某些方法後，也可以使用腳本方便的重置模型。

```
php bin/hyperf.php gen:model table_name
```

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
     *
     * @var string
     */
    protected ?string $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'gender' => 'integer'];
}
```

## 模型成員變量

|     參數     |  類型  | 默認值  |         備註         |
| :----------: | :----: | :-----: | :------------------: |
|  connection  | string | default |      數據庫連接      |
|    table     | string |   無    |      數據表名稱      |
|  primaryKey  | string |   id    |       模型主鍵       |
|   keyType    | string |   int   |       主鍵類型       |
|   fillable   | array  |   []    | 允許被批量賦值的屬性 |
|    casts     | string |   無    |    數據格式化配置    |
|  timestamps  |  bool  |  true   |  是否自動維護時間戳  |
| incrementing |  bool  |  true   |     是否自增主鍵     |

### 數據表名稱

如果我們沒有指定模型對應的 table，它將使用類的複數形式「蛇形命名」來作為表名。因此，在這種情況下，Hyperf 將假設 User 模型存儲的是 users 數據表中的數據。你可以通過在模型上定義 table 屬性來指定自定義數據表：

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected ?string $table = 'user';
}
```

### 主鍵

Hyperf 會假設每個數據表都有一個名為 id 的主鍵列。你可以定義一個受保護的 $primaryKey 屬性來重寫約定。

此外，Hyperf 假設主鍵是一個自增的整數值，這意味着默認情況下主鍵會自動轉換為 int 類型。如果您希望使用非遞增或非數字的主鍵則需要設置公共的 $incrementing 屬性設置為 false。如果你的主鍵不是一個整數，你需要將模型上受保護的 $keyType 屬性設置為 string。

### 時間戳

默認情況下，Hyperf 預期你的數據表中存在 `created_at` 和 `updated_at` 。如果你不想讓 Hyperf 自動管理這兩個列， 請將模型中的 `$timestamps` 屬性設置為 `false`：

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public bool $timestamps = false;
}
```

如果需要自定義時間戳的格式，在你的模型中設置 `$dateFormat` 屬性。這個屬性決定日期屬性在數據庫的存儲方式，以及模型序列化為數組或者 JSON 的格式：

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected ?string $dateFormat = 'U';
}
```

如果您需要不希望保持 `datetime` 格式的儲存，或者希望對時間做進一步的處理，您可以通過在模型內重寫 `fromDateTime($value)` 方法實現。   

如果你需要自定義存儲時間戳的字段名，可以在模型中設置 `CREATED_AT` 和 `UPDATED_AT` 常量的值來實現，其中一個為 `null`，則表明不希望 ORM 處理該字段：

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    const CREATED_AT = 'creation_date';

    const UPDATED_AT = 'last_update';
}
```

### 數據庫連接

默認情況下，Hyperf 模型將使用你的應用程序配置的默認數據庫連接 `default`。如果你想為模型指定一個不同的連接，設置 `$connection` 屬性：當然，`connection-name` 作為 `key`，必須在 `databases.php` 配置文件中存在。

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected ?string $connection = 'connection-name';
}
```

### 默認屬性值

如果要為模型的某些屬性定義默認值，可以在模型上定義 `$attributes` 屬性：

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected array $attributes = [
        'delayed' => false,
    ];
}
```

## 模型查詢

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();

```

### 重新加載模型

你可以使用 `fresh` 和 `refresh` 方法重新加載模型。 `fresh` 方法會重新從數據庫中檢索模型。現有的模型實例不受影響：

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

`refresh` 方法使用數據庫中的新數據重新賦值現有模型。此外，已經加載的關係會被重新加載：

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('name','Hyperf')->first();

$user->name = 'Hyperf2';

$user->refresh();

echo $user->name; // Hyperf
```

### 集合

對於模型中的 `all` 和 `get` 方法可以查詢多個結果，返回一個 `Hyperf\Database\Model\Collection` 實例。 `Collection` 類提供了很多輔助函數來處理查詢結果：

```php
$users = $users->reject(function ($user) {
    // 排除所有已刪除的用户
    return $user->deleted;
});
```

### 檢索單個模型

除了從指定的數據表檢索所有記錄外，你可以使用 `find` 或 `first` 方法來檢索單條記錄。這些方法返回單個模型實例，而不是返回模型集合：

```php
<?php
use App\Model\User;

$user = User::query()->where('id', 1)->first();

$user = User::query()->find(1);
```

### 檢索多個模型

當然 `find` 的方法不止支持單個模型。

```php
<?php
use App\Model\User;

$users = User::query()->find([1, 2, 3]);
```

### 『未找到』異常

有時你希望在未找到模型時拋出異常，這在控制器和路由中非常有用。    
`findOrFail` 和 `firstOrFail` 方法會檢索查詢的第一個結果，如果未找到，將拋出 `Hyperf\Database\Model\ModelNotFoundException` 異常：

```php
<?php
use App\Model\User;

$model = User::findOrFail(1);
$model = User::where('age', '>', 18)->firstOrFail();
```

### 聚合函數

你還可以使用 查詢構造器 提供的 `count`，`sum`, `max`, 和其他的聚合函數。這些方法只會返回適當的標量值而不是一個模型實例：

```php
<?php
use App\Model\User;

$count = User::query()->where('gender', 1)->count();
```

## 插入 & 更新模型

### 插入

要往數據庫新增一條記錄，先創建新模型實例，給實例設置屬性，然後調用 `save` 方法：

```php
use App\Model\User;

/** @var User $user */
$user = new User();

$user->name = 'Hyperf';

$user->save();
```

在這個示例中，我們賦值給了 `App\Model\User` 模型實例的 `name` 屬性。當調用 `save` 方法時，將會插入一條新記錄。 `created_at` 和 `updated_at` 時間戳將會自動設置，不需要手動賦值。

### 更新

`save` 方法也可以用來更新數據庫已經存在的模型。更新模型，你需要先檢索出來，設置要更新的屬性，然後調用 `save` 方法。同樣， `updated_at` 時間戳會自動更新，所以也不需要手動賦值：

```php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$user->name = 'Hi Hyperf';

$user->save();
```

### 批量更新

也可以更新匹配查詢條件的多個模型。在這個示例中，所有的 `gender` 為 `1` 的用户，修改 `gender_show` 為 男性：

```php
use App\Model\User;

User::query()->where('gender', 1)->update(['gender_show' => '男性']);
```

> 批量更新時， 更新的模型不會觸發 `saved` 和 `updated` 事件。因為在批量更新時，並沒有實例化模型。同時，也不會執行相應的 `casts`，例如數據庫中 `json` 格式，在 Model 類中 `casts` 字段標記為 `array`，若是用批量更新，則插入時不會自動將 `array` 轉換為 `json` 字符串格式。

### 批量賦值

你也可以使用 `create` 方法來保存新模型，此方法會返回模型實例。不過，在使用之前，你需要在模型上指定 `fillable` 或 `guarded` 屬性，因為所有的模型都默認不可進行批量賦值。

當用户通過 HTTP 請求傳入一個意外的參數，並且該參數更改了數據庫中你不需要更改的字段時。比如：惡意用户可能會通過 HTTP 請求傳入 `is_admin` 參數，然後將其傳給 `create` 方法，此操作能讓用户將自己升級成管理員。

所以，在開始之前，你應該定義好模型上的哪些屬性是可以被批量賦值的。你可以通過模型上的 `$fillable` 屬性來實現。 例如：讓 `User` 模型的 `name` 屬性可以被批量賦值：

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected array $fillable = ['name'];
}
```

一旦我們設置好了可以批量賦值的屬性，就可以通過 `create` 方法插入新數據到數據庫中了。 `create` 方法將返回保存的模型實例：

```php
use App\Model\User;

$user = User::create(['name' => 'Hyperf']);
```

如果你已經有一個模型實例，你可以傳遞一個數組給 fill 方法來賦值：

```php
$user->fill(['name' => 'Hyperf']);
```

### 保護屬性

`$fillable` 可以看作批量賦值的「白名單」, 你也可以使用 `$guarded` 屬性來實現。 `$guarded` 屬性包含的是不允許批量賦值的數組。也就是説， `$guarded` 從功能上將更像是一個「黑名單」。注意：你只能使用 `$fillable` 或 `$guarded` 二者中的一個，不可同時使用。下面這個例子中，除了 `gender_show` 屬性，其他的屬性都可以批量賦值：

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected $guarded = ['gender_show'];
}
```

### 其他創建方法

`firstOrCreate` / `firstOrNew`

這裏有兩個你可能用來批量賦值的方法： `firstOrCreate` 和 `firstOrNew`。

`firstOrCreate` 方法會通過給定的 列 / 值 來匹配數據庫中的數據。如果在數據庫中找不到對應的模型， 則會從第一個參數的屬性乃至第二個參數的屬性中創建一條記錄插入到數據庫。

`firstOrNew` 方法像 `firstOrCreate` 方法一樣嘗試通過給定的屬性查找數據庫中的記錄。不同的是，如果 `firstOrNew` 方法找不到對應的模型，會返回一個新的模型實例。注意 `firstOrNew` 返回的模型實例尚未保存到數據庫中，你需要手動調用 `save` 方法來保存：

```php
<?php
use App\Model\User;

// 通過 name 來查找用户，不存在則創建...
$user = User::firstOrCreate(['name' => 'Hyperf']);

// 通過 name 查找用户，不存在則使用 name 和 gender, age 屬性創建...
$user = User::firstOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);

//  通過 name 查找用户，不存在則創建一個實例...
$user = User::firstOrNew(['name' => 'Hyperf']);

// 通過 name 查找用户，不存在則使用 name 和 gender, age 屬性創建一個實例...
$user = User::firstOrNew(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

### 刪除模型

可以在模型實例上調用 `delete` 方法來刪除實例：

```php
use App\Model\User;

$user = User::query()->find(1);

$user->delete();
```

### 通過查詢刪除模型

您可通過在查詢上調用 `delete` 方法來刪除模型數據，在這個例子中，我們將刪除所有 `gender` 為 `1` 的用户。與批量更新一樣，批量刪除不會為刪除的模型啓動任何模型事件：

```php
use App\Model\User;

// 注意使用 delete 方法時必須建立在某些查詢條件基礎之上才能安全刪除數據，不存在 where 條件，會導致刪除整個數據表
User::query()->where('gender', 1)->delete(); 
```

### 通過主鍵直接刪除數據

在上面的例子中，在調用 `delete` 之前需要先去數據庫中查找對應的模型。事實上，如果你知道了模型的主鍵，您可以直接通過 `destroy` 靜態方法來刪除模型數據，而不用先去數據庫中查找。 `destroy` 方法除了接受單個主鍵作為參數之外，還接受多個主鍵，或者使用數組，集合來保存多個主鍵：

```php
use App\Model\User;

User::destroy(1);

User::destroy([1,2,3]);
```

### 軟刪除

除了真實刪除數據庫記錄，`Hyperf` 也可以「軟刪除」模型。軟刪除的模型並不是真的從數據庫中刪除了。事實上，是在模型上設置了 `deleted_at` 屬性並將其值寫入數據庫。如果 `deleted_at` 值非空，代表這個模型已被軟刪除。如果要開啓模型軟刪除功能，你需要在模型上使用 `Hyperf\Database\Model\SoftDeletes` trait

> `SoftDeletes` trait 會自動將 `deleted_at` 屬性轉換成 `DateTime / Carbon` 實例

```php
<?php

namespace App\Model;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
}
```

`restoreOrCreate` 方法會通過給定的 列 / 值 來匹配數據庫中的數據。如果在數據庫中找到對應的模型，即執行 `restore` 方法恢復模型，否則會從第一個參數的屬性乃至第二個參數的屬性中創建一條記錄插入到數據庫。

```php
// 通過 name 查找用户，不存在則使用 name 和 gender, age 屬性創建...
$user = User::restoreOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

## Bit 類型

默認情況下，Hyperf 中的數據庫模型轉 SQL 過程中，會將參數值統一轉為 String 類型，以解決 int 在大數問題和使值類型更容易匹配索引，若想要使 `ORM` 支持 `bit` 類型，只需要增加以下事件監聽器代碼即可。

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Connection;
use Hyperf\Database\MySqlBitConnection;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

/**
 * @Listener()
 */
class SupportMySQLBitListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        Connection::resolverFor('mysql', static function ($connection, $database, $prefix, $config) {
            return new MySqlBitConnection($connection, $database, $prefix, $config);
        });
    }
}

```
