# Model

The model component is derived from [Eloquent ORM](https://laravel.com/docs/5.8/eloquent), and related operations can be referred to in the Eloquent ORM documentation.

## Creating Models

Hyperf provides a command to create models, which allows you to easily create corresponding models based on database tables. The command generates models via `AST`, so when you add some methods, you can also use the script to conveniently reset the model.

```
php bin/hyperf.php gen:model table_name
```

The optional parameters are as follows:

| Parameter | Type | Default Value | Remark |
| :---: | :---: | :---: | :---: |
| --pool | string | `default` | Connection pool, the script will create based on the current connection pool configuration |
| --path | string | `app/Model` | Model path |
| --force-casts | bool | `false` | Whether to force reset the `casts` parameter |
| --prefix | string | Empty String | Table prefix |
| --inheritance | string | `Model` | Parent class |
| --uses | string | `Hyperf\DbConnection\Model\Model` | Used in conjunction with `inheritance` |
| --refresh-fillable | bool | `false` | Whether to refresh the `fillable` parameter |
| --table-mapping | array | `[]` | Increase mapping relationship for table name -> model, e.g., ['users:Account'] |
| --ignore-tables | array | `[]` | Table names that do not need to generate models, e.g., ['users'] |
| --with-comments | bool | `false` | Whether to add field comments |
| --property-case | int | `0` | Field type: 0 snake_case, 1 camelCase |

When converting field types to camelCase using `--property-case`, you also need to manually add `Hyperf\Database\Model\Concerns\CamelCase` to the model.

Corresponding configurations can also be configured in `databases.{pool}.commands.gen:model`, as follows:

> All hyphens need to be converted to underscores.

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // Ignore other configurations
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

## Model Member Variables

| Parameter | Type | Default Value | Remark |
| :---: | :---: | :---: | :---: |
| connection | string | default | Database connection |
| table | string | None | Table name |
| primaryKey | string | id | Model primary key |
| keyType | string | int | Primary key type |
| fillable | array | [] | Allowed mass-assignable attributes |
| casts | string | None | Data formatting configuration |
| timestamps | bool | true | Whether to automatically maintain timestamps |
| incrementing | bool | true | Whether primary key is auto-incrementing |

### Table Name

If we do not specify the table corresponding to the model, it will use the "snake_case" plural form of the class name as the table name. Therefore, in this case, Hyperf will assume that the User model stores data in the `users` table. You can specify a custom table name by defining the `table` property on the model:

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

Hyperf assumes that each table has a primary key column named `id`. You can define a protected `$primaryKey` property to override this convention.

In addition, Hyperf assumes that the primary key is an auto-incrementing integer value, which means that by default, the primary key will be automatically converted to an integer type. If you wish to use a non-incrementing or non-numeric primary key, you need to set the public `$incrementing` property to `false`. If your primary key is not an integer, you need to set the protected `$keyType` property on the model to `string`.

### Timestamps

By default, Hyperf expects `created_at` and `updated_at` to exist in your table. If you do not want Hyperf to automatically manage these two columns, set the `$timestamps` property in the model to `false`:

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

If you need to customize the format of the timestamp, set the `$dateFormat` property in your model. This property determines how date attributes are stored in the database, as well as their format when the model is serialized to an array or JSON:

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

If you do not want to store in `datetime` format, or if you want to perform further processing on the time, you can do so by overriding the `fromDateTime($value)` method in the model.

If you need to customize the field names for storing timestamps, you can set the values of the `CREATED_AT` and `UPDATED_AT` constants in the model. Setting one of them to `null` indicates that you do not want the ORM to process that field:

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

### Database Connection

By default, Hyperf models will use the `default` database connection configured for your application. If you want to specify a different connection for the model, set the `$connection` property: of course, the `connection-name` as a `key` must exist in the `databases.php` configuration file.

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

### Default Attribute Values

If you want to define default values for some attributes of the model, you can define the `$attributes` property on the model:

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

## Model Queries

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();
```

### Reloading Models

You can use the `fresh` and `refresh` methods to reload a model. The `fresh` method will re-retrieve the model from the database. The existing model instance remains unaffected:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$freshUser = $user->fresh();
```

The `refresh` method re-assigns the existing model with new data from the database. In addition, already loaded relationships will be re-loaded:

```php
<?php
use App\Model\User;

/** @var User $user */
$user = User::query()->where('name','Hyperf')->first();

$user->name = 'Hyperf2';

$user->refresh();

echo $user->name; // Hyperf
```

### Collections

For the `all` and `get` methods in the model, they can query multiple results and return a `Hyperf\Database\Model\Collection` instance. The `Collection` class provides many helper functions to handle query results:

```php
$users = $users->reject(function ($user) {
    // Exclude all deleted users
    return $user->deleted;
});
```

### Retrieving a Single Model

In addition to retrieving all records from the specified table, you can use the `find` or `first` method to retrieve a single record. These methods return a single model instance instead of a collection of models:

```php
<?php
use App\Model\User;

$user = User::query()->where('id', 1)->first();

$user = User::query()->find(1);
```

### Retrieving Multiple Models

Of course, the `find` method does not only support a single model.

```php
<?php
use App\Model\User;

$users = User::query()->find([1, 2, 3]);
```

### "Not Found" Exception

Sometimes you want to throw an exception when a model is not found, which is very useful in controllers and routes. The `findOrFail` and `firstOrFail` methods will retrieve the first result of the query, and if not found, will throw a `Hyperf\Database\Model\ModelNotFoundException` exception:

```php
<?php
use App\Model\User;

$model = User::findOrFail(1);
$model = User::where('age', '>', 18)->firstOrFail();
```

### Aggregate Functions

You can also use the `count`, `sum`, `max`, and other aggregate functions provided by the query builder. These methods will only return the appropriate scalar value rather than a model instance:

```php
<?php
use App\Model\User;

$count = User::query()->where('gender', 1)->count();
```

## Inserting & Updating Models

### Inserting

To add a new record to the database, create a new model instance, set the attributes for the instance, and then call the `save` method:

```php
use App\Model\User;

/** @var User $user */
$user = new User();

$user->name = 'Hyperf';

$user->save();
```

In this example, we assign a value to the `name` attribute of the `App\Model\User` model instance. When the `save` method is called, a new record will be inserted. The `created_at` and `updated_at` timestamps will be automatically set and do not need to be manually assigned.

### Updating

The `save` method can also be used to update models that already exist in the database. To update a model, you need to retrieve it first, set the attributes to be updated, and then call the `save` method. Similarly, the `updated_at` timestamp will be automatically updated, so it does not need to be manually assigned:

```php
use App\Model\User;

/** @var User $user */
$user = User::query()->find(1);

$user->name = 'Hi Hyperf';

$user->save();
```

### Batch Updates

You can also update multiple models that match query conditions. In this example, for all users whose `gender` is `1`, we change `gender_show` to "Male":

```php
use App\Model\User;

User::query()->where('gender', 1)->update(['gender_show' => 'Male']);
```

> During batch updates, the updated models will not trigger `saved` and `updated` events. Because during batch updates, the models are not instantiated. Also, corresponding `casts` will not be executed, for example, if `json` format in database is marked as `array` in `casts` field of Model class, if using batch update, the `array` will not be automatically converted to `json` string format during insertion.

### Mass Assignment

You can also use the `create` method to save a new model, and this method will return the model instance. However, before using it, you need to specify `fillable` or `guarded` attributes on the model, because all models default to not allowing mass assignment.

When a user passes an unexpected parameter through an HTTP request, and that parameter changes a field in the database that you did not want changed. For example: a malicious user might pass the `is_admin` parameter through an HTTP request, and then pass it to the `create` method, this operation can allow the user to upgrade themselves to an administrator.

Therefore, before starting, you should define which attributes on the model can be mass-assigned. You can do this through the `$fillable` attribute on the model. For example: allow the `name` attribute of the `User` model to be mass-assigned:

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

Once we have set the attributes that can be mass-assigned, we can insert new data into the database through the `create` method. The `create` method will return the saved model instance:

```php
use App\Model\User;

$user = User::create(['name' => 'Hyperf']);
```

If you already have a model instance, you can pass an array to the `fill` method to assign values:

```php
$user->fill(['name' => 'Hyperf']);
```

### Guarded Attributes

`$fillable` can be thought of as a "whitelist" for mass assignment, you can also use the `$guarded` attribute to achieve this. The `$guarded` attribute contains an array of attributes that are not allowed to be mass-assigned. That is to say, `$guarded` functions more like a "blacklist". Note: You can only use either `$fillable` or `$guarded`, not both at the same time. In the following example, except for the `gender_show` attribute, other attributes can be mass-assigned:

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

### Other Creation Methods

`firstOrCreate` / `firstOrNew`

There are two methods you might use for mass assignment: `firstOrCreate` and `firstOrNew`.

The `firstOrCreate` method matches data in the database with the given column/value. If no matching model is found in the database, a record will be created and inserted into the database from the attributes of the first argument and the attributes of the second argument.

The `firstOrNew` method, like the `firstOrCreate` method, attempts to find a record in the database with the given attributes. The difference is that if the `firstOrNew` method cannot find a matching model, it returns a new model instance. Note that the model instance returned by `firstOrNew` has not yet been saved to the database, you need to manually call the `save` method to save it:

```php
<?php
use App\Model\User;

// Find user by name, create if it doesn't exist...
$user = User::firstOrCreate(['name' => 'Hyperf']);

// Find user by name, if it doesn't exist, create it using name, gender, and age attributes...
$user = User::firstOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);

// Find user by name, create an instance if it doesn't exist...
$user = User::firstOrNew(['name' => 'Hyperf']);

// Find user by name, create an instance using name, gender, and age attributes if it doesn't exist...
$user = User::firstOrNew(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

### Deleting Models

You can call the `delete` method on a model instance to delete the instance:

```php
use App\Model\User;

$user = User::query()->find(1);

$user->delete();
```

### Deleting Models via Query

You can delete model data by calling the `delete` method on the query. In this example, we will delete all users whose `gender` is `1`. Like batch updates, batch deletes do not trigger any model events for the deleted models:

```php
use App\Model\User;

// Note: When using the delete method, it must be based on some query conditions to safely delete data. If there is no where condition, it will cause the deletion of the entire table.
User::query()->where('gender', 1)->delete(); 
```

### Deleting Data Directly by Primary Key

In the examples above, you need to search for the corresponding model in the database before calling `delete`. In fact, if you know the model's primary key, you can delete the model data directly through the `destroy` static method without searching in the database first. The `destroy` method, in addition to accepting a single primary key as an argument, also accepts multiple primary keys, or uses arrays or collections to hold multiple primary keys:

```php
use App\Model\User;

User::destroy(1);

User::destroy([1,2,3]);
```

### Soft Deletes

In addition to actually deleting database records, `Hyperf` can also "soft delete" models. A soft-deleted model is not actually removed from the database. In fact, a `deleted_at` attribute is set on the model and its value is written to the database. If the `deleted_at` value is not null, it means that the model has been soft deleted. To enable the model soft delete feature, you need to use the `Hyperf\Database\Model\SoftDeletes` trait on the model.

> The `SoftDeletes` trait will automatically convert the `deleted_at` attribute to a `DateTime / Carbon` instance.

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

The `restoreOrCreate` method matches data in the database with the given column/value. If the corresponding model is found in the database, the `restore` method is executed to restore the model; otherwise, a record is created from the attributes of the first argument and the attributes of the second argument and inserted into the database.

```php
// Find user by name, if it doesn't exist, create it using name, gender, and age attributes...
$user = User::restoreOrCreate(
    ['name' => 'Hyperf'],
    ['gender' => 1, 'age' => 20]
);
```

## Bit Type

By default, in the process of converting database models to SQL in Hyperf, parameter values are uniformly converted to String type to solve the problem of large numbers in `int` and to make value types easier to match indexes. If you want `ORM` to support the `bit` type, you only need to add the following event listener code.

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
