# Modifier

> This document borrows heavily from [LearnKu](https://learnku.com) Many thanks to LearnKu for contributing to the PHP community.

Accessors and modifiers allow you to format model property values when you get or set certain property values on a model instance.

## Accessors & Modifiers

### Define an accessor

To define an accessor, you need to create a `getFooAttribute` method on the model, and the `Foo` field to be accessed needs to be named in "camel case". In this example, we will define an accessor for the `first_name` property. This accessor is automatically called when the model tries to get the `first_name` property:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Get the user's name.
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

As you can see, the raw value of the field is passed into the accessor, allowing you to process it and return the result. To get the modified value, you can access the `first_name` property on the model instance:

```php
$user = App\User::find(1);

$firstName = $user->first_name;
```

Of course, you can also pass an existing property value and use an accessor to return a new computed value:

```php
namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Get the user's name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

### Define a modifier

To define a modifier, define the `setFooAttribute` method on the model. The `Foo` fields to be accessed are named using "camel case". Let's define a modifier for the `first_name` property again. This modifier will be called automatically when we try to set the value of the `first_name` property on the schema:

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

Modifiers take the value of an attribute that has already been set, allowing you to modify and set its value to the `$attributes` property inside the model. For example, if we try to set the value of the `first_name` property to `Sally`:

```php
$user = App\User::find(1);

$user->first_name = 'Sally';
```

In this example, the `setFirstNameAttribute` method is called with the value `Sally` as a parameter. The modifier then applies the `strtolower` function and sets the result of the processing to the internal `$attributes` array.

## date converter

By default, the model converts the `created_at` and `updated_at` fields to `Carbon` instances, which inherit the `PHP` native `DateTime` class and provide various useful methods. You can add other date properties by setting the model's `$dates` property:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be converted to date format.
     *
     * @var array
     */
    protected $dates = [
        'seen_at',
    ];
}

```

> Tip: You can disable the default created_at and updated_at timestamps by setting the model's public $timestamps value to false.

When a field is in date format, you can set the value to a `UNIX` timestamp, a datetime `(Y-m-d)` string, or a `DateTime` / `Carbon` instance. The date value will be properly formatted and saved to your database:

As mentioned above, when the fetched property is contained in the `$dates` property, it is automatically converted to a `Carbon` instance, allowing you to use any `Carbon` method on the property:

```php
$user = App\User::find(1);

return $user->deleted_at->getTimestamp();
```

### Time format

Timestamps will all be formatted as `Y-m-d H:i:s`. If you need a custom timestamp format, set the `$dateFormat` property in the model. This property determines how the date property will be stored in the database, and the format when the model is serialized into an array or `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class Flight extends Model
{
    /**
     * This property should be cast to the native type.
     *
     * @var string
     */
    protected $dateFormat = 'U';
}
```

## Attribute type conversion

The `$casts` property on the model provides a convenience method to cast properties to common data types. The `$casts` property should be an array whose keys are the names of the properties to be cast, and the values are the data types you wish to cast.
The supported data types are: `integer`, `real`, `float`, `double`, `decimal:<digits>`, `string`, `boolean`, `object`, `array`, `collection` , `date`, `datetime` and `timestamp`. When converting to `decimal` type, you need to define the number of decimal places, such as: `decimal:2`.

As an example, let's convert the `is_admin` property stored in the database as an integer ( `0` or `1` ) to a boolean value:

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

Now when you access the `is_admin` property, although the value stored in the database is an integer type, the return value is always converted to a boolean type:

```php
$user = App\User::find(1);

if ($user->is_admin) {
    //
}
```

### Custom type conversion

Models have several common type conversions built into them. However, users occasionally need to convert data into custom types. Now, this requirement can be accomplished by defining a class that implements the `CastsAttributes` interface

Classes that implement this interface must define a `get` and `set` method in advance. The `get` method is responsible for converting the raw data obtained from the database into the corresponding type, while the `set` method converts the data into the corresponding database type for storing in the database. For example, let's reimplement the built-in `json` type conversion as a custom type conversion:

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * Convert the extracted data
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * Convert to the value to be stored
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }
}
```

Once a custom type cast is defined, it can be attached to a model property using its class name:

```php
<?php

namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
        'options' => Json::class,
    ];
}
```

#### Value object type conversion

Not only can you convert data to native data types, but you can also convert data to objects. The two custom type conversions are defined in a very similar way. But the `set` method in the custom conversion class that converts the data to an object needs to return an array of key-value pairs, which are used to set the original, storable value into the corresponding model.

As an example, define a custom type conversion class to convert multiple model property values into a single `Address` value object, assuming that the `Address` object has two public properties `lineOne` and `lineTwo`:

```php
<?php

namespace App\Casts;

use App\Address;
use Hyperf\Contract\CastsAttributes;

class AddressCaster implements CastsAttributes
{
    /**
     * Convert the extracted data
     */
    public function get($model, $key, $value, $attributes): Address
    {
        return new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * Convert to the value to be stored
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

After the value object type conversion, any data changes to the value object will be automatically synced back to the model before the model is saved:

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

**The implementation here is different from Laravel, if the following usage occurs, please pay special attention**

```php
$user = App\User::find(1);

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#20000';

// After directly modifying the field of address, it cannot take effect in attributes immediately, but you can get the modified data directly through $user->address.
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

// When we save the data or delete the data, the attributes will be changed to the modified data.
$user->save();
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#20000'
//];
```

If after modifying `address`, you don't want to save it or get the data of `address_line_one` through `address->lineOne`, you can also use the following method

```php
$user = App\User::find(1);
$user->address->lineOne = 'Updated Address Value';
$user->syncAttributes();
var_dump($user->getAttributes());
```

Of course, if you still need to modify the function of `attributes` synchronously after modifying the corresponding `value`, you can try the following methods. First, we implement a `UserInfo` and inherit `CastsValue`.

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

Then implement the corresponding `UserInfoCaster`

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

When we modify UserInfo in the following way, we can synchronize the data modified to attributes.

```php
/** @var User $user */
$user = User::query()->find(100);
$user->userInfo->name = 'John1';
var_dump($user->getAttributes()); // ['name' => 'John1']
```

#### Inbound type conversion

Sometimes, you may only need to typecast property values written to the model without doing any processing on property values fetched from the model. A typical example of inbound type conversion is "hashing". Inbound type conversion classes need to implement the `CastsInboundAttributes` interface, and only need to implement the `set` method.

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsInboundAttributes;

class Hash implements CastsInboundAttributes
{
    /**
     * hash algorithm
     *
     * @var string
     */
    protected $algorithm;

    /**
     * Create a new instance of the typecast class
     */
    public function __construct($algorithm = 'md5')
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Convert to the value to be stored
     */
    public function set($model, $key, $value, $attributes)
    {
        return hash($this->algorithm, $value);
    }
}
```

#### Type conversion parameters

When attaching a custom cast to a model, you can specify the cast parameter passed in. To pass in type conversion parameters, use `:` to separate the parameters from the class name, and use commas to separate multiple parameters. These parameters will be passed to the constructor of the type conversion class:

```php
<?php
namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
        'secret' => Hash::class.':sha256',
    ];
}
```

### Array & `JSON` conversion

`array` type conversions are very useful when you store serialized `JSON` data in the database. For example: if your database has a `JSON` or `TEXT` field type that is serialized to `JSON`, and you add an `array` type conversion to the model, it will be automatically converted to ` when you access it PHP` array:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
}
```

一Once the conversion is defined, it will be automatically deserialized from the `JSON` type to a `PHP` array when you access the `options` property. When you set the value of the `options` property, the given array is also automatically serialized to `JSON` type storage:

```php
$user = App\User::find(1);

$options = $user->options;

$options['key'] = 'value';

$user->options = $options;

$user->save();
```

### Date type conversion

When using the `date` or `datetime` attributes, you can specify the format of the date. This format will be used when models are serialized as arrays or `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Properties that should be typecast
     *
     * @var array
     */
    protected $casts = [
         'created_at' => 'datetime:Y-m-d',
    ];
}
```

### Query-time type conversion

There are times when you need to typecast specific properties during query execution, such as when you need to fetch data from a database table. As an example, consider the following query:

```php
use App\Post;
use App\User;

$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->get();
```

In the result set obtained by this query, the `last_posted_at` attribute will be a string. It would be more convenient if we did a `date` type conversion when executing the query. You can do this by using the `withCasts` method:

```php
$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->withCasts([
    'last_posted_at' => 'date'
])->get();
```

