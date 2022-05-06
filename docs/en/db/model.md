# Model

Model components are derived from [Eloquent ORM](https://laravel.com/docs/5.8/eloquent), and related operations can refer to the Eloquent ORM documentation.

## Create a model

Hyperf provides commands to create models, and you can easily create corresponding models based on data tables. The command generates the model via `AST`, so when you add certain methods, you can also easily reset the model with a script.
```
$ php bin/hyperf.php db:model table_name
```

The created model is as follows
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

## Model parameters

| parameter  |  type  | defaults  |         Notes         |
|:----------:|:------:|:-------:|:--------------------:|
| connection | string | default |      Database Connectivity      |
|   table    | string |   null    |      data table name      |
| primaryKey | string |   id    |       model primary key      |
|  keyType   | string |   int   |       primary key type       |
|  fillable  | array  |   []    | properties that are allowed to be bulk copied |
|   casts    | string |   null    |    data formatting configuration    |
| timestamps |  bool  |  true   |  whether to automatically maintain timestamps  |

### data table name

If we don't specify the table corresponding to the model, it will use the plural form of the class "snake name" as the table name. So in this case Hyperf will assume that the User model stores data from the users data table. You can specify custom data tables by defining the table attribute on the model:
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

### Primary key

Hyperf will assume that each data table has a primary key column named id. You can define a protected $primaryKey property to override the convention.

Additionally, Hyperf assumes that the primary key is an auto-incrementing integer value, which means that the primary key is automatically converted to int by default. If you wish to use a non-incrementing or non-numeric primary key then you need to set the public $incrementing property to false. If your primary key is not an integer, you need to set the protected $keyType property on the model to string.

### Timestamp

By default, Hyperf expects `created_at` and `updated_at` to exist in your data table. If you don't want Hyperf to manage these two columns automatically, set the `$timestamps` property in the model to `false`:

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

If you need a custom timestamp format, set the `$dateFormat` property on your model. This property determines how the date property is stored in the database, and the format in which the model is serialized as an array or JSON:

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

If you need storage that you don't want to keep in `datetime` format, or want to do further processing of the time, you can do it by overriding the `fromDateTime($value)` method in the model.

If you need to customize the field name for storing timestamps, you can do so by setting the values of the `CREATED_AT` and `UPDATED_AT` constants in the model, one of which is `null`, indicating that you do not want the ORM to process this field:

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

### Database Connectivity

By default, the Hyperf model will use the default database connection `default` configured by your application. If you want to specify a different connection for the model, set the `$connection` property: of course, the `connection-name` as the `key` must exist in the `databases.php` configuration file.
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

### Default property value

If you want to define default values for some attributes of the model, you can define the `$attributes` attribute on the model:
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

## Model query

```php
use App\Models\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();

```

### Reload model

You can reload the model using the `fresh` and `refresh` methods. The `fresh` method will retrieve the model from the database again. Existing model instances are not affected:
```php
use App\Models\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

The `refresh` method reassigns an existing model with new data from the database. Additionally, already loaded relationships are reloaded:
```php
use App\Models\User;

/** @var User $user */
$user = User::query()->where('name','Hyperf')->first();

$user->name = 'Hyperf2';

$user->refresh();

echo $user->name; // Hyperf
```

### Gather

For the `all` and `get` methods on the model to query multiple results, a `Hyperf\Database\Model\Collection` instance is returned. The `Collection` class provides a number of helper functions to process query results:
```php
$users = $users->reject(function ($user) {
    // Exclude all deleted users
    return $user->deleted;
});
```

### Retrieve a single model

In addition to retrieving all records from a specified data table, you can use the `find` or `first` methods to retrieve a single record. Instead of returning a collection of models, these methods return a single model instance:
```php
use App\Models\User;

$user = User::query()->where('id', 1)->first();

$user = User::query()->find(1);
```

### Retrieve multiple models

Of course the `find` method supports more than just a single model.
```php
use App\Models\User;

$users = User::query()->find([1, 2, 3]);
```

### Aggregate function

You can also use the `count`, `sum`, `max`, and other aggregate functions provided by the query builder. These methods will just return the appropriate scalar value instead of a model instance:
```php
use App\Models\User;

$count = User::query()->where('gender', 1)->count();
```

## Insert & Update Model

### Insert

To add a new record to the database, first create a new model instance, set properties on the instance, and then call the `save` method:
```php
use App\Models\User;

/** @var User $user */
$user = new User();

$user->name = 'Hi Hyperf';

$user->save();
```

In this example, we assign to the `name` property of the `App\Models\User` model instance. When the `save` method is called, a new record will be inserted. `created_at` and `updated_at` timestamps will be set automatically, no manual assignment is required.
### Update

The `save` method can also be used to update models that already exist in the database. To update the model, you need to retrieve it, set the properties to update, and then call the `save` method. Also, the `updated_at` timestamp is updated automatically, so no manual assignment is required:
```php
use App\Models\User;

/** @var User $user */
$user = User::query()->find(1);

$user->name = 'Hi Hyperf';

$user->save();
```

### Bulk update

Multiple models matching the query criteria can also be updated. In this example, all users whose `gender` is 1, modify `gender_show` to be male:
```php
use App\Models\User;

User::query()->where('gender', 1)->update(['gender_show' => 'male']);
```

> When batch updating, the updated model will not trigger saved and updated events. Because during batch updates, the model is never retrieved.
### Mass assignment

You can also save a new model using the `create` method, which returns a model instance. However, you need to specify the `fillable` or `guarded` attribute on the model before using it, as all models are not mass-assignable by default.

When a user passes in an unexpected parameter through an HTTP request, and that parameter changes a field in the database that you don't need to change. For example, a malicious user might pass the `is_admin` parameter in an HTTP request and then pass it to the `create` method, which allows the user to escalate themselves to admin.

So, before you start, you should define which properties on the model can be mass-assigned. You can do this via the `$fillable` property on the model. For example, to make the `name` property of the `User` model mass assignable:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected $fillable = ['name'];
}
```

Once we have set the properties that can be mass-assigned, we can insert new data into the database via the `create` method. The `create` method will return the saved model instance:

```php
use App\Models\User;

$user = User::create(['name' => 'Hyperf']);
```

If you already have a model instance, you can pass an array to the fill method to assign:

```php
$user->fill(['name' => 'Hyperf']);
```

### Protected properties

`$fillable` can be seen as a "whitelist" for mass assignment, and you can also use the `$guarded` attribute to achieve this. The `$guarded` property contains arrays that do not allow mass assignment. That is, `$guarded` will function more like a "blacklist". Note: You can only use either `$fillable` or `$guarded`, not both. In the following example, except for the `gender_show` attribute, all other attributes can be mass-assigned:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    protected $guarded = ['gender_show'];
}
```

### Delete model

Instances can be deleted by calling the `delete` method on a model instance:

```php
use App\Models\User;

$user = User::query()->find(1);

$user->delete();
```

### Delete model by query

You can delete model data by calling the `delete` method on the query, in this example we will delete all users whose `gender` is `1`. Like bulk updates, bulk deletes do not fire any model events for the deleted model:

```php
use App\Models\User;

// Note that when using the delete method, certain query conditions must be used to safely delete data. There is no where condition, which will cause the entire data table to be deleted.
User::query()->where('gender', 1)->delete(); 
```

### Delete data directly by primary key

In the above example, before calling `delete`, you need to look up the corresponding model in the database. In fact, if you know the primary key of the model, you can delete the model data directly through the `destroy` static method without first looking in the database. In addition to accepting a single primary key as a parameter, the `destroy` method also accepts multiple primary keys, or uses arrays, collections to store multiple primary keys:
```php
use App\Models\User;

User::destroy(1);

User::destroy([1,2,3]);
```

### Soft delete

In addition to actually deleting database records, `Hyperf` can also "soft delete" the model. Soft deleted models are not really deleted from the database. In fact, the `deleted_at` attribute is set on the model and its value is written to the database. If the `deleted_at` value is non-null, it means the model has been soft deleted. To enable model soft deletes, you need to use the `Hyperf\Database\Model\SoftDeletes` trait on the model

> `SoftDeletes` trait will automatically convert `deleted_at` attributes to `DateTime / Carbon` instances

```php
<?php

namespace App;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\SoftDeletes;

class Flight extends Model
{
    use SoftDeletes;
}

```
