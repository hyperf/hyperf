# 修改器

> 本文件大量借鑑於 [LearnKu](https://learnku.com) 十分感謝 LearnKu 對 PHP 社群做出的貢獻。

當你在模型例項中獲取或設定某些屬性值的時候，訪問器和修改器允許你對模型屬性值進行格式化。

## 訪問器 & 修改器

### 定義一個訪問器

若要定義一個訪問器， 則需在模型上建立一個 `getFooAttribute` 方法，要訪問的 `Foo` 欄位需使用「駝峰式」命名。 在這個示例中，我們將為 `first_name` 屬性定義一個訪問器。當模型嘗試獲取 `first_name` 屬性時，將自動呼叫此訪問器：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 獲取使用者的姓名.
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

如你所見，欄位的原始值被傳遞到訪問器中，允許你對它進行處理並返回結果。如果想獲取被修改後的值，你可以在模型例項上訪問 `first_name` 屬性:

```php
$user = App\User::find(1);

$firstName = $user->first_name;
```

當然，你也可以透過已有的屬性值，使用訪問器返回新的計算值:

```php
namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 獲取使用者的姓名.
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

若要定義一個修改器，則需在模型上面定義 `setFooAttribute` 方法。要訪問的 `Foo` 欄位使用「駝峰式」命名。讓我們再來定義一個 `first_name` 屬性的修改器。當我們嘗試在模式上在設定 `first_name` 屬性值時，該修改器將被自動呼叫：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 設定使用者的姓名.
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

修改器會獲取屬性已經被設定的值，允許你修改並且將其值設定到模型內部的 `$attributes` 屬性上。舉個例子，如果我們嘗試將 `first_name` 屬性的值設定為 `Sally`：

```php
$user = App\User::find(1);

$user->first_name = 'Sally';
```

在這個例子中，`setFirstNameAttribute` 方法在呼叫的時候接受 `Sally` 這個值作為引數。接著修改器會應用 `strtolower` 函式並將處理的結果設定到內部的 `$attributes` 陣列。

## 日期轉化器

預設情況下，模型會將 `created_at` 和 `updated_at` 欄位轉換為 `Carbon` 例項，它繼承了 `PHP` 原生的 `DateTime` 類並提供了各種有用的方法。你可以透過設定模型的 `$dates` 屬性來新增其他日期屬性：

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

> Tip: 你可以透過將模型的公有屬性 $timestamps 值設定為 false 來禁用預設的 created_at 和 updated_at 時間戳。

當某個欄位是日期格式時，你可以將值設定為一個 `UNIX` 時間戳，日期時間 `(Y-m-d)` 字串，或者 `DateTime` / `Carbon` 例項。日期值會被正確格式化並儲存到你的資料庫中：

就如上面所說，當獲取到的屬性包含在 `$dates` 屬性中時，都會自動轉換為 `Carbon` 例項，允許你在屬性上使用任意的 `Carbon` 方法：

```php
$user = App\User::find(1);

return $user->deleted_at->getTimestamp();
```

### 時間格式

時間戳都將以 `Y-m-d H:i:s` 形式格式化。如果你需要自定義時間戳格式，可在模型中設定 `$dateFormat` 屬性。這個屬性決定了日期屬性將以何種形式儲存在資料庫中，以及當模型序列化成陣列或 `JSON` 時的格式：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class Flight extends Model
{
    /**
     * 這個屬性應該被轉化為原生型別.
     *
     * @var string
     */
    protected $dateFormat = 'U';
}
```

## 屬性型別轉換

模型中的 `$casts` 屬性提供了一個便利的方法來將屬性轉換為常見的資料型別。`$casts` 屬性應是一個數組，且陣列的鍵是那些需要被轉換的屬性名稱，值則是你希望轉換的資料型別。
支援轉換的資料型別有：`integer`, `real`, `float`, `double`, `decimal:<digits>`, `string`, `boolean`, `object`, `array`, `collection`, `date`, `datetime` 和 `timestamp`。 當需要轉換為 `decimal` 型別時，你需要定義小數位的個數，如： `decimal:2`。

示例， 讓我們把以整數（ `0` 或 `1` ）形式儲存在資料庫中的 `is_admin` 屬性轉成布林值：

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

現在當你訪問 `is_admin` 屬性時，雖然儲存在資料庫裡的值是一個整數型別，但是返回值總是會被轉換成布林值型別：

```php
$user = App\User::find(1);

if ($user->is_admin) {
    //
}
```

### 自定義型別轉換

模型內建了多種常用的型別轉換。但是，使用者偶爾會需要將資料轉換成自定義型別。現在，該需求可以透過定義一個實現 `CastsAttributes` 介面的類來完成

實現了該介面的類必須事先定義一個 `get` 和 `set` 方法。 `get` 方法負責將從資料庫中獲取的原始資料轉換成對應的型別，而 `set` 方法則是將資料轉換成對應的資料庫型別以便存入資料庫中。舉個例子，下面我們將內建的 `json` 型別轉換以自定義型別轉換的形式重新實現一遍：

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * 將取出的資料進行轉換
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * 轉換成將要進行儲存的值
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }
}
```

定義好自定義型別轉換後，可以使用其類名稱將其附加到模型屬性：

```php
<?php

namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 應進行型別轉換的屬性
     *
     * @var array
     */
    protected $casts = [
        'options' => Json::class,
    ];
}
```

#### 值物件型別轉換

你不僅可以將資料轉換成原生的資料型別，還可以將資料轉換成物件。兩種自定義型別轉換的定義方式非常類似。但是將資料轉換成物件的自定義轉換類中的 `set` 方法需要返回鍵值對陣列，用於設定原始、可儲存的值到對應的模型中。

舉個例子，定義一個自定義型別轉換類用於將多個模型屬性值轉換成單個 `Address` 值物件，假設 `Address` 物件有兩個公有屬性 `lineOne` 和 `lineTwo`：

```php
<?php

namespace App\Casts;

use App\Address;
use Hyperf\Contract\CastsAttributes;

class AddressCaster implements CastsAttributes
{
    /**
     * 將取出的資料進行轉換
     */
    public function get($model, $key, $value, $attributes): Address
    {
        return new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * 轉換成將要進行儲存的值
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

進行值物件型別轉換後，任何對值物件的資料變更將會自動在模型儲存前同步回模型當中：

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

**這裡的實現與 Laravel 不同，如果出現以下用法，請需要格外注意**

```php
$user = App\User::find(1);

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#20000';

// 直接修改 address 的欄位後，是無法立馬再 attributes 中生效的，但可以直接透過 $user->address 拿到修改後的資料。
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

// 當我們儲存資料或者刪除資料後，attributes 便會改成修改後的資料。
$user->save();
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#20000'
//];
```

如果修改 `address` 後，不想要儲存，也不想透過 `address->lineOne` 獲取 `address_line_one` 的資料，還可以使用以下 方法

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

當我們再使用以下方式修改 UserInfo 時，便可以同步修改到 attributes 的資料。

```php
/** @var User $user */
$user = User::query()->find(100);
$user->userInfo->name = 'John1';
var_dump($user->getAttributes()); // ['name' => 'John1']
```

#### 入站型別轉換

有時候，你可能只需要對寫入模型的屬性值進行型別轉換而不需要對從模型中獲取的屬性值進行任何處理。一個典型入站型別轉換的例子就是「hashing」。入站型別轉換類需要實現 `CastsInboundAttributes` 介面，只需要實現 `set` 方法。

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsInboundAttributes;

class Hash implements CastsInboundAttributes
{
    /**
     * 雜湊演算法
     *
     * @var string
     */
    protected $algorithm;

    /**
     * 建立一個新的型別轉換類例項
     */
    public function __construct($algorithm = 'md5')
    {
        $this->algorithm = $algorithm;
    }

    /**
     * 轉換成將要進行儲存的值
     */
    public function set($model, $key, $value, $attributes)
    {
        return hash($this->algorithm, $value);
    }
}
```

#### 型別轉換引數

當將自定義型別轉換附加到模型時，可以指定傳入的型別轉換引數。傳入型別轉換引數需使用 `:` 將引數與類名分隔，多個引數之間使用逗號分隔。這些引數將會傳遞到型別轉換類的建構函式中：

```php
<?php
namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 應進行型別轉換的屬性
     *
     * @var array
     */
    protected $casts = [
        'secret' => Hash::class.':sha256',
    ];
}
```

### 陣列 & `JSON` 轉換

當你在資料庫儲存序列化的 `JSON` 的資料時，`array` 型別的轉換非常有用。比如：如果你的資料庫具有被序列化為 `JSON` 的 `JSON` 或 `TEXT` 欄位型別，並且在模型中加入了 `array` 型別轉換，那麼當你訪問的時候就會自動被轉換為 `PHP` 陣列：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 應進行型別轉換的屬性
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
}
```

一旦定義了轉換，你訪問 `options` 屬性時他會自動從 `JSON` 型別反序列化為 `PHP` 陣列。當你設定了 `options` 屬性的值時，給定的陣列也會自動序列化為 `JSON` 型別儲存：

```php
$user = App\User::find(1);

$options = $user->options;

$options['key'] = 'value';

$user->options = $options;

$user->save();
```

### Date 型別轉換

當使用 `date` 或 `datetime` 屬性時，可以指定日期的格式。 這種格式會被用在模型序列化為陣列或者 `JSON`：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 應進行型別轉換的屬性
     *
     * @var array
     */
    protected $casts = [
         'created_at' => 'datetime:Y-m-d',
    ];
}
```

### 查詢時型別轉換

有時候需要在查詢執行過程中對特定屬性進行型別轉換，例如需要從資料庫表中獲取資料的時候。舉個例子，請參考以下查詢：

```php
use App\Post;
use App\User;

$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->get();
```

在該查詢獲取到的結果集中，`last_posted_at` 屬性將會是一個字串。假如我們在執行查詢時進行 `date` 型別轉換將更方便。你可以透過使用 `withCasts` 方法來完成上述操作：

```php
$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->withCasts([
    'last_posted_at' => 'date'
])->get();
```

