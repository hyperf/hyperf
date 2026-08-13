# Mutators

> This documentation draws heavily from [LearnKu](https://learnku.com). Many thanks to LearnKu for their contributions to the PHP community.

Accessors and mutators allow you to format model attribute values when you get or set attributes on model instances.

## Accessors & Mutators

### Defining an Accessor

To define an accessor, create a `getFooAttribute` method on your model, where `Foo` is the "camel-cased" name of the column you wish to access. In this example, we will define an accessor for the `first_name` attribute. When the model attempts to retrieve the `first_name` attribute, this accessor will be automatically called:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Get the user's first name.
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

As you can see, the original value of the column is passed to the accessor, allowing you to process it and return the result. To retrieve the modified value, you can access the `first_name` attribute on the model instance:

```php
$user = App\User::find(1);

$firstName = $user->first_name;
```

Of course, you can also use existing attribute values to return new calculated values via an accessor:

```php
namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

### Defining a Mutator

To define a mutator, define a `setFooAttribute` method on your model. The `Foo` field to be accessed should use "camel-cased" naming. Let's define a mutator for the `first_name` attribute. When we attempt to set the `first_name` attribute value on the model, this mutator will be automatically called:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Set the user's first name.
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

The mutator will receive the value being set on the attribute, allowing you to modify it and set the value on the model's internal `$attributes` attribute. For example, if we attempt to set the value of the `first_name` attribute to `Sally`:

```php
$user = App\User::find(1);

$user->first_name = 'Sally';
```

In this example, the `setFirstNameAttribute` method is called with `Sally` as the argument. The mutator then applies the `strtolower` function and sets the processed result into the internal `$attributes` array.

## Date Mutators

By default, the model will convert the `created_at` and `updated_at` columns into `Carbon` instances, which extend the native PHP `DateTime` class and provide various useful methods. You can add other date attributes by setting the `$dates` property of the model:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * The attributes that should be converted to date format.
     *
     * @var array
     */
    protected $dates = [
        'seen_at',
    ];
}
```

> Tip: You can disable the default `created_at` and `updated_at` timestamps by setting the model's public `$timestamps` property to `false`.

When a column is a date format, you can set the value to a `UNIX` timestamp, a date-time `(Y-m-d)` string, or a `DateTime` / `Carbon` instance. The date value will be correctly formatted and saved to your database.

As mentioned above, when retrieved attributes are included in the `$dates` property, they are automatically converted to `Carbon` instances, allowing you to use any `Carbon` methods on the attribute:

```php
$user = App\User::find(1);

return $user->deleted_at->getTimestamp();
```

### Date Format

Timestamps are formatted as `Y-m-d H:i:s`. If you need to customize the timestamp format, you can set the `$dateFormat` property in the model. This property determines how date attributes are stored in the database, as well as their format when the model is serialized to an array or `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class Flight extends Model
{
    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'U';
}
```

## Attribute Casting

The `$casts` property on the model provides a convenient method to cast attributes to common data types. The `$casts` property should be an array where the key is the name of the attribute to be cast, and the value is the data type you wish to cast to.
Supported data types for casting are: `integer`, `real`, `float`, `double`, `decimal:<digits>`, `string`, `boolean`, `object`, `array`, `collection`, `date`, `datetime`, and `timestamp`. When casting to the `decimal` type, you need to define the number of decimal places, e.g., `decimal:2`.

For example, let's cast the `is_admin` attribute, which is stored in the database as an integer (`0` or `1`), to a boolean:

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

Now, when you access the `is_admin` attribute, although the value stored in the database is an integer, the returned value will always be cast to a boolean type:

```php
$user = App\User::find(1);

if ($user->is_admin) {
    //
}
```

### Custom Casting

Models have many built-in common types of casting. However, users occasionally need to cast data to custom types. Now, this requirement can be met by defining a class that implements the `CastsAttributes` interface.

Classes that implement this interface must define a `get` and `set` method. The `get` method is responsible for converting the raw data fetched from the database into the corresponding type, while the `set` method converts the data into the corresponding database type to be stored. For example, let's re-implement the built-in `json` casting as a custom type casting:

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * Transform the data retrieved from the database.
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * Transform the value to be stored in the database.
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }
}
```

After defining the custom type casting, you can attach it to a model attribute using its class name:

```php
<?php

namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'options' => Json::class,
    ];
}
```

#### Value Object Casting

You can not only cast data to native data types but also cast data to objects. The definition method for both types of custom casting is very similar. However, the `set` method in the custom casting class that converts data to an object needs to return an array of key-value pairs, which is used to set the raw, storable values to the corresponding model.

For example, define a custom casting class to convert multiple model attributes into a single `Address` value object. Assume the `Address` object has two public properties, `lineOne` and `lineTwo`:

```php
<?php

namespace App\Casts;

use App\Address;
use Hyperf\Contract\CastsAttributes;

class AddressCaster implements CastsAttributes
{
    /**
     * Transform the data retrieved from the database.
     */
    public function get($model, $key, $value, $attributes): Address
    {
        return new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * Transform the value to be stored in the database.
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

After performing value object casting, any data changes to the value object will be automatically synchronized back to the model before it is saved:

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

**Implementation here differs from Laravel. If the following usage occurs, please pay extra attention:**

```php
$user = App\User::find(1);

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#20000';

// After directly modifying the fields of 'address', it cannot immediately take effect in 'attributes', but you can get the modified data directly through $user->address.
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Address Value',
//    'address_line_two' => '#10000'
//];

// When we save or delete data, 'attributes' will be changed to the modified data.
$user->save();
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#20000'
//];
```

If you modify `address` but do not want to save it, and do not want to fetch the data of `address_line_one` through `address->lineOne`, you can also use the following method:

```php
$user = App\User::find(1);
$user->address->lineOne = 'Updated Address Value';
$user->syncAttributes();
var_dump($user->getAttributes());
```

Of course, if you still need the functionality to synchronize modifications to `attributes` after modifying the corresponding `value`, you can try using the following method. First, we implement `UserInfo` and inherit from `CastsValue`.

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

Then implement the corresponding `UserInfoCaster`:

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

When we modify `UserInfo` in the following way, we can synchronize the data to `attributes`.

```php
/** @var User $user */
$user = User::query()->find(100);
$user->userInfo->name = 'John1';
var_dump($user->getAttributes()); // ['name' => 'John1']
```

#### Inbound Casting

Sometimes, you may only need to cast the value being written to the model without processing the value retrieved from the model. A typical example of inbound casting is "hashing". Inbound casting classes need to implement the `CastsInboundAttributes` interface; you only need to implement the `set` method.

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsInboundAttributes;

class Hash implements CastsInboundAttributes
{
    /**
     * Hashing algorithm.
     *
     * @var string
     */
    protected $algorithm;

    /**
     * Create a new casting class instance.
     */
    public function __construct($algorithm = 'md5')
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Transform the value to be stored in the database.
     */
    public function set($model, $key, $value, $attributes)
    {
        return hash($this->algorithm, $value);
    }
}
```

#### Casting Parameters

When attaching custom casting to a model, you can specify incoming casting parameters. To pass casting parameters, use `:` to separate the parameters from the class name, and use commas to separate multiple parameters. These parameters will be passed to the constructor of the casting class:

```php
<?php
namespace App;

use App\Casts\Hash;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'secret' => Hash::class.':sha256',
    ];
}
```

### Array & JSON Casting

The `array` cast type is very useful when you store serialized `JSON` data in your database. For example: if your database has a `JSON` or `TEXT` column type that is serialized as `JSON`, and you add the `array` cast type to your model, it will be automatically converted to a `PHP` array when you access it:

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
        'options' => 'array',
    ];
}
```

Once defined, when you access the `options` attribute, it will automatically be deserialized from `JSON` type to a `PHP` array. When you set the value of the `options` attribute, the given array will also be automatically serialized to `JSON` type for storage:

```php
$user = App\User::find(1);

$options = $user->options;

$options['key'] = 'value';

$user->options = $options;

$user->save();
```

### Date Casting

When using `date` or `datetime` attributes, you can specify the date format. This format is used when the model is serialized to an array or `JSON`:

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
         'created_at' => 'datetime:Y-m-d',
    ];
}
```

### Casting During Query

Sometimes you need to cast specific attributes during the query execution process, such as when you need to fetch data from the database table. For example, please refer to the following query:

```php
use App\Post;
use App\User;

$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->get();
```

In the result set obtained by this query, the `last_posted_at` attribute will be a string. It would be more convenient if we performed a `date` cast when executing the query. You can complete the above operation by using the `withCasts` method:

```php
$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->withCasts([
    'last_posted_at' => 'date'
])->get();
```
