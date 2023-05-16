# 修改器

> 本文档大量借鉴于 [LearnKu](https://learnku.com) 十分感谢 LearnKu 对 PHP 社区做出的贡献。

当你在模型实例中获取或设置某些属性值的时候，访问器和修改器允许你对模型属性值进行格式化。

## 访问器 & 修改器

### 定义一个访问器

若要定义一个访问器， 则需在模型上创建一个 `getFooAttribute` 方法，要访问的 `Foo` 字段需使用「驼峰式」命名。 在这个示例中，我们将为 `first_name` 属性定义一个访问器。当模型尝试获取 `first_name` 属性时，将自动调用此访问器：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 获取用户的姓名.
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

如你所见，字段的原始值被传递到访问器中，允许你对它进行处理并返回结果。如果想获取被修改后的值，你可以在模型实例上访问 `first_name` 属性:

```php
$user = App\User::find(1);

$firstName = $user->first_name;
```

当然，你也可以通过已有的属性值，使用访问器返回新的计算值:

```php
namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 获取用户的姓名.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

### 定义一个修改器

若要定义一个修改器，则需在模型上面定义 `setFooAttribute` 方法。要访问的 `Foo` 字段使用「驼峰式」命名。让我们再来定义一个 `first_name` 属性的修改器。当我们尝试在模式上在设置 `first_name` 属性值时，该修改器将被自动调用：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 设置用户的姓名.
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

修改器会获取属性已经被设置的值，允许你修改并且将其值设置到模型内部的 `$attributes` 属性上。举个例子，如果我们尝试将 `first_name` 属性的值设置为 `Sally`：

```php
$user = App\User::find(1);

$user->first_name = 'Sally';
```

在这个例子中，`setFirstNameAttribute` 方法在调用的时候接受 `Sally` 这个值作为参数。接着修改器会应用 `strtolower` 函数并将处理的结果设置到内部的 `$attributes` 数组。

## 日期转化器

默认情况下，模型会将 `created_at` 和 `updated_at` 字段转换为 `Carbon` 实例，它继承了 `PHP` 原生的 `DateTime` 类并提供了各种有用的方法。你可以通过设置模型的 `$dates` 属性来添加其他日期属性：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 应该转换为日期格式的属性.
     *
     * @var array
     */
    protected $dates = [
        'seen_at',
    ];
}

```

> Tip: 你可以通过将模型的公有属性 $timestamps 值设置为 false 来禁用默认的 created_at 和 updated_at 时间戳。

当某个字段是日期格式时，你可以将值设置为一个 `UNIX` 时间戳，日期时间 `(Y-m-d)` 字符串，或者 `DateTime` / `Carbon` 实例。日期值会被正确格式化并保存到你的数据库中：

就如上面所说，当获取到的属性包含在 `$dates` 属性中时，都会自动转换为 `Carbon` 实例，允许你在属性上使用任意的 `Carbon` 方法：

```php
$user = App\User::find(1);

return $user->deleted_at->getTimestamp();
```

### 时间格式

时间戳都将以 `Y-m-d H:i:s` 形式格式化。如果你需要自定义时间戳格式，可在模型中设置 `$dateFormat` 属性。这个属性决定了日期属性将以何种形式保存在数据库中，以及当模型序列化成数组或 `JSON` 时的格式：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class Flight extends Model
{
    /**
     * 这个属性应该被转化为原生类型.
     *
     * @var string
     */
    protected $dateFormat = 'U';
}
```

## 属性类型转换

模型中的 `$casts` 属性提供了一个便利的方法来将属性转换为常见的数据类型。`$casts` 属性应是一个数组，且数组的键是那些需要被转换的属性名称，值则是你希望转换的数据类型。
支持转换的数据类型有：`integer`, `real`, `float`, `double`, `decimal:<digits>`, `string`, `boolean`, `object`, `array`, `collection`, `date`, `datetime` 和 `timestamp`。 当需要转换为 `decimal` 类型时，你需要定义小数位的个数，如： `decimal:2`。

示例， 让我们把以整数（ `0` 或 `1` ）形式存储在数据库中的 `is_admin` 属性转成布尔值：

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

现在当你访问 `is_admin` 属性时，虽然保存在数据库里的值是一个整数类型，但是返回值总是会被转换成布尔值类型：

```php
$user = App\User::find(1);

if ($user->is_admin) {
    //
}
```

### 自定义类型转换

模型内置了多种常用的类型转换。但是，用户偶尔会需要将数据转换成自定义类型。现在，该需求可以通过定义一个实现 `CastsAttributes` 接口的类来完成

实现了该接口的类必须事先定义一个 `get` 和 `set` 方法。 `get` 方法负责将从数据库中获取的原始数据转换成对应的类型，而 `set` 方法则是将数据转换成对应的数据库类型以便存入数据库中。举个例子，下面我们将内置的 `json` 类型转换以自定义类型转换的形式重新实现一遍：

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * 将取出的数据进行转换
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * 转换成将要进行存储的值
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }
}
```

定义好自定义类型转换后，可以使用其类名称将其附加到模型属性：

```php
<?php

namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 应进行类型转换的属性
     *
     * @var array
     */
    protected $casts = [
        'options' => Json::class,
    ];
}
```

#### 值对象类型转换

你不仅可以将数据转换成原生的数据类型，还可以将数据转换成对象。两种自定义类型转换的定义方式非常类似。但是将数据转换成对象的自定义转换类中的 `set` 方法需要返回键值对数组，用于设置原始、可存储的值到对应的模型中。

举个例子，定义一个自定义类型转换类用于将多个模型属性值转换成单个 `Address` 值对象，假设 `Address` 对象有两个公有属性 `lineOne` 和 `lineTwo`：

```php
<?php

namespace App\Casts;

use App\Address;
use Hyperf\Contract\CastsAttributes;

class AddressCaster implements CastsAttributes
{
    /**
     * 将取出的数据进行转换
     */
    public function get($model, $key, $value, $attributes): Address
    {
        return new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * 转换成将要进行存储的值
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

进行值对象类型转换后，任何对值对象的数据变更将会自动在模型保存前同步回模型当中：

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

**这里的实现与 Laravel 不同，如果出现以下用法，请需要格外注意**

```php
$user = App\User::find(1);

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#20000';

// 直接修改 address 的字段后，是无法立马再 attributes 中生效的，但可以直接通过 $user->address 拿到修改后的数据。
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

// 当我们保存数据或者删除数据后，attributes 便会改成修改后的数据。
$user->save();
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#20000'
//];
```

如果修改 `address` 后，不想要保存，也不想通过 `address->lineOne` 获取 `address_line_one` 的数据，还可以使用以下 方法

```php
$user = App\User::find(1);
$user->address->lineOne = 'Updated Address Value';
$user->syncAttributes();
var_dump($user->getAttributes());
```

当然，如果您仍然需要修改对应的 `value` 后，同步修改 `attributes` 的功能，可以尝试使用以下方式。首先，我们实现一个 `UserInfo` 并继承 `CastsValue`。

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

然后实现对应的 `UserInfoCaster`

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

当我们再使用以下方式修改 UserInfo 时，便可以同步修改到 attributes 的数据。

```php
/** @var User $user */
$user = User::query()->find(100);
$user->userInfo->name = 'John1';
var_dump($user->getAttributes()); // ['name' => 'John1']
```

#### 入站类型转换

有时候，你可能只需要对写入模型的属性值进行类型转换而不需要对从模型中获取的属性值进行任何处理。一个典型入站类型转换的例子就是「hashing」。入站类型转换类需要实现 `CastsInboundAttributes` 接口，只需要实现 `set` 方法。

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
     * 创建一个新的类型转换类实例
     */
    public function __construct($algorithm = 'md5')
    {
        $this->algorithm = $algorithm;
    }

    /**
     * 转换成将要进行存储的值
     */
    public function set($model, $key, $value, $attributes)
    {
        return hash($this->algorithm, $value);
    }
}
```

#### 类型转换参数

当将自定义类型转换附加到模型时，可以指定传入的类型转换参数。传入类型转换参数需使用 `:` 将参数与类名分隔，多个参数之间使用逗号分隔。这些参数将会传递到类型转换类的构造函数中：

```php
<?php
namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 应进行类型转换的属性
     *
     * @var array
     */
    protected $casts = [
        'secret' => Hash::class.':sha256',
    ];
}
```

### 数组 & `JSON` 转换

当你在数据库存储序列化的 `JSON` 的数据时，`array` 类型的转换非常有用。比如：如果你的数据库具有被序列化为 `JSON` 的 `JSON` 或 `TEXT` 字段类型，并且在模型中加入了 `array` 类型转换，那么当你访问的时候就会自动被转换为 `PHP` 数组：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 应进行类型转换的属性
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
}
```

一旦定义了转换，你访问 `options` 属性时他会自动从 `JSON` 类型反序列化为 `PHP` 数组。当你设置了 `options` 属性的值时，给定的数组也会自动序列化为 `JSON` 类型存储：

```php
$user = App\User::find(1);

$options = $user->options;

$options['key'] = 'value';

$user->options = $options;

$user->save();
```

### Date 类型转换

当使用 `date` 或 `datetime` 属性时，可以指定日期的格式。 这种格式会被用在模型序列化为数组或者 `JSON`：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * 应进行类型转换的属性
     *
     * @var array
     */
    protected $casts = [
         'created_at' => 'datetime:Y-m-d',
    ];
}
```

### 查询时类型转换

有时候需要在查询执行过程中对特定属性进行类型转换，例如需要从数据库表中获取数据的时候。举个例子，请参考以下查询：

```php
use App\Post;
use App\User;

$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->get();
```

在该查询获取到的结果集中，`last_posted_at` 属性将会是一个字符串。假如我们在执行查询时进行 `date` 类型转换将更方便。你可以通过使用 `withCasts` 方法来完成上述操作：

```php
$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->withCasts([
    'last_posted_at' => 'date'
])->get();
```

