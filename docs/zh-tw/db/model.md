# 模型

模型元件衍生於 [Eloquent ORM](https://laravel.com/docs/5.8/eloquent)，相關操作均可參考 Eloquent ORM 的文件。

## 建立模型

Hyperf 提供了建立模型的命令，您可以很方便的根據資料表建立對應模型。命令透過 `AST` 生成模型，所以當您增加了某些方法後，也可以使用指令碼方便的重置模型。

```
php bin/hyperf.php gen:model table_name
```

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

## 模型成員變數

|     引數     |  型別  | 預設值  |         備註         |
| :----------: | :----: | :-----: | :------------------: |
|  connection  | string | default |      資料庫連線      |
|    table     | string |   無    |      資料表名稱      |
|  primaryKey  | string |   id    |       模型主鍵       |
|   keyType    | string |   int   |       主鍵型別       |
|   fillable   | array  |   []    | 允許被批次賦值的屬性 |
|    casts     | string |   無    |    資料格式化配置    |
|  timestamps  |  bool  |  true   |  是否自動維護時間戳  |
| incrementing |  bool  |  true   |     是否自增主鍵     |

### 資料表名稱

如果我們沒有指定模型對應的 table，它將使用類的複數形式「蛇形命名」來作為表名。因此，在這種情況下，Hyperf 將假設 User 模型儲存的是 users 資料表中的資料。你可以透過在模型上定義 table 屬性來指定自定義資料表：

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

Hyperf 會假設每個資料表都有一個名為 id 的主鍵列。你可以定義一個受保護的 $primaryKey 屬性來重寫約定。

此外，Hyperf 假設主鍵是一個自增的整數值，這意味著預設情況下主鍵會自動轉換為 int 型別。如果您希望使用非遞增或非數字的主鍵則需要設定公共的 $incrementing 屬性設定為 false。如果你的主鍵不是一個整數，你需要將模型上受保護的 $keyType 屬性設定為 string。

### 時間戳

預設情況下，Hyperf 預期你的資料表中存在 `created_at` 和 `updated_at` 。如果你不想讓 Hyperf 自動管理這兩個列， 請將模型中的 `$timestamps` 屬性設定為 `false`：

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

如果需要自定義時間戳的格式，在你的模型中設定 `$dateFormat` 屬性。這個屬性決定日期屬性在資料庫的儲存方式，以及模型序列化為陣列或者 JSON 的格式：

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

如果您需要不希望保持 `datetime` 格式的儲存，或者希望對時間做進一步的處理，您可以透過在模型內重寫 `fromDateTime($value)` 方法實現。   

如果你需要自定義儲存時間戳的欄位名，可以在模型中設定 `CREATED_AT` 和 `UPDATED_AT` 常量的值來實現，其中一個為 `null`，則表明不希望 ORM 處理該欄位：

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

### 資料庫連線

預設情況下，Hyperf 模型將使用你的應用程式配置的預設資料庫連線 `default`。如果你想為模型指定一個不同的連線，設定 `$connection` 屬性：當然，`connection-name` 作為 `key`，必須在 `databases.php` 配置檔案中存在。

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

### 預設屬性值

如果要為模型的某些屬性定義預設值，可以在模型上定義 `$attributes` 屬性：

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

### 重新載入模型

你可以使用 `fresh` 和 `refresh` 方法重新載入模型。 `fresh` 方法會重新從資料庫中檢索模型。現有的模型例項不受影響：

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

`refresh` 方法使用資料庫中的新資料重新賦值現有模型。此外，已經載入的關係會被重新載入：

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

對於模型中的 `all` 和 `get` 方法可以查詢多個結果，返回一個 `Hyperf\Database\Model\Collection` 例項。 `Collection` 類提供了很多輔助函式來處理查詢結果：

```php
$users = $users->reject(function ($user) {
    // 排除所有已刪除的使用者
    return $user->deleted;
});
```

### 檢索單個模型

除了從指定的資料表檢索所有記錄外，你可以使用 `find` 或 `first` 方法來檢索單條記錄。這些方法返回單個模型例項，而不是返回模型集合：

```php
<?php
use App\Model\User;

$user = User::query()->where('id', 1)->first();

$user = User::query()->find(1);
```

### 檢索多個模型

當然 `find` 的方法不止支援單個模型。

```php
<?php
use App\Model\User;

$users = User::query()->find([1, 2, 3]);
```

### 『未找到』異常

有時你希望在未找到模型時丟擲異常，這在控制器和路由中非常有用。    
`findOrFail` 和 `firstOrFail` 方法會檢索查詢的第一個結果，如果未找到，將丟擲 `Hyperf\Database\Model\ModelNotFoundException` 異常：

```php
<?php
use App\Model\User;

$model = User::findOrFail(1);
$model = User::where('age', '>', 18)->firstOrFail();
```

### 聚合函式

你還可以使用 查詢構造器 提供的 `count`，`sum`, `max`, 和其他的聚合函式。這些方法只會返回適當的標量值而不是一個模型例項：

```php
<?php
use App\Model\User;

$count = User::query()->where('gender', 1)->count();
```

## 插入 & 更新模型

### 插入

要往資料庫新增一條記錄，先建立新模型例項，給例項設定屬性，然後呼叫 `save` 方法：

```php
use App\Model\User;

/** @var User $user */
$user = new User();

$user->name = 'Hyperf';

$user->save();
```

在這個示例中，我們賦值給了 `App\Model\User` 模型例項的 `name` 屬性。當呼叫 `save` 方法時，將會插入一條新記錄。 `created_at` 和 `updated_at` 時間戳將會自動設定，不需要手動賦值。

### 更新

`save` 方法也可以用來更新資料庫已經存在的模型。更新模型，你需要先檢索出來，設定要更新的屬性，然後呼叫 `save` 方法。同樣， `updated_at` 時間戳會自動更新，所以也不需要手動賦值：

```php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$user->name = 'Hi Hyperf';

$user->save();
```

### 批次更新

也可以更新匹配查詢條件的多個模型。在這個示例中，所有的 `gender` 為 `1` 的使用者，修改 `gender_show` 為 男性：

```php
use App\Model\User;

User::query()->where('gender', 1)->update(['gender_show' => '男性']);
```

> 批次更新時， 更新的模型不會觸發 `saved` 和 `updated` 事件。因為在批次更新時，並沒有例項化模型。同時，也不會執行相應的 `casts`，例如資料庫中 `json` 格式，在 Model 類中 `casts` 欄位標記為 `array`，若是用批次更新，則插入時不會自動將 `array` 轉換為 `json` 字串格式。

### 批次賦值

你也可以使用 `create` 方法來儲存新模型，此方法會返回模型例項。不過，在使用之前，你需要在模型上指定 `fillable` 或 `guarded` 屬性，因為所有的模型都預設不可進行批次賦值。

當用戶透過 HTTP 請求傳入一個意外的引數，並且該引數更改了資料庫中你不需要更改的欄位時。比如：惡意使用者可能會透過 HTTP 請求傳入 `is_admin` 引數，然後將其傳給 `create` 方法，此操作能讓使用者將自己升級成管理員。

所以，在開始之前，你應該定義好模型上的哪些屬性是可以被批次賦值的。你可以透過模型上的 `$fillable` 屬性來實現。 例如：讓 `User` 模型的 `name` 屬性可以被批次賦值：

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

一旦我們設定好了可以批次賦值的屬性，就可以透過 `create` 方法插入新資料到資料庫中了。 `create` 方法將返回儲存的模型例項：

```php
use App\Model\User;

$user = User::create(['name' => 'Hyperf']);
```

如果你已經有一個模型例項，你可以傳遞一個數組給 fill 方法來賦值：

```php
$user->fill(['name' => 'Hyperf']);
```

### 保護屬性

`$fillable` 可以看作批次賦值的「白名單」, 你也可以使用 `$guarded` 屬性來實現。 `$guarded` 屬性包含的是不允許批次賦值的陣列。也就是說， `$guarded` 從功能上將更像是一個「黑名單」。注意：你只能使用 `$fillable` 或 `$guarded` 二者中的一個，不可同時使用。下面這個例子中，除了 `gender_show` 屬性，其他的屬性都可以批次賦值：

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

### 其他建立方法

`firstOrCreate` / `firstOrNew`

這裡有兩個你可能用來批次賦值的方法： `firstOrCreate` 和 `firstOrNew`。

`firstOrCreate` 方法會透過給定的 列 / 值 來匹配資料庫中的資料。如果在資料庫中找不到對應的模型， 則會從第一個引數的屬性乃至第二個引數的屬性中建立一條記錄插入到資料庫。

`firstOrNew` 方法像 `firstOrCreate` 方法一樣嘗試透過給定的屬性查詢資料庫中的記錄。不同的是，如果 `firstOrNew` 方法找不到對應的模型，會返回一個新的模型例項。注意 `firstOrNew` 返回的模型例項尚未儲存到資料庫中，你需要手動呼叫 `save` 方法來儲存：

```php
<?php
use App\Model\User;

// 透過 name 來查詢使用者，不存在則建立...
$user = User::firstOrCreate(['name' => 'Hyperf']);

// 透過 name 查詢使用者，不存在則使用 name 和 gender, age 屬性建立...
$user = User::firstOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);

//  透過 name 查詢使用者，不存在則建立一個例項...
$user = User::firstOrNew(['name' => 'Hyperf']);

// 透過 name 查詢使用者，不存在則使用 name 和 gender, age 屬性建立一個例項...
$user = User::firstOrNew(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

### 刪除模型

可以在模型例項上呼叫 `delete` 方法來刪除例項：

```php
use App\Model\User;

$user = User::query()->find(1);

$user->delete();
```

### 透過查詢刪除模型

您可透過在查詢上呼叫 `delete` 方法來刪除模型資料，在這個例子中，我們將刪除所有 `gender` 為 `1` 的使用者。與批次更新一樣，批次刪除不會為刪除的模型啟動任何模型事件：

```php
use App\Model\User;

// 注意使用 delete 方法時必須建立在某些查詢條件基礎之上才能安全刪除資料，不存在 where 條件，會導致刪除整個資料表
User::query()->where('gender', 1)->delete(); 
```

### 透過主鍵直接刪除資料

在上面的例子中，在呼叫 `delete` 之前需要先去資料庫中查詢對應的模型。事實上，如果你知道了模型的主鍵，您可以直接透過 `destroy` 靜態方法來刪除模型資料，而不用先去資料庫中查詢。 `destroy` 方法除了接受單個主鍵作為引數之外，還接受多個主鍵，或者使用陣列，集合來儲存多個主鍵：

```php
use App\Model\User;

User::destroy(1);

User::destroy([1,2,3]);
```

### 軟刪除

除了真實刪除資料庫記錄，`Hyperf` 也可以「軟刪除」模型。軟刪除的模型並不是真的從資料庫中刪除了。事實上，是在模型上設定了 `deleted_at` 屬性並將其值寫入資料庫。如果 `deleted_at` 值非空，代表這個模型已被軟刪除。如果要開啟模型軟刪除功能，你需要在模型上使用 `Hyperf\Database\Model\SoftDeletes` trait

> `SoftDeletes` trait 會自動將 `deleted_at` 屬性轉換成 `DateTime / Carbon` 例項

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

`restoreOrCreate` 方法會透過給定的 列 / 值 來匹配資料庫中的資料。如果在資料庫中找到對應的模型，即執行 `restore` 方法恢復模型，否則會從第一個引數的屬性乃至第二個引數的屬性中建立一條記錄插入到資料庫。

```php
// 透過 name 查詢使用者，不存在則使用 name 和 gender, age 屬性建立...
$user = User::restoreOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

## Bit 型別

預設情況下，Hyperf 中的資料庫模型轉 SQL 過程中，會將引數值統一轉為 String 型別，以解決 int 在大數問題和使值型別更容易匹配索引，若想要使 `ORM` 支援 `bit` 型別，只需要增加以下事件監聽器程式碼即可。

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
