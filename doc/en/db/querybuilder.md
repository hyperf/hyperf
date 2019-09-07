# Query constructor

## Introduction

Hyperf's database query constructor provides a convenient interface for creating and running database queries. It can be used to perform most database operations in an application and can run on all supported database systems.

Hyperf's query constructor uses PDO parameter binding to protect your application from SQL injection attacks. Therefore there is no need to clean up the string passed as a binding.

Only some of the commonly used tutorials are available here. The specific tutorials can be viewed on the Laravel website.
[Laravel Query Builder](https://laravel.com/docs/5.8/queries)

## Get results

```php
use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM user;');
$users = Db::table('user')->get();
$users = Db::table('user')->select('name', 'gender as user_gender')->get();
```

The `Db::select()` method returns an array, and the `get` method returns `Hyperf\Utils\Collection`. The element is `stdClass`, so you can return the data of each element with the following code.

```php
<?php

foreach ($users as $user) {
    echo $user->name;
}
```

### Convert the result to an array format

In some scenarios, you may want to use the `Array` instead of the `stdClass` object structure in the result of the query, and the `Eloquent` removes the default `FetchMode` configured by the configuration. At this point you can change the configuration by listening to the `Hyperf\Database\Events\StatementPrepared` event through the listener:

```php
<?php
declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Events\StatementPrepared;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use PDO;

/**
 * @Listener
 */
class FetchModeListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            StatementPrepared::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof StatementPrepared) {
            $event->statement->setFetchMode(PDO::FETCH_ASSOC);
        }
    }
}
```

### Get the value of a row

If you want to get a value for a row, you can use the `first` method.

```php
<?php
use Hyperf\DbConnection\Db;

$row = Db::table('user')->first(); // sql Will automatically add limit 1
var_dump($row);
```

### Get a single value

If you want to get a single value, you can use the `value` method.

```php
<?php
use Hyperf\DbConnection\Db;

$id = Db::table('user')->value('id');
var_dump($id);
```

### Get the value of a column

If you want to get a collection with a single column value, you can use the `pluck` method. In the following example, we will get a collection of headings in the role table:

```php
<?php
use Hyperf\DbConnection\Db;

$names = Db::table('user')->pluck('name');

foreach ($names as $name) {
    echo $names;
}

```

You can also specify a custom key-value for the field in the returned collection:

```php
<?php
use Hyperf\DbConnection\Db;

$roles = Db::table('roles')->pluck('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}

```

### Blocking results

If you need to process thousands of database records, you can consider using the `chunk` method. This method takes a small chunk of the result set at a time and passes it to the `closure` function for processing. This method is very useful when `Command` is written with thousands of pieces of data. For example, we can cut all user table data into a small piece of 100 records at a time:

```php
<?php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        //
    }
});
```

You can stop continuing to get chunked results by returning `false` in the closure:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {

    return false;
});
```

If you want to update the database records when the results are chunked, the block results may be inconsistent with the expected return results. Therefore, it is best to use the chunkById method when updating records in chunks. This method automatically paginates the results based on the recorded primary key:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->where('gender', 1)->chunkById(100, function ($users) {
    foreach ($users as $user) {
        Db::table('user')
            ->where('id', $user->id)
            ->update(['update_time' => time()]);
    }
});
```

> When a record is updated or deleted in a block's callback, any changes to the primary or foreign key may affect the block query. This may result in records not being included in the chunked results.

### Aggregate query

The framework also provides aggregate class methods such as `count`, `max`, `min`, `avg`, `sum`.

```php
use Hyperf\DbConnection\Db;

$count = Db::table('user')->count();
```

#### Determine if the record exists

In addition to the `count` method to determine if the result of the query condition exists, you can also use the `exists` and `doesntExist` methods:

```php
return Db::table('orders')->where('finalized', 1)->exists();

return Db::table('orders')->where('finalized', 1)->doesntExist();
```

## Query

### Specify a Select statement

Of course you may not always want to get all the columns from the database table. Using the select method, you can customize a select query to query the specified field:

```php
$users = Db::table('user')->select('name', 'email as user_email')->get();
```

The `distinct` method forces the result returned by the query to not be repeated:

```php
$users = Db::table('user')->distinct()->get();
```

If you already have a query constructor instance and want to include a field in an existing query, you can use the addSelect method:

```php
$query = Db::table('users')->select('name');

$users = $query->addSelect('age')->get();
```

## Original expression

Sometimes you need to use the original expression in your query, for example implementing `COUNT(0) AS count`, which requires the `raw` method.

```php
use Hyperf\DbConnection\Db;

$res = Db::table('user')->select('gender', Db::raw('COUNT(0) AS `count`'))->groupBy('gender')->get();
```

### Forced index

The slow query problem of the database, more than 90% of the index is wrong, some of the queries are because the database server's `query optimizer` does not use the best index, then you need to use the mandatory index:

```php
Db::table(Db::raw(sprintf("{$table} FORCE INDEX({$index})");
```

### Native method

You can use the following method instead of `Db::raw` to insert a native expression into each part of the query.

The `selectRaw` method can be used instead of `select(Db::raw(...))`. The second argument to the method is an option, and the value is an array of bound parameters:

```php
$orders = Db::table('order')
    ->selectRaw('price * ? as price_with_tax', [1.0825])
    ->get();
```

The `whereRaw` and `orWhereRaw` methods inject the native `where` into your query. The second argument to these two methods is still an option, and the value is still an array of bound parameters:

```php
$orders = Db::table('order')
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->get();
```

The `havingRaw` and `orHavingRaw` methods can be used to set the native string to the value of the `having` statement:

```php
$orders = Db::table('order')
    ->select('department', Db::raw('SUM(price) as total_sales'))
    ->groupBy('department')
    ->havingRaw('SUM(price) > ?', [2500])
    ->get();
```

The `orderByRaw` method can be used to set the native string to the value of the `order by` clause:

```php
$orders = Db::table('order')
    ->orderByRaw('updated_at - created_at DESC')
    ->get();
```

## Table connection

### Inner Join Clause

The query constructor can also write `join` methods. To perform the basic `internal link', you can use the `join` method on the query constructor instance. The first argument passed to the `join` method is the name of the table you need to join, while the other arguments are bound using the fields of the specified connection. You can also join multiple data tables in a single query:

```php
$users = Db::table('users')
    ->join('contacts', 'users.id', '=', 'contacts.user_id')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.*', 'contacts.phone', 'orders.price')
    ->get();
```

### Left Join

If you want to use `"Left Connection" or "Right Connection" instead of "Inside Connection", you can use the `leftJoin` or `rightJoin` method. These two methods are used in the same way as the `join` method:

```php
$users = Db::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
$users = Db::table('users')
    ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

### Cross Join 

Use the `crossJoin` method to do the `cross-connect` with the name of the table you want to connect to. A cross-connection creates a Cartesian product between the first table and the joined table:

```php
$users = Db::table('sizes')
    ->crossJoin('colours')
    ->get();
```

### Advanced Join 

You can specify a more advanced `join` statement. For example, passing a `closure` as the second argument to the `join` method. This `closure` receives a `JoinClause` object, specifying the constraints specified in the `join` statement:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')->orOn(...);
    })
    ->get();
```

You can specify a more advanced `join` statement. For example, passing a `cloque` as a `join` method. If you want to use a `where' style statement on a connection, you can use the `where` and `orWhere` methods on the connection. These methods compare the column and value, not the second parameter of the column and column comparison: This `closure` receives a `JoinClause` object, specifying the constraints specified in the `join` statement:...

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
                ->where('contacts.user_id', '>', 5);
    })
    ->get();
```

### Sub join query

You can associate a query as a subquery using the `joinSub`, `leftJoinSub` and `rightJoinSub` methods. Each of them receives three parameters: a subquery, a table alias, and a closure that defines the associated field:

```php
$latestPosts = Db::table('posts')
    ->select('user_id', Db::raw('MAX(created_at) as last_post_created_at'))
    ->where('is_published', true)
    ->groupBy('user_id');

$users = Db::table('users')
    ->joinSub($latestPosts, 'latest_posts', function($join) {
        $join->on('users.id', '=', 'latest_posts.user_id');
    })->get();
```

## Joint query

The Query Builder also provides a shortcut to "join" two queries. For example, you can create a query first and then combine it with the second query using the `union` method:

```php
$first = Db::table('users')->whereNull('first_name');

$users = Db::table('users')
    ->whereNull('last_name')
    ->union($first)
    ->get();
```

## Where 

### Simple Where

In constructing the `where` query instance, you can use the `where` method. The most basic way to call `where` is to pass three arguments: the first argument is the column name, the second argument is any operator supported by the database system, and the third is the value to be compared for that column.

For example, here is a query to verify that the value of the gender field is equal to 1:

```php
$users = Db::table('user')->where('gender', '=', 1)->get();
```

For convenience, if you simply compare the column values to the given values, you can use the values directly as the second argument to the `where` method:

```php
$users = Db::table('user')->where('gender', 1)->get();
```

Of course, you can also use other operators to write the where clause:

```php
$users = Db::table('users')->where('gender', '>=', 0)->get();

$users = Db::table('users')->where('gender', '<>', 1)->get();

$users = Db::table('users')->where('name', 'like', 'T%')->get();
```

You can also pass a conditional array to the where function:

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
])->get();
```

### Or 

You can chain the `where` constraint together, or you can add the `or` clause to the query. The `orWhere` method is the same as the `where` method:

```php
$users = Db::table('user')
    ->where('gender', 1)
    ->orWhere('name', 'John')
    ->get();
```

### Other Where

#### whereBetween

The `whereBetween` method verifies that the field value is between the two values given:

```php
$users = Db::table('users')->whereBetween('votes', [1, 100])->get();
```

#### whereNotBetween

The `whereNotBetween` method verifies that the field value is outside of the given two values:

```php
$users = Db::table('users')->whereNotBetween('votes', [1, 100])->get();
```

#### whereIn / whereNotIn

The value of the `whereIn` method validation field must exist in the specified array:

```php
$users = Db::table('users')->whereIn('id', [1, 2, 3])->get();
```

The value of the `whereNotIn` method validation field must not exist in the specified array:

```php
$users = Db::table('users')->whereNotIn('id', [1, 2, 3])->get();
```

### Parameter grouping

Sometimes you need to create a more advanced `where` clause, such as `"where exists"` or nested parameter groupings. The Query Builder can also handle these. Let's look at an example of grouping constraints in parentheses:

```php
Db::table('users')->where('name', '=', 'John')
    ->where(function ($query) {
        $query->where('votes', '>', 100)
                ->orWhere('title', '=', 'Admin');
    })
    ->get();
```

As you can see, construct a query constructor to constrain a group by writing a `where` method with a `Closure`. This `Closure` receives a query instance that you can use to set the constraints that should be included. The above example will generate the following SQL:

```sql
select * from users where name = 'John' and (votes > 100 or title = 'Admin')
```

> You should call this group with orWhere to avoid accidents with the global effect of the application.

#### Where Exists 

The `whereExists` method allows you to use the `where exists SQL` statement. The `whereExists` method receives a `Closure` parameter that accepts a `Closure` parameter that takes a query builder instance and allows you to define the query placed in the `exists` clause:

```php
Db::table('users')->whereExists(function ($query) {
    $query->select(Db::raw(1))
            ->from('orders')
            ->whereRaw('orders.user_id = users.id');
})
->get();
```

The above query will produce the following SQL statement:

```sql
select * from users
where exists (
    select 1 from orders where orders.user_id = users.id
)
```

#### JSON Where 

`Hyperf` also supports querying fields of type `JSON` (only on databases supported by the `JSON` type).

```php
$users = Db::table('users')
    ->where('options->language', 'en')
    ->get();

$users = Db::table('users')
    ->where('preferences->dining->meal', 'salad')
    ->get();
```

You can also use `whereJsonContains` to query the `JSON` array:

```php
$users = Db::table('users')
    ->whereJsonContains('options->languages', 'en')
    ->get();
```

You can use `whereJsonLength` to query the length of the `JSON` array:

```php
$users = Db::table('users')
    ->whereJsonLength('options->languages', 0)
    ->get();

$users = Db::table('users')
    ->whereJsonLength('options->languages', '>', 1)
    ->get();
```

## Ordering, Grouping, Limit, & Offset

### orderBy

The `orderBy` method allows you to sort the result set by the given field. The first argument of `orderBy` should be the field you wish to sort, and the second argument controls the direction of the sort, which can be `asc` or `desc`

```php
$users = Db::table('users')
    ->orderBy('name', 'desc')
    ->get();
```

### latest / oldest

The `latest` and `oldest` methods allow you to easily sort by date. It uses the `created_at` column as the sort by default. Of course, you can also pass a custom column name:

```php
$user = Db::table('users')->latest()->first();
```

### inRandomOrder

The `inRandomOrder` method is used to randomly sort the results. For example, you can use this method to randomly find a user.

```php
$randomUser = Db::table('users')->inRandomOrder()->first();
```

### groupBy / having

The `groupBy` and `having` methods can group the results. The use of the `having` method is very similar to the `where` method:

```php
$users = Db::table('users')
    ->groupBy('account_id')
    ->having('account_id', '>', 100)
    ->get();
```

You can pass multiple parameters to the `groupBy` method:

```php
$users = Db::table('users')
    ->groupBy('first_name', 'status')
    ->having('account_id', '>', 100)
    ->get();
```

> For the more advanced having syntax, see the havingRaw method.

### skip / take

To limit the number of returns to the result, or to skip the specified number of results, you can use the `skip` and `take` methods:

```php
$users = Db::table('users')->skip(10)->take(5)->get();
```

Or you can use the limit and offset methods:

```php
$users = Db::table('users')->offset(10)->limit(5)->get();
```

## When

Sometimes you may want a clause to only apply to a query if it is true. For example, you might only want to apply the `where` statement if the given value exists in the request. You can do this by using the `when` method:

```php
$role = $request->input('role');

$users = Db::table('users')
    ->when($role, function ($query, $role) {
        return $query->where('role_id', $role);
    })
    ->get();
```

The `when` method only executes the given closure when the first argument is `true`. If the first argument is `false` then the closure will not be executed.

You can pass another closure as the third argument to the `when` method. The closure will be executed if the first argument is `false`. To illustrate how to use this feature, let's configure the default ordering of a query:

```php
$sortBy = null;

$users = Db::table('users')
    ->when($sortBy, function ($query, $sortBy) {
        return $query->orderBy($sortBy);
    }, function ($query) {
        return $query->orderBy('name');
    })
    ->get();
```

## Insert

The query constructor also provides the `insert` method for inserting records into the database. The `insert` method accepts the field name and field value in the form of an array for insertion:

```php
Db::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

You can even pass an array to the `insert` method and insert multiple records into the table.

```php
Db::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);
```

### Self-increase ID

If the data table has a self-incrementing `ID`, use the `insertGetId` method to insert the record and return the `ID` value.

```php
$id = Db::table('users')->insertGetId(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

## Update

Of course, in addition to inserting records into the database, the query constructor can also update existing records with the `update` method. The `update` method, like the `insert` method, accepts an array containing the fields and values to be updated. You can constrain the `update` query with the `where` clause:

```php
Db::table('users')->where('id', 1)->update(['votes' => 1]);
```

### Update or add

Sometimes you may want to update an existing record in the database or create it if there is no matching record. In this case, you can use the `updateOrInsert` method. The `updateOrInsert` method takes two arguments: an array of conditions for looking up the record, and an array of key-value pairs containing the record to be more.

The `updateOrInsert` method will first try to find the matching database record using the key and value pairs of the first parameter. If the record exists, the value in the second parameter is used to update the record. If the record is not found, a new record is inserted and the updated data is a collection of two arrays:

```php
Db::table('users')->updateOrInsert(
    ['email' => 'john@example.com', 'name' => 'John'],
    ['votes' => '2']
);
```

### Update JSON field

When updating a JSON field, you can use the -> syntax to access the corresponding value in the JSON object. This operation only supports MySQL 5.7+:

```php
Db::table('users')->where('id', 1)->update(['options->enabled' => true]);
```

### Self-increasing and self-decreasing

The Query Builder also provides a convenient way to increment or decrement a given field. This method provides a more expressive and more refined interface than writing the `update` statement manually.

Both methods receive at least one parameter: the column that needs to be modified. The second parameter is optional and is used to control the amount of column increment or decrement:

```php
Db::table('users')->increment('votes');

Db::table('users')->increment('votes', 5);

Db::table('users')->decrement('votes');

Db::table('users')->decrement('votes', 5);
```

You can also specify which fields to update during the operation:

```php
Db::table('users')->increment('votes', 1, ['name' => 'John']);
```

## Delete

The query constructor can also delete records from a table using the `delete` method. Before using `delete`, you can add the `where` clause to constrain the `delete` syntax:

```php
Db::table('users')->delete();

Db::table('users')->where('votes', '>', 100)->delete();
```

If you need to empty the table, you can use the `truncate` method, which will remove all rows and reset the auto increment `ID` to zero:

```php
Db::table('users')->truncate();
```

## Pessimistic lock

The Query Builder also contains functions that can help you implement "pessimistic locking" on the `select` syntax. If you want to implement a `shared lock' in your query, you can use the `sharedLock` method. Shared locks prevent selected data columns from being tampered until the transaction is committed

```php
Db::table('users')->where('votes', '>', 100)->sharedLock()->get();
```

Alternatively, you can use the `lockForUpdate` method. Use the `"update"` lock to prevent lines from being modified or selected by other shared locks:

```php
Db::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
```

