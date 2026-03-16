# Query builder

## Introduction

Hyperf's database query builder provides a convenient interface for creating and running database queries. It can be used to perform most database operations in an application and runs on all supported database systems.

Hyperf's query builder uses PDO parameter binding to protect your application from SQL injection attacks. So there is no need to sanitize strings passed as bindings.

Only some commonly used tutorials are provided here, and specific tutorials can be viewed on the Laravel official website.
[Laravel Query Builder](https://laravel.com/docs/5.8/queries)

## Get results

```php
use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM user;');
$users = Db::table('user')->get();
$users = Db::table('user')->select('name', 'gender as user_gender')->get();
```

The `Db::select()` method returns an array, and the `get` method returns `Hyperf\Collection\Collection`. The element is `stdClass`, so the data of each element can be returned by the following code

```php
<?php

foreach ($users as $user) {
    echo $user->name;
}
```

### Convert the result to array format

In some scenarios, you may want to use `Array` instead of `stdClass` object structure in the query result, and `Eloquent` removes the default `FetchMode` configured by configuration, then At this point, you can change the configuration by listening to the `Hyperf\Database\Events\StatementPrepared` event through the listener:

```php
<?php
declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Events\StatementPrepared;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use PDO;

#[Listener]
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

### Get the value of a column

If you want to get a collection containing a single column of values, you can use the `pluck` method. In the following example, we will get a collection of titles in the roles table:

```php
<?php
use Hyperf\DbConnection\Db;

$names = Db::table('user')->pluck('name');

foreach ($names as $name) {
    echo $names;
}

```

You can also specify custom keys for fields in the returned collection:

```php
<?php
use Hyperf\DbConnection\Db;

$roles = Db::table('roles')->pluck('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}

```

### Chunked results

If you need to process thousands of database records, you might consider using the `chunk` method. This method takes a small chunk of the result set at a time and passes it to the `closure` function for processing. This method is very useful when `Command` is writing thousands of pieces of processing data. For example, we can cut the entire user table data into small pieces that process 100 records at a time:

```php
<?php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        //
    }
});
```

You can stop fetching chunked results by returning `false` in the closure:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {

    return false;
});
```

If you are updating database records while chunking the results, the chunked results may not be the same as expected. Therefore, when updating records in chunks, it is better to use the chunkById method. This method will automatically paginate the results based on the record's primary key:

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

> Any changes to the primary or foreign keys may affect the block query while updating or deleting records inside the block's callback. This may result in records not being included in the chunked result.

### Aggregate query

The framework also provides aggregate class methods such as `count`, `max`, `min`, `avg`, `sum`.

```php
use Hyperf\DbConnection\Db;

$count = Db::table('user')->count();
```

#### Determine if the record exists

In addition to using the `count` method to determine whether the result of a query condition exists, you can also use the `exists` and `doesntExist` methods:

```php
return Db::table('orders')->where('finalized', 1)->exists();

return Db::table('orders')->where('finalized', 1)->doesntExist();
```

## Inquire

### Specify a Select statement

Of course you may not always want to get all the columns from the database table. Using the select method, you can customize a select query statement to query the specified fields:

```php
$users = Db::table('user')->select('name', 'email as user_email')->get();
```

The `distinct` method forces the query to return unique results:

```php
$users = Db::table('user')->distinct()->get();
```

If you already have a query builder instance and want to add a field to the existing query, you can use the addSelect method:

```php
$query = Db::table('users')->select('name');

$users = $query->addSelect('age')->get();
```

## Original expression

Sometimes you need to use raw expressions in a query, for example to implement `COUNT(0) AS count`, which requires the use of the `raw` method.

```php
use Hyperf\DbConnection\Db;

$res = Db::table('user')->select('gender', Db::raw('COUNT(0) AS `count`'))->groupBy('gender')->get();
```

### Native method

The following methods can be used instead of `Db::raw` to insert raw expressions into various parts of the query.

The `selectRaw` method can be used in place of `select(Db::raw(...))`. The second parameter of this method is optional, and the value is an array of bound parameters:

```php
$orders = Db::table('order')
    ->selectRaw('price * ? as price_with_tax', [1.0825])
    ->get();
```

The `whereRaw` and `orWhereRaw` methods inject native `where` into your query. The second parameter of these two methods is still optional, and the value is still an array of bound parameters:

```php
$orders = Db::table('order')
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->get();
```

The `havingRaw` and `orHavingRaw` methods can be used to set a raw string as the value of a `having` statement:

```php
$orders = Db::table('order')
    ->select('department', Db::raw('SUM(price) as total_sales'))
    ->groupBy('department')
    ->havingRaw('SUM(price) > ?', [2500])
    ->get();
```

The `orderByRaw` method can be used to set a raw string as the value of the `order by` clause:

```php
$orders = Db::table('order')
    ->orderByRaw('updated_at - created_at DESC')
    ->get();
```

## Join table

### Inner Join Clause

Query builders can also write `join` methods. To perform basic `"inner join"`, you can use the `join` method on the query builder instance. The first argument passed to the `join` method is the name of the table you want to join, while the other arguments use the field constraints that specify the join. You can also join multiple tables in a single query:

```php
$users = Db::table('users')
    ->join('contacts', 'users.id', '=', 'contacts.user_id')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.*', 'contacts.phone', 'orders.price')
    ->get();
```

### Left Join

If you want to use `"left join"` or `"right join"` instead of `"inner join"`, use the `leftJoin` or `rightJoin` methods. These two methods are used in the same way as the `join` method:

```php
$users = Db::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
$users = Db::table('users')
    ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

### Cross Join Statement

Use the `crossJoin` method to do a `"cross join"` with the name of the table you want to join. A cross join produces a Cartesian product between the first table and the joined tables:

```php
$users = Db::table('sizes')
    ->crossJoin('colours')
    ->get();
```

### Advanced Join Statement

You can specify more advanced `join` statements. For example passing a `closure` as the second parameter of the `join` method. This `closure` accepts a `JoinClause` object, specifying the constraints specified in the `join` statement:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')->orOn(...);
    })
    ->get();
```

If you want to use `"where"` style statements on the join, you can use the `where` and `orWhere` methods on the join. These methods compare columns to values instead of columns to columns:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
                ->where('contacts.user_id', '>', 5);
    })
    ->get();
```

### Subjoin query

You can use the `joinSub`, leftJoinSub` and `rightJoinSub` methods to join a query as a subquery. Each of their methods takes three parameters: a subquery, a table alias, and a closure that defines the associated fields:

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

## Combined query

The query builder also provides a shortcut for "joining" two queries. For example, you can create a query first, then use the `union` method to union it with the second query:

```php
$first = Db::table('users')->whereNull('first_name');

$users = Db::table('users')
    ->whereNull('last_name')
    ->union($first)
    ->get();
```

## Where statement

### Simple Where Statement

In constructing a `where` query instance, you can use the `where` method. The most basic way to call `where` is to pass three parameters: the first parameter is the column name, the second parameter is any operator supported by the database system, and the third parameter is the value to be compared for the column.

For example, here is a query to verify that the value of the gender field is equal to 1:

```php
$users = Db::table('user')->where('gender', '=', 1)->get();
```

For convenience, if you are simply comparing the column value to a given value, you can pass the value directly as the second parameter of the `where` method:

```php
$users = Db::table('user')->where('gender', 1)->get();
```

Of course, you can also use other operators to write where clauses:

```php
$users = Db::table('users')->where('gender', '>=', 0)->get();

$users = Db::table('users')->where('gender', '<>', 1)->get();

$users = Db::table('users')->where('name', 'like', 'T%')->get();
```

You can also pass an array of conditions to the where function:

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
])->get();
```

### Or Statement

You can chain `where` constraints together or add `or` clauses to the query. The `orWhere` method accepts the same parameters as the `where` method:

```php
$users = Db::table('user')
    ->where('gender', 1)
    ->orWhere('name', 'John')
    ->get();
```

### Other Where Statements

#### whereBetween

The `whereBetween` method verifies that the field value is between two given values:

```php
$users = Db::table('users')->whereBetween('votes', [1, 100])->get();
```

#### whereNotBetween

The `whereNotBetween` method verifies that the field value is outside the given two values:

```php
$users = Db::table('users')->whereNotBetween('votes', [1, 100])->get();
```

#### whereIn / whereNotIn

The `whereIn` method validates that the value of the field must exist in the specified array:

```php
$users = Db::table('users')->whereIn('id', [1, 2, 3])->get();
```

The `whereNotIn` method verifies that the value of the field must not exist in the specified array:

```php
$users = Db::table('users')->whereNotIn('id', [1, 2, 3])->get();
```

### Parameter grouping

Sometimes you need to create more advanced `where` clauses, such as `"where exists"` or nested parameter groupings. The query builder can also handle these. Below, let's see an example of grouping constraints in parentheses:

```php
Db::table('users')->where('name', '=', 'John')
    ->where(function ($query) {
        $query->where('votes', '>', 100)
                ->orWhere('title', '=', 'Admin');
    })
    ->get();
```

As you can see, a `Closure` is written to the `where` method to construct a query builder to constrain a grouping. The `Closure` receives a query instance that you can use to set constraints that should be included. The above example will generate the following SQL:

```sql
select * from users where name = 'John' and (votes > 100 or title = 'Admin')
```

> You should call this grouping with orWhere to avoid accidental application of global effects.

#### Where Exists Statement

The `whereExists` method allows you to use the `where exists SQL` statement. The `whereExists` method accepts a `Closure` parameter, the `whereExists` method accepts a `Closure` parameter, the closure takes a query builder instance allowing you to define queries placed in the `exists` clause:

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

#### JSON Where Statement

`Hyperf` also supports querying fields of type `JSON` (only on databases that support type `JSON`).

```php
$users = Db::table('users')
    ->where('options->language', 'en')
    ->get();

$users = Db::table('users')
    ->where('preferences->dining->meal', 'salad')
    ->get();
```

You can also use `whereJsonContains` to query `JSON` arrays:

```php
$users = Db::table('users')
    ->whereJsonContains('options->languages', 'en')
    ->get();
```

You can use `whereJsonLength` to query the length of a `JSON` array:

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

The `orderBy` method allows you to order the result set by a given field. The first parameter of `orderBy` should be the field you want to sort, and the second parameter controls the direction of sorting, which can be `asc` or `desc`

```php
$users = Db::table('users')
    ->orderBy('name', 'desc')
    ->get();
```

### latest / oldest

The `latest` and `oldest` methods allow you to easily sort by date. It uses the `created_at` column as the sort by default. Of course, you can also pass custom column names:

```php
$user = Db::table('users')->latest()->first();
```

### inRandomOrder

The `inRandomOrder` method is used to randomly order the results. For example, you can use this method to find a random user.

```php
$randomUser = Db::table('users')->inRandomOrder()->first();
```

### groupBy / having

The `groupBy` and `having` methods can group results. The use of the `having` method is very similar to the `where` method:

```php
$users = Db::table('users')
    ->groupBy('account_id')
    ->having('account_id', '>', 100)
    ->get();
```

You can pass multiple arguments to the `groupBy` method:

```php
$users = Db::table('users')
    ->groupBy('first_name', 'status')
    ->having('account_id', '>', 100)
    ->get();
```

> For more advanced having syntax, see havingRaw method.

### skip / take

To limit the number of results returned, or to skip a specified number of results, you can use the `skip` and `take` methods:

```php
$users = Db::table('users')->skip(10)->take(5)->get();
```

Or you can also use the limit and offset methods:

```php
$users = Db::table('users')->offset(10)->limit(5)->get();
```

## Conditional statements

Sometimes you may want to execute a query only if the clause applies if a certain condition is true. For example, you might only want to apply a `where` statement if a given value exists in the request. You can do this by using the `when` method:

```php
$role = $request->input('role');

$users = Db::table('users')
    ->when($role, function ($query, $role) {
        return $query->where('role_id', $role);
    })
    ->get();
```

The `when` method executes the given closure only if the first argument is `true`. If the first argument is `false` , then the closure will not be executed

You can pass another closure as the third parameter of the `when` method. The closure will be executed if the first argument is `false`. To illustrate how to use this feature, let's configure the default ordering of a query:

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

The query builder also provides the `insert` method for inserting records into the database. The `insert` method accepts an array of field names and field values for insertion:

```php
Db::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

You can even pass an array to the `insert` method to insert multiple records into the table

```php
Db::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);
```

### Auto Increment ID

If the table has an auto-incrementing `ID`, use the `insertGetId` method to insert the record and return the `ID` value

```php
$id = Db::table('users')->insertGetId(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

## Update

Of course, in addition to inserting records into the database, the query builder can also update existing records via the `update` method. The `update` method, like the `insert` method, accepts an array containing the fields and values to update. You can constrain the `update` query with the `where` clause:

```php
Db::table('users')->where('id', 1)->update(['votes' => 1]);
```

### Update or Insert

Sometimes you may wish to update an existing record in the database, or create a matching record if it does not exist. In this case, the `updateOrInsert` method can be used. The `updateOrInsert` method accepts two parameters: an array of conditions to find the record, and an array of key-value pairs containing the record to update.

The `updateOrInsert` method will first try to find a matching database record using the key and value pair of the first argument. If the record exists, use the value in the second parameter to update the record. If the record is not found, a new record is inserted, and the updated data is a collection of two arrays:

```php
Db::table('users')->updateOrInsert(
    ['email' => 'john@example.com', 'name' => 'John'],
    ['votes' => '2']
);
```

### Update JSON fields

When updating a JSON field, you can use the -> syntax to access the corresponding value in the JSON object, which is only supported on MySQL 5.7+:

```php
Db::table('users')->where('id', 1)->update(['options->enabled' => true]);
```

### Auto increment and decrement

The query builder also provides convenience methods for incrementing or decrementing a given field. This method provides a more expressive and concise interface than manually writing `update` statements.

Both methods receive at least one parameter: the column that needs to be modified. The second parameter is optional and controls the amount by which the column is incremented or decremented:

```php
Db::table('users')->increment('votes');

Db::table('users')->increment('votes', 5);

Db::table('users')->decrement('votes');

Db::table('users')->decrement('votes', 5);
```

You can also specify fields to update during the operation:

```php
Db::table('users')->increment('votes', 1, ['name' => 'John']);
```

## Delete

The query builder can also delete records from a table using the `delete` method. Before using `delete`, you can add a `where` clause to constrain the `delete` syntax:

```php
Db::table('users')->delete();

Db::table('users')->where('votes', '>', 100)->delete();
```

If you need to empty the table, you can use the `truncate` method, which will delete all rows and reset the auto-incrementing `ID` to zero:

```php
Db::table('users')->truncate();
```

## Pessimistic lock

The query builder also contains some functions that can help you implement `pessimistic locking` on the `select` syntax. To implement a `"shared lock"` in a query, you can use the `sharedLock` method. Shared locks prevent selected data columns from being tampered with until the transaction is committed

```php
Db::table('users')->where('votes', '>', 100)->sharedLock()->get();
```

Alternatively, you can use the `lockForUpdate` method. Use the `"update"` lock to prevent rows from being modified or selected by other shared locks:

```php
Db::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
```

