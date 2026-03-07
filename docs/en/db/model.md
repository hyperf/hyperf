# Model

The model component is derived from [Eloquent ORM](https://laravel.com/docs/5.8/eloquent),and all related operations can refer to the Eloquent ORM documentation.

## Creating a Model

Hyperf provides a command to create models, allowing you to conveniently create corresponding models based on your database tables. The command generates models using `AST`, which means you can easily reset the model with a script even after adding certain methods.

```
php bin/hyperf.php gen:model table_name
```

Optional parameters are as follows:

|        Parameter   |  Type  |              Default Value        |                       Note                        |
| :----------------: | :----: | :-------------------------------: | :-----------------------------------------------: |
|       --pool       | string |             `default`             |       Connection pool, the script will create based on the current pool configuration        |
|       --path       | string |            `app/Model`            |                     Model path                     |
|   --force-casts    |  bool  |              `false`              |             Whether to forcibly reset the `casts` attribute             |
|      --prefix      | string |             ''              |                      Table prefix                       |
|   --inheritance    | string |              `Model`              |                       Parent class                        |
|       --uses       | string | `Hyperf\DbConnection\Model\Model` |              Used in conjunction with `inheritance`              |
| --refresh-fillable |  bool  |              `false`              |             Whether to refresh the `fillable` attribute              |
|  --table-mapping   | array  |               `[]`                | Mapping of table name to model, e.g., ['users:Account'] |
|  --ignore-tables   | array  |               `[]`                |        Tables to ignore for model generation, e.g., ['users']        |
|  --with-comments   |  bool  |              `false`              |                 Whether to add field comments                 |
|  --property-case   |  int   |                `0`                |             Field type 0 snake 1 hump     |


When using the `--property-case` option to convert field names to camelCase, it is also necessary to manually include the `Hyperf\Database\Model\Concerns\CamelCase` trait in your model.

The corresponding configuration can also be set in `databases.{pool}.commands.gen:model` as follows:

> All dashes need to be converted into underscores

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // Ignore other configurations.
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

The created model is as follows:

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

## Model member variables

| Parameters | Type | Default value | Remarks |
| :----------: | :----: | :-----: | :------------------: |
| connection | string | default | database connection |
| table | string | None | Data table name |
| primaryKey | string | id | model primary key |
| keyType | string | int | primary key type |
| fillable | array | [] | Properties that allow batch assignment |
| casts | string | None | Data formatting configuration |
| timestamps | bool | true | Whether to automatically maintain timestamps |
| incrementing | bool | true | Whether to auto-increment the primary key |

### Table Names

If we do not specify the table corresponding to the model, it will use the plural form of the class name in 'snake case' as the table name. Therefore, in this case, Hyperf will assume that the User model stores data in the 'users' table. You can specify a custom table by defining the table property on the model:

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

### Primary Key

Hyperf will assume that every data table has a primary key column named id. You can define a protected $primaryKey property to override the convention.

Additionally, Hyperf assumes that the primary key is an auto-incrementing integer value, which means that the primary key is automatically converted to an int type by default. If you wish to use a non-increasing or non-numeric primary key you need to set the public $incrementing property to false. If your primary key is not an integer, you need to set the protected $keyType property on the model to string.


### Timestamps

By default, Hyperf expects your table to have `created_at` and `updated_at` columns. If you do not want Hyperf to automatically manage these two columns, set the `$timestamps` property in your model to `false`:

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

If you need to customize the timestamp format, set the `$dateFormat` property in your model. This property determines how the date attribute is stored in the database, and the model is serialized into an array or JSON format:

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

If you need storage that does not want to keep the `datetime` format, or want to do further processing on the time, you can do this by overriding the `fromDateTime($value)` method in the model.

If you need to customize the field name for storing timestamps, you can set the values ​​of the `CREATED_AT` and `UPDATED_AT` constants in the model. If one of them is `null`, it indicates that you do not want the ORM to process the field:

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

### Database Connectivity

By default, Hyperf models will use the default database connection `default` configured by your application. If you want to specify a different connection for the model, set the `$connection` property: Of course, the `connection-name` as the `key` must exist in the `databases.php` configuration file.

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

### Default attribute value

If you want to define default values ​​for some attributes of the model, you can define the `$attributes` attribute on the model:

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

## Model query

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();

```

### Reload model

You can reload the model using the `fresh` and `refresh` methods. The `fresh` method will retrieve the model from the database again. Existing model instances are not affected:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

The `refresh` method revalues ​​an existing model with new data from the database. Additionally, already loaded relationships will be reloaded:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('name','Hyperf')->first();

$user->name = 'Hyperf2';

$user->refresh();

echo $user->name; // Hyperf
```

### Collection

For the `all` and `get` methods in the model, you can query multiple results and return a `Hyperf\Database\Model\Collection` instance. The `Collection` class provides many helper functions to process query results:

```php
$users = $users->reject(function ($user) {
    // Exclude all deleted users
    return $user->deleted;
});
```

### Retrieve a single model

In addition to retrieving all records from a specified data table, you can use the `find` or `first` methods to retrieve a single record. These methods return a single model instance rather than a collection of models:

```php
<?php
use App\Model\User;

$user = User::query()->where('id', 1)->first();

$user = User::query()->find(1);
```

### Retrieve multiple models

Of course the `find` method supports more than just a single model.

```php
<?php
use App\Model\User;

$users = User::query()->find([1, 2, 3]);
```

### "Not found" exception

Sometimes you want to throw an exception when a model is not found, this is very useful in controllers and routes.
The `findOrFail` and `firstOrFail` methods will retrieve the first result of the query, if not found, a `Hyperf\Database\Model\ModelNotFoundException` exception will be thrown:

```php
<?php
use App\Model\User;

$model = User::findOrFail(1);
$model = User::where('age', '>', 18)->firstOrFail();
```

### Aggregation function

You can also use the `count`, `sum`, `max`, and other aggregate functions provided by the query builder. These methods will simply return the appropriate scalar value rather than a model instance:

```php
<?php
use App\Model\User;

$count = User::query()->where('gender', 1)->count();
```

## Insert & update model

### Insert

To add a new record to the database, first create a new model instance, set properties for the instance, and then call the `save` method:

```php
use App\Model\User;

/** @var User $user */
$user = new User();

$user->name = 'Hyperf';

$user->save();
```

In this example, we assign a value to the `name` property of the `App\Model\User` model instance. When the `save` method is called, a new record will be inserted. The `created_at` and `updated_at` timestamps will be set automatically and no manual assignment is required.

### renew

The `save` method can also be used to update an existing model in the database. To update the model, you need to retrieve it first, set the properties to be updated, and then call the `save` method. Likewise, the `updated_at` timestamp is updated automatically, so there is no need to manually assign it:

```php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$user->name = 'Hi Hyperf';

$user->save();
```

### Batch update

You can also update multiple models that match the query criteria. In this example, for all users whose `gender` is `1`, change `gender_show` to male:

```php
use App\Model\User;

User::query()->where('gender', 1)->update(['gender_show' => 'male']);
```

> During batch update, the updated model will not trigger the `saved` and `updated` events. Because during batch update, the model is not instantiated. At the same time, the corresponding `casts` will not be executed. For example, in the `json` format in the database, the `casts` field in the Model class is marked as `array`. If batch update is used, the `array` will not be automatically converted during insertion. In `json` string format.

### Batch assignment

You can also save a new model using the `create` method, which returns a model instance. However, before using it, you need to specify the `fillable` or `guarded` attribute on the model, because all models cannot be batch assigned by default.

When the user passes in an unexpected parameter via an HTTP request, and that parameter changes a field in the database that you don't need to change. For example: a malicious user may pass in the `is_admin` parameter through an HTTP request and then pass it to the `create` method. This operation allows the user to upgrade himself to an administrator.

Therefore, before starting, you should define which attributes on the model can be batch assigned. You can do this via the `$fillable` attribute on the model. For example: Let the `name` attribute of the `User` model be batch assigned:

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

Once we have set up the properties that can be batch assigned, we can insert new data into the database through the `create` method. The `create` method will return the saved model instance:

```php
use App\Model\User;

$user = User::create(['name' => 'Hyperf']);
```

If you already have a model instance, you can pass an array to the fill method to assign values ​​to:

```php
$user->fill(['name' => 'Hyperf']);
```

### Protected attributes

`$fillable` can be regarded as a "whitelist" for batch assignment, and you can also use the `$guarded` attribute to achieve this. The `$guarded` attribute contains arrays for which batch assignment is not allowed. In other words, `$guarded` will function more like a "blacklist". Note: You can only use one of `$fillable` or `$guarded`, not both at the same time. In the following example, except for the `gender_show` attribute, all other attributes can be batch assigned:

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

### Other creation methods

`firstOrCreate` / `firstOrNew`

There are two methods you might use for batch assignment: `firstOrCreate` and `firstOrNew`.

The `firstOrCreate` method will match the data in the database with the given column/value. If the corresponding model cannot be found in the database, a record will be created from the attributes of the first parameter and even the attributes of the second parameter and inserted into the database.

The `firstOrNew` method, like the `firstOrCreate` method, attempts to find a record in the database by the given attribute. The difference is that if the `firstOrNew` method cannot find the corresponding model, it will return a new model instance. Note that the model instance returned by `firstOrNew` has not yet been saved to the database. You need to manually call the `save` method to save:

```php
<?php
use App\Model\User;

// Find the user by name, create it if it does not exist...
$user = User::firstOrCreate(['name' => 'Hyperf']);

// Find the user by name. If it does not exist, use the name and gender, age attributes to create...
$user = User::firstOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);

// Find the user by name, create an instance if it does not exist...
$user = User::firstOrNew(['name' => 'Hyperf']);

// Find the user by name. If it does not exist, use the name and gender, age attributes to create an instance...
$user = User::firstOrNew(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

### Delete model

The `delete` method can be called on a model instance to delete the instance:

```php
use App\Model\User;

$user = User::query()->find(1);

$user->delete();
```

### Delete model by query

You can delete model data by calling the `delete` method on the query, in this example we will delete all users whose `gender` is `1`. Like batch update, batch delete does not start any model events for the deleted model:

```php
use App\Model\User;

// Note that when using the delete method, certain query conditions must be established to safely delete data. If there is no where condition, the entire data table will be deleted.
User::query()->where('gender', 1)->delete(); 
```

### Delete data directly by primary key

In the above example, you need to find the corresponding model in the database before calling `delete`. In fact, if you know the primary key of the model, you can delete the model data directly through the `destroy` static method without having to look it up in the database first. In addition to accepting a single primary key as a parameter, the `destroy` method also accepts multiple primary keys, or uses an array or collection to save multiple primary keys:

```php
use App\Model\User;

User::destroy(1);

User::destroy([1,2,3]);
```

### Soft delete

In addition to actually deleting database records, `Hyperf` can also "soft delete" models. A soft-deleted model is not actually deleted from the database. In fact, the `deleted_at` attribute is set on the model and its value is written to the database. If the value of `deleted_at` is non-empty, it means that the model has been soft deleted. If you want to enable model soft deletion, you need to use the `Hyperf\Database\Model\SoftDeletes` trait on the model

> The `SoftDeletes` trait will automatically convert the `deleted_at` attribute into a `DateTime / Carbon` instance

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

The `restoreOrCreate` method will match the data in the database with the given column/value. If the corresponding model is found in the database, execute the `restore` method to restore the model, otherwise a record will be created from the attributes of the first parameter and even the attributes of the second parameter and inserted into the database.

```php
// Look up users by name, create them with name and gender, age attributes if they don't exist...
$user = User::restoreOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

## Bit type

By default, when converting the database model in Hyperf to SQL, parameter values ​​will be uniformly converted to String type to solve the problem of int in large numbers and make it easier for value types to match indexes. If you want to make `ORM` support `bit` type, just add the following event listener code.

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Connection;
use Hyperf\Database\MySqlBitConnection;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

#[Listener]
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
