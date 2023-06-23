# 修改器

> 本文檔大量借鑑於 [LearnKu](https://learnku.com) 十分感謝 LearnKu 對 PHP 社區做出的貢獻。

當你在模型實例中獲取或設置某些屬性值的時候，訪問器和修改器允許你對模型屬性值進行格式化。

## 訪問器 & 修改器

### 定義一個訪問器

若要定義一個訪問器， 則需在模型上創建一個 `getFooAttribute` 方法，要訪問的 `Foo` 字段需使用「駝峯式」命名。 在這個示例中，我們將為 `first_name` 屬性定義一個訪問器。當模型嘗試獲取 `first_name` 屬性時，將自動調用此訪問器：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 獲取用户的姓名.
     *
     * @param  string  $value
     * @return string
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }
}
```

如你所見，字段的原始值被傳遞到訪問器中，允許你對它進行處理並返回結果。如果想獲取被修改後的值，你可以在模型實例上訪問 `first_name` 屬性:

```php
$user = App\User::find(1);

$firstName = $user->first_name;
```

當然，你也可以通過已有的屬性值，使用訪問器返回新的計算值:

```php
namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 獲取用户的姓名.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

### 定義一個修改器

若要定義一個修改器，則需在模型上面定義 `setFooAttribute` 方法。要訪問的 `Foo` 字段使用「駝峯式」命名。讓我們再來定義一個 `first_name` 屬性的修改器。當我們嘗試在模式上在設置 `first_name` 屬性值時，該修改器將被自動調用：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 設置用户的姓名.
     *
     * @param  string  $value
     * @return void
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strtolower($value);
    }
}
```

修改器會獲取屬性已經被設置的值，允許你修改並且將其值設置到模型內部的 `$attributes` 屬性上。舉個例子，如果我們嘗試將 `first_name` 屬性的值設置為 `Sally`：

```php
$user = App\User::find(1);

$user->first_name = 'Sally';
```

在這個例子中，`setFirstNameAttribute` 方法在調用的時候接受 `Sally` 這個值作為參數。接着修改器會應用 `strtolower` 函數並將處理的結果設置到內部的 `$attributes` 數組。

## 日期轉化器

默認情況下，模型會將 `created_at` 和 `updated_at` 字段轉換為 `Carbon` 實例，它繼承了 `PHP` 原生的 `DateTime` 類並提供了各種有用的方法。你可以通過設置模型的 `$dates` 屬性來添加其他日期屬性：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 應該轉換為日期格式的屬性.
     *
     * @var array
     */
    protected $dates = [
        'seen_at',
    ];
}

```

> Tip: 你可以通過將模型的公有屬性 $timestamps 值設置為 false 來禁用默認的 created_at 和 updated_at 時間戳。

當某個字段是日期格式時，你可以將值設置為一個 `UNIX` 時間戳，日期時間 `(Y-m-d)` 字符串，或者 `DateTime` / `Carbon` 實例。日期值會被正確格式化並保存到你的數據庫中：

就如上面所説，當獲取到的屬性包含在 `$dates` 屬性中時，都會自動轉換為 `Carbon` 實例，允許你在屬性上使用任意的 `Carbon` 方法：

```php
$user = App\User::find(1);

return $user->deleted_at->getTimestamp();
```

### 時間格式

時間戳都將以 `Y-m-d H:i:s` 形式格式化。如果你需要自定義時間戳格式，可在模型中設置 `$dateFormat` 屬性。這個屬性決定了日期屬性將以何種形式保存在數據庫中，以及當模型序列化成數組或 `JSON` 時的格式：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class Flight extends Model
{
    /**
     * 這個屬性應該被轉化為原生類型.
     *
     * @var string
     */
    protected $dateFormat = 'U';
}
```

## 屬性類型轉換

模型中的 `$casts` 屬性提供了一個便利的方法來將屬性轉換為常見的數據類型。`$casts` 屬性應是一個數組，且數組的鍵是那些需要被轉換的屬性名稱，值則是你希望轉換的數據類型。
支持轉換的數據類型有：`integer`, `real`, `float`, `double`, `decimal:<digits>`, `string`, `boolean`, `object`, `array`, `collection`, `date`, `datetime` 和 `timestamp`。 當需要轉換為 `decimal` 類型時，你需要定義小數位的個數，如： `decimal:2`。

示例， 讓我們把以整數（ `0` 或 `1` ）形式存儲在數據庫中的 `is_admin` 屬性轉成布爾值：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_admin' => 'boolean',
    ];
}
```

現在當你訪問 `is_admin` 屬性時，雖然保存在數據庫裏的值是一個整數類型，但是返回值總是會被轉換成布爾值類型：

```php
$user = App\User::find(1);

if ($user->is_admin) {
    //
}
```

### 自定義類型轉換

模型內置了多種常用的類型轉換。但是，用户偶爾會需要將數據轉換成自定義類型。現在，該需求可以通過定義一個實現 `CastsAttributes` 接口的類來完成

實現了該接口的類必須事先定義一個 `get` 和 `set` 方法。 `get` 方法負責將從數據庫中獲取的原始數據轉換成對應的類型，而 `set` 方法則是將數據轉換成對應的數據庫類型以便存入數據庫中。舉個例子，下面我們將內置的 `json` 類型轉換以自定義類型轉換的形式重新實現一遍：

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * 將取出的數據進行轉換
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * 轉換成將要進行存儲的值
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }
}
```

定義好自定義類型轉換後，可以使用其類名稱將其附加到模型屬性：

```php
<?php

namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 應進行類型轉換的屬性
     *
     * @var array
     */
    protected $casts = [
        'options' => Json::class,
    ];
}
```

#### 值對象類型轉換

你不僅可以將數據轉換成原生的數據類型，還可以將數據轉換成對象。兩種自定義類型轉換的定義方式非常類似。但是將數據轉換成對象的自定義轉換類中的 `set` 方法需要返回鍵值對數組，用於設置原始、可存儲的值到對應的模型中。

舉個例子，定義一個自定義類型轉換類用於將多個模型屬性值轉換成單個 `Address` 值對象，假設 `Address` 對象有兩個公有屬性 `lineOne` 和 `lineTwo`：

```php
<?php

namespace App\Casts;

use App\Address;
use Hyperf\Contract\CastsAttributes;

class AddressCaster implements CastsAttributes
{
    /**
     * 將取出的數據進行轉換
     */
    public function get($model, $key, $value, $attributes): Address
    {
        return new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * 轉換成將要進行存儲的值
     */
    public function set($model, $key, $value, $attributes)
    {
        return [
            'address_line_one' => $value->lineOne,
            'address_line_two' => $value->lineTwo,
        ];
    }
}
```

進行值對象類型轉換後，任何對值對象的數據變更將會自動在模型保存前同步回模型當中：

```php
<?php
$user = App\User::find(1);

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#10000';

$user->save();

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#10000'
//];
```

**這裏的實現與 Laravel 不同，如果出現以下用法，請需要格外注意**

```php
$user = App\User::find(1);

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#20000';

// 直接修改 address 的字段後，是無法立馬再 attributes 中生效的，但可以直接通過 $user->address 拿到修改後的數據。
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

// 當我們保存數據或者刪除數據後，attributes 便會改成修改後的數據。
$user->save();
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#20000'
//];
```

如果修改 `address` 後，不想要保存，也不想通過 `address->lineOne` 獲取 `address_line_one` 的數據，還可以使用以下 方法

```php
$user = App\User::find(1);
$user->address->lineOne = 'Updated Address Value';
$user->syncAttributes();
var_dump($user->getAttributes());
```

當然，如果您仍然需要修改對應的 `value` 後，同步修改 `attributes` 的功能，可以嘗試使用以下方式。首先，我們實現一個 `UserInfo` 並繼承 `CastsValue`。

```php
namespace App\Caster;

use Hyperf\Database\Model\CastsValue;

/**
 * @property string $name
 * @property int $gender
 */
class UserInfo extends CastsValue
{
}
```

然後實現對應的 `UserInfoCaster`

```php
<?php

declare(strict_types=1);

namespace App\Caster;

use Hyperf\Contract\CastsAttributes;
use Hyperf\Collection\Arr;

class UserInfoCaster implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): UserInfo
    {
        return new UserInfo($model, Arr::only($attributes, ['name', 'gender']));
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return [
            'name' => $value->name,
            'gender' => $value->gender,
        ];
    }
}

```

當我們再使用以下方式修改 UserInfo 時，便可以同步修改到 attributes 的數據。

```php
/** @var User $user */
$user = User::query()->find(100);
$user->userInfo->name = 'John1';
var_dump($user->getAttributes()); // ['name' => 'John1']
```

#### 入站類型轉換

有時候，你可能只需要對寫入模型的屬性值進行類型轉換而不需要對從模型中獲取的屬性值進行任何處理。一個典型入站類型轉換的例子就是「hashing」。入站類型轉換類需要實現 `CastsInboundAttributes` 接口，只需要實現 `set` 方法。

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsInboundAttributes;

class Hash implements CastsInboundAttributes
{
    /**
     * 哈希算法
     *
     * @var string
     */
    protected $algorithm;

    /**
     * 創建一個新的類型轉換類實例
     */
    public function __construct($algorithm = 'md5')
    {
        $this->algorithm = $algorithm;
    }

    /**
     * 轉換成將要進行存儲的值
     */
    public function set($model, $key, $value, $attributes)
    {
        return hash($this->algorithm, $value);
    }
}
```

#### 類型轉換參數

當將自定義類型轉換附加到模型時，可以指定傳入的類型轉換參數。傳入類型轉換參數需使用 `:` 將參數與類名分隔，多個參數之間使用逗號分隔。這些參數將會傳遞到類型轉換類的構造函數中：

```php
<?php
namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 應進行類型轉換的屬性
     *
     * @var array
     */
    protected $casts = [
        'secret' => Hash::class.':sha256',
    ];
}
```

### 數組 & `JSON` 轉換

當你在數據庫存儲序列化的 `JSON` 的數據時，`array` 類型的轉換非常有用。比如：如果你的數據庫具有被序列化為 `JSON` 的 `JSON` 或 `TEXT` 字段類型，並且在模型中加入了 `array` 類型轉換，那麼當你訪問的時候就會自動被轉換為 `PHP` 數組：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 應進行類型轉換的屬性
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
}
```

一旦定義了轉換，你訪問 `options` 屬性時他會自動從 `JSON` 類型反序列化為 `PHP` 數組。當你設置了 `options` 屬性的值時，給定的數組也會自動序列化為 `JSON` 類型存儲：

```php
$user = App\User::find(1);

$options = $user->options;

$options['key'] = 'value';

$user->options = $options;

$user->save();
```

### Date 類型轉換

當使用 `date` 或 `datetime` 屬性時，可以指定日期的格式。 這種格式會被用在模型序列化為數組或者 `JSON`：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 應進行類型轉換的屬性
     *
     * @var array
     */
    protected $casts = [
         'created_at' => 'datetime:Y-m-d',
    ];
}
```

### 查詢時類型轉換

有時候需要在查詢執行過程中對特定屬性進行類型轉換，例如需要從數據庫表中獲取數據的時候。舉個例子，請參考以下查詢：

```php
use App\Post;
use App\User;

$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->get();
```

在該查詢獲取到的結果集中，`last_posted_at` 屬性將會是一個字符串。假如我們在執行查詢時進行 `date` 類型轉換將更方便。你可以通過使用 `withCasts` 方法來完成上述操作：

```php
$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->withCasts([
    'last_posted_at' => 'date'
])->get();
```

