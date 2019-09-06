# Model

Model components are derived from [Eloquent ORM](https://laravel.com/docs/5.8/eloquent), and the relevant operations can refer to the documentation of Eloquent ORM.

## Create a model

Hyperf provides commands to create models, and you can easily create corresponding models based on data tables. The command generates the model via `AST`, so you can use the script to easily reset the model when you add some methods.

```
$ php bin/hyperf.php db:model table_name
```

The model created is as follows
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

## Model member variable

|  parameter   |  type  | default value |                     remark                      |
| :----------: | :----: | :-----------: | :---------------------------------------------: |
|  connection  | string |    default    |               Database connection               |
|    table     | string |      无       |                 Data table name                 |
|  primaryKey  | string |      id       |                Model primary key                |
|   keyType    | string |      int      |                Primary key type                 |
|   fillable   | array  |      []       |       Allowed bulk copying of attributes        |
|    casts     | string |      无       |          Data formatting configuration          |
|  timestamps  |  bool  |     true      | Whether to automatically maintain the timestamp |
| incrementing |  bool  |     true      |       Whether to increase the primary key       |

### Data table name

If we do not specify a table corresponding to the model, it will use the plural form of the class "snake name" as the table name. Therefore, in this case, Hyperf will assume that the User model stores the data in the users data table. You can specify a custom data table by defining the table property on the model:

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

Hyperf assumes that each data table has a primary key column called id. You can override the convention by defining a protected $primaryKey property.

In addition, Hyperf assumes that the primary key is an incremented integer value, which means that the primary key is automatically converted to the int type by default. If you want to use a non-incremental or non-numeric primary key, you need to set the public $incrementing property to false. If your primary key is not an integer, you need to set the protected $keyType property on the model to string.

### Timestamp

By default, Hyperf expects `created_at` and `updated_at` to exist in your data table. If you don't want Hyperf to automatically manage these two columns, set the `$timestamps` property in the model to `false`:

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

If you need a custom timestamp format, set the `$dateFormat` property in your model. This property determines how the date property is stored in the database, and the model is serialized into an array or JSON format:

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

If you don't want to keep the `datetime` format stored, or if you want to do further processing of the time, you can do so by overriding the `fromDateTime($value)` method inside the model.

If you need to customize the field name for storing timestamps, you can set the values of the `CREATED_AT` and `UPDATED_AT` constants in the model, one of which is `null`, which means you don't want the ORM to process the field:

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

### Database connection

By default, the Hyperf model will use the default database connection `default` configured by your application. If you want to specify a different connection for the model, set the `$connection` property: Of course, `connection-name` as `key` must exist in the `databases.php` configuration file.

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

### Default attribute value

If you want to define default values for certain properties of the model, you can define the `$attributes` attribute on the model:

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

### Reload the model

You can reload the model using the `fresh` and `refresh` methods. The `fresh` method will retrieve the model from the database again. Existing model instances are not affected:

```php
use App\Models\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

The `refresh` method reassigns an existing model with new data from the database. In addition, the already loaded relationship will be reloaded:

```php
use App\Models\User;

/** @var User $user */
$user = User::query()->where('name','Hyperf')->first();

$user->name = 'Hyperf2';

$user->refresh();

echo $user->name; // Hyperf
```

### Collection

For the `all` and `get` methods in the model, you can query multiple results and return a `Hyperf\Database\Model\Collection` instance. The `Collection` class provides a number of helper functions to handle the results of the query:

```php
$users = $users->reject(function ($user) {
    // Exclude all deleted users
    return $user->deleted;
});
```

### Retrieve a single model

In addition to retrieving all records from the specified data table, you can use the `find` or `first` methods to retrieve a single record. These methods return a single model instance instead of returning a collection of models:

```php
use App\Models\User;

$user = User::query()->where('id', 1)->first();

$user = User::query()->find(1);
```

### Retrieve multiple models

Of course the `find` method doesn't just support a single model.

```php
use App\Models\User;

$users = User::query()->find([1, 2, 3]);
```

### Aggregate function

You can also use the `count`, `sum`, `max`, and other aggregate functions provided by the Query Builder. These methods only return the appropriate scalar value instead of a model instance:

```php
use App\Models\User;

$count = User::query()->where('gender', 1)->count();
```

## Insert & update model

### Insert

To add a new record to the database, first create a new model instance, set the properties for the instance, and then call the `save` method:

```php
use App\Models\User;

/** @var User $user */
$user = new User();

$user->name = 'Hi Hyperf';

$user->save();
```

In this example, we assign the `name` attribute to the `App\Models\User` model instance. When the `save` method is called, a new record will be inserted. The `created_at` and `updated_at` timestamps are set automatically and do not require manual assignment.

### Update

The `save` method can also be used to update a model that already exists in the database. To update the model, you need to retrieve it first, set the properties to be updated, and then call the `save` method. Similarly, the `updated_at` timestamp is automatically updated, so there is no need to manually assign values:

```php
use App\Models\User;

/** @var User $user */
$user = User::query()->find(1);

$user->name = 'Hi Hyperf';

$user->save();
```

### Batch update

It is also possible to update multiple models that match the query criteria. In this example, for all users whose `gender` is 1, modify `gender_show` to be male:

```php
use App\Models\User;

User::query()->where('gender', 1)->update(['gender_show' => 'man']);
```

> The updated model does not trigger saved and updated events when batch updates. Because the model is never retrieved during batch updates.

### Batch assignment

You can also use the `create` method to save the new model, which returns a model instance. However, before using it, you need to specify the `fillable` or `guarded` attribute on the model, because all models are not available for batch assignment by default.

When the user passes an unexpected parameter via an HTTP request, and the parameter changes the fields in the database that you don't need to change. For example, a malicious user might pass the `is_admin` parameter via an HTTP request and pass it to the `create` method, which allows the user to upgrade themselves to an administrator.

So, before you start, you should define which properties on the model can be assigned in batches. You can do this with the `$fillable` attribute on the model. For example: let the `name` attribute of the `User` model be assigned in batches:

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

Once we've set up the properties that can be assigned in bulk, we can insert new data into the database via the `create` method. The `create` method will return the saved model instance:

```php
use App\Models\User;

$user = User::create(['name' => 'Hyperf']);
```

If you already have a model instance, you can pass an array to the fill method to assign a value:

```php
$user->fill(['name' => 'Hyperf']);
```

### Protection attribute

`$fillable` can be thought of as a "whitelist" of bulk assignments, which you can also implement using the `$guarded` attribute. The `$guarded` property contains an array that does not allow batch assignment. In other words, `$guarded` is functionally more like a "blacklist." Note: You can only use one of `$fillable` or `$guarded`, not both. In the following example, in addition to the `gender_show` attribute, other attributes can be assigned in batches:

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

The `delete` method can be called on the model instance to delete the instance:

```php
use App\Models\User;

$user = User::query()->find(1);

$user->delete();
```

### Delete the model by query

You can delete the model data by calling the `delete` method on the query. In this example, we will delete all users whose `gender` is `1`. As with batch updates, bulk delete does not launch any model events for the deleted model:

```php
use App\Models\User;

// Note that when using the delete method, you must establish some query conditions to safely delete data. There is no where condition, which will cause the entire data table to be deleted.
User::query()->where('gender', 1)->delete(); 
```

### Delete data directly by primary key

In the above example, you need to go to the database to find the corresponding model before calling `delete`. In fact, if you know the model's primary key, you can delete the model data directly through the `destroy` static method without first going to the database to find it. The `destroy` method accepts multiple primary keys in addition to accepting a single primary key, or uses arrays, collections to hold multiple primary keys:

```php
use App\Models\User;

User::destroy(1);

User::destroy([1,2,3]);
```

### Soft delete

In addition to actually deleting database records, `Hyperf` can also "soft delete" the model. The soft deleted model is not really removed from the database. In fact, the `deleted_at` attribute is set on the model and its value is written to the database. If the `deleted_at` value is not empty, it means the model has been soft deleted. If you want to enable model soft delete, you need to use `Hyperf\Database\Model\SoftDeletes` trait on the model.

> `SoftDeletes` trait will automatically convert `deleted_at` attribute to `DateTime / Carbon` instance

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
