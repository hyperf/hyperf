# Query Builder

## Introduction

Hyperf's database query builder provides a convenient interface for creating and running database queries. It can be used to perform most database operations in your application and works on all supported database systems.

Hyperf's query builder uses PDO parameter binding to protect your application from SQL injection attacks. Therefore, there is no need to sanitize the strings passed as bindings.

Here are only some commonly used tutorials. For specific tutorials, please visit the official Laravel website.
[Laravel Query Builder](https://laravel.com/docs/5.8/queries)

## Retrieving Results

```php
use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM user;');
$users = Db::table('user')->get();
$users = Db::table('user')->select('name', 'gender as user_gender')->get();
```

The `Db::select()` method will return an array, and the `get` method will return a `Hyperf\Collection\Collection`. The elements are `stdClass`, so you can access the data of each element using the following code:

```php
<?php

foreach ($users as $user) {
    echo $user->name;
}
```

### Converting Results to Array Format

In some scenarios, you may want the query results to use `Array` instead of `stdClass` object structure. Since `Eloquent` has removed the ability to configure the default `FetchMode` via configuration, you can listen to the `Hyperf\Database\Events\StatementPrepared` event to change this configuration:

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

### Retrieving a Single Row

If you want to get a single row, you can use the `first` method:

```php
<?php
use Hyperf\DbConnection\Db;

$row = Db::table('user')->first(); // SQL will automatically include limit 1
var_dump($row);
```

### Retrieving a Single Value

If you want to get a single value, you can use the `value` method:

```php
<?php
use Hyperf\DbConnection\Db;

$id = Db::table('user')->value('id');
var_dump($id);
```

### Retrieving a Column of Values

If you want to get a collection containing values of a single column, you can use the `pluck` method. In the example below, we will get a collection of titles from the roles table:

```php
<?php
use Hyperf\DbConnection\Db;

$names = Db::table('user')->pluck('name');

foreach ($names as $name) {
    echo $name;
}
```

You can also specify a custom key for the returned collection:

```php
<?php
use Hyperf\DbConnection\Db;

$roles = Db::table('roles')->pluck('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}
```

### Chunking Results

If you need to process thousands of database records, consider using the `chunk` method. This method retrieves a small chunk of the result set at a time and passes it to a `Closure` function for processing. This method is very useful when writing `Command` to process thousands of data. For example, we can split all data in the user table into chunks of 100 records:

```php
<?php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        //
    }
});
```

You can terminate further retrieval of chunked results by returning `false` in the `Closure`:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {

    return false;
});
```

If you update database records while chunking results, the chunked results may be inconsistent with the expected results. Therefore, when chunking records for updates, it is best to use the `chunkById` method. This method will automatically paginate the results based on the primary key of the records:

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

> When updating or deleting records in the chunk callback, any changes to the primary key or foreign key may affect the chunk query. This may result in records not being included in the chunked results.

### Aggregations

The framework also provides aggregate methods, such as `count`, `max`, `min`, `avg`, `sum`.

```php
use Hyperf\DbConnection\Db;

$count = Db::table('user')->count();
```

#### Determining If Records Exist

In addition to using the `count` method to determine whether the results of a query condition exist, you can also use `exists` and `doesntExist` methods:

```php
return Db::table('orders')->where('finalized', 1)->exists();

return Db::table('orders')->where('finalized', 1)->doesntExist();
```

## Queries

### Specifying a Select Statement

Of course, you may not always want to retrieve all columns from a database table. Using the `select` method, you can customize a `select` query statement to query specific fields:

```php
$users = Db::table('user')->select('name', 'email as user_email')->get();
```

The `distinct` method forces the query to return distinct results:

```php
$users = Db::table('user')->distinct()->get();
```

If you already have a query builder instance and want to add a field to the existing query statement, you can use the `addSelect` method:

```php
$query = Db::table('users')->select('name');

$users = $query->addSelect('age')->get();
```

## Raw Expressions

Sometimes you need to use raw expressions in queries, such as implementing `COUNT(0) AS count`. This requires the use of the `raw` method.

```php
use Hyperf\DbConnection\Db;

$res = Db::table('user')->select('gender', Db::raw('COUNT(0) AS `count`'))->groupBy('gender')->get();
```

### Forcing Indexes

More than 90% of database slow query problems are due to incorrect indexes. Some queries occur because the database server's `query optimizer` did not use the best index. In this case, you need to use a forced index:

```php
Db::table(Db::raw("{$table} FORCE INDEX({$index})"));
```

### Raw Methods

You can use the following methods instead of `Db::raw` to insert raw expressions into various parts of the query.

The `selectRaw` method can be used instead of `select(Db::raw(...))`. The second argument of this method is optional and is an array of binding parameters:

```php
$orders = Db::table('order')
    ->selectRaw('price * ? as price_with_tax', [1.0825])
    ->get();
```

The `whereRaw` and `orWhereRaw` methods inject raw `where` clauses into your query. The second argument of these two methods is also optional and is an array of binding parameters:

```php
$orders = Db::table('order')
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->get();
```

The `havingRaw` and `orHavingRaw` methods can be used to set raw strings as the value of the `having` statement:

```php
$orders = Db::table('order')
    ->select('department', Db::raw('SUM(price) as total_sales'))
    ->groupBy('department')
    ->havingRaw('SUM(price) > ?', [2500])
    ->get();
```

The `orderByRaw` method can be used to set raw strings as the value of the `order by` clause:

```php
$orders = Db::table('order')
    ->orderByRaw('updated_at - created_at DESC')
    ->get();
```

## Table Joins

### Inner Join Clause

The query builder can also write `join` methods. To perform a basic "inner join", you can use the `join` method on the query builder instance. The first argument passed to the `join` method is the name of the table you need to join, while other arguments specify the field constraints for the join. You can also join multiple tables in a single query:

```php
$users = Db::table('users')
    ->join('contacts', 'users.id', '=', 'contacts.user_id')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.*', 'contacts.phone', 'orders.price')
    ->get();
```

### Left Join

If you want to use "left join" or "right join" instead of "inner join", you can use `leftJoin` or `rightJoin` methods. These two methods are used in the same way as the `join` method:

```php
$users = Db::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
$users = Db::table('users')
    ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

### Cross Join Clause

Use the `crossJoin` method with the table name you want to join to perform a "cross join". Cross join generates a Cartesian product between the first table and the joined table:

```php
$users = Db::table('sizes')
    ->crossJoin('colours')
    ->get();
```

### Advanced Join Clauses

You can specify more advanced `join` statements. For example, pass a `Closure` as the second argument to the `join` method. This `Closure` receives a `JoinClause` object to specify constraints in the `join` statement:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')->orOn(...);
    })
    ->get();
```

If you want to use "where" style clauses on joins, you can use the `where` and `orWhere` methods on the join. These methods compare columns and values instead of comparing columns and columns:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
                ->where('contacts.user_id', '>', 5);
    })
    ->get();
```

### Subquery Joins

You can use the `joinSub`, `leftJoinSub`, and `rightJoinSub` methods to join a query as a subquery. Each of these methods receives three arguments: the subquery, the table alias, and a Closure that defines the joined fields:

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

## Unions

The query builder also provides a shortcut to "union" two queries. For example, you can first create a query and then use the `union` method to union it with a second query:

```php
$first = Db::table('users')->whereNull('first_name');

$users = Db::table('users')
    ->whereNull('last_name')
    ->union($first)
    ->get();
```

## Where Clauses

### Simple Where Clauses

When constructing a `where` query instance, you can use the `where` method. The most basic way to call `where` is to pass three arguments: the first argument is the column name, the second argument is any operator supported by the database system, and the third is the value to compare against that column.

For example, here is a query to verify that the value of the `gender` field is equal to 1:

```php
$users = Db::table('user')->where('gender', '=', 1)->get();
```

For convenience, if you are simply comparing column values for equality with a given value, you can pass the value directly as the second argument to the `where` method:

```php
$users = Db::table('user')->where('gender', 1)->get();
```

Of course, you can also use other operators to write `where` clauses:

```php
$users = Db::table('users')->where('gender', '>=', 0)->get();

$users = Db::table('users')->where('gender', '<>', 1)->get();

$users = Db::table('users')->where('name', 'like', 'T%')->get();
```

You can also pass an array of conditions to the `where` function:

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
])->get();
```

You can also use a Closure to create query arrays:

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
    [function ($query) {
        $query->where('type', 3)->orWhere('type', 6);
    }]
])->get();
```

### Or Clauses

You can chain `where` constraints together, or add `or` clauses to your query. The `orWhere` method receives the same arguments as the `where` method:

```php
$users = Db::table('user')
    ->where('gender', 1)
    ->orWhere('name', 'John')
    ->get();
```

### Other Where Clauses

#### whereBetween

The `whereBetween` method verifies that a field's value is between two given values:

```php
$users = Db::table('users')->whereBetween('votes', [1, 100])->get();
```

#### whereNotBetween

The `whereNotBetween` method verifies that a field's value lies outside of two given values:

```php
$users = Db::table('users')->whereNotBetween('votes', [1, 100])->get();
```

#### whereIn / whereNotIn

The `whereIn` method verifies that a field's value exists in a given array:

```php
$users = Db::table('users')->whereIn('id', [1, 2, 3])->get();
```

The `whereNotIn` method verifies that a field's value does not exist in a given array:

```php
$users = Db::table('users')->whereNotIn('id', [1, 2, 3])->get();
```

### Parameter Grouping

Sometimes you need to create more advanced `where` clauses, such as "where exists" or nested parameter groups. The query builder can also handle these. Below, let's look at an example of nested constraint grouping in parentheses:

```php
Db::table('users')->where('name', '=', 'John')
    ->where(function ($query) {
        $query->where('votes', '>', 100)
                ->orWhere('title', '=', 'Admin');
    })
    ->get();
```

As you can see, by passing a `Closure` into the `where` method, you construct a grouping constraint. The `Closure` receives a query builder instance, which you can use to set the constraints that should be included. The example above will generate the following SQL:

```sql
select * from users where name = 'John' and (votes > 100 or title = 'Admin')
```

> You should group these constraints with an `orWhere` call to avoid unexpected behavior when global scopes are applied.

#### Where Exists Clauses

The `whereExists` method allows you to write `where exists SQL` statements. The `whereExists` method receives a `Closure`, which receives a query builder instance, allowing you to define the query to be placed inside the `exists` clause:

```php
Db::table('users')->whereExists(function ($query) {
    $query->select(Db::raw(1))
            ->from('orders')
            ->whereRaw('orders.user_id = users.id');
})
->get();
```

The above query will generate the following SQL statement:

```sql
select * from users
where exists (
    select 1 from orders where orders.user_id = users.id
)
```

#### JSON Where Clauses

Hyperf also supports querying `JSON` field types (only on databases that support the `JSON` type).

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

The `orderBy` method allows you to sort the result set by a given field. The first argument to `orderBy` should be the field you wish to sort by, and the second argument controls the direction of the sort, which can be `asc` or `desc`:

```php
$users = Db::table('users')
    ->orderBy('name', 'desc')
    ->get();
```

### latest / oldest

The `latest` and `oldest` methods allow you to easily order results by date. By default, it uses `created_at` as the column to sort by. Of course, you can also pass a custom column name:

```php
$user = Db::table('users')->latest()->first();
```

### inRandomOrder

The `inRandomOrder` method can be used to sort results randomly. For example, you can use this method to randomly fetch a user:

```php
$randomUser = Db::table('users')->inRandomOrder()->first();
```

### groupBy / having

The `groupBy` and `having` methods can be used to group the results. The `having` method's usage is very similar to the `where` method:

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

> For more advanced `having` syntax, see the `havingRaw` method.

### skip / take

To limit the number of results returned or to skip a given number of results, you can use `skip` and `take` methods:

```php
$users = Db::table('users')->skip(10)->take(5)->get();
```

Alternatively, you can use `limit` and `offset` methods:

```php
$users = Db::table('users')->offset(10)->limit(5)->get();
```

## Conditional Clauses

Sometimes you may want clauses to apply only when something is true. For example, you may only want to apply a `where` statement if a given value is present in the request. You can do this by using the `when` method:

```php
$role = $request->input('role');

$users = Db::table('users')
    ->when($role, function ($query, $role) {
        return $query->where('role_id', $role);
    })
    ->get();
```

The `when` method only executes the given closure when the first argument is `true`. If the first argument is `false`, the closure will not be executed.

You can pass another closure as the third argument to the `when` method. This closure will execute if the first argument is `false`. To illustrate how to use this feature, let's configure default sorting for a query:

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

## Inserts

The query builder also provides an `insert` method for inserting records into the database. The `insert` method receives an array of field names and values to insert:

```php
Db::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

You can even pass an array of arrays to the `insert` method to insert multiple records into the table:

```php
Db::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);
```

### Auto-incrementing IDs

If the table has an auto-incrementing `ID`, use the `insertGetId` method to insert a record and return the `ID` value:

```php
$id = Db::table('users')->insertGetId(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

## Updates

In addition to inserting records into the database, the query builder can also update existing records using the `update` method. The `update` method, like the `insert` method, accepts an array of columns and values to update. You can constrain `update` queries using the `where` clause:

```php
Db::table('users')->where('id', 1)->update(['votes' => 1]);
```

### Update or Insert

Sometimes you may want to update an existing record in the database, or create it if no matching record exists. In this case, you can use the `updateOrInsert` method. The `updateOrInsert` method accepts two arguments: an array of conditions for finding the record, and an array of key-value pairs containing the changes to the record.

The `updateOrInsert` method will first attempt to find a matching database record using the first argument's key-value pairs. If the record exists, it will update the record with the values from the second argument. If the record cannot be found, a new record will be inserted with the data merged from both arrays:

```php
Db::table('users')->updateOrInsert(
    ['email' => 'john@example.com', 'name' => 'John'],
    ['votes' => '2']
);
```

### Updating JSON Fields

When updating `JSON` fields, you can use the `->` syntax to access the corresponding value in the `JSON` object. This operation is only supported on MySQL 5.7+:

```php
Db::table('users')->where('id', 1)->update(['options->enabled' => true]);
```

### Increments & Decrements

The query builder also provides convenient methods for incrementing or decrementing the value of a given column. This method provides a more expressive and concise interface than manually writing `update` statements.

Both methods accept at least one argument: the column to modify. A second argument is optional, used to control the amount by which the column should be incremented or decremented:

```php
Db::table('users')->increment('votes');

Db::table('users')->increment('votes', 5);

Db::table('users')->decrement('votes');

Db::table('users')->decrement('votes', 5);
```

You can also specify additional columns to update during the operation:

```php
Db::table('users')->increment('votes', 1, ['name' => 'John']);
```

## Deletes

The query builder can also use the `delete` method to delete records from the table. Before using `delete`, you can add `where` clauses to constrain the `delete` statement:

```php
Db::table('users')->delete();

Db::table('users')->where('votes', '>', 100)->delete();
```

If you need to truncate the table, you can use the `truncate` method, which will remove all rows and reset the auto-increment `ID` to zero:

```php
Db::table('users')->truncate();
```

## Pessimistic Locking

The query builder also includes a few functions to help you achieve "pessimistic locking" on your `select` statements. If you want to place a "shared lock" on your query, you can use the `sharedLock` method. A shared lock prevents the selected data rows from being modified until the transaction is committed:

```php
Db::table('users')->where('votes', '>', 100)->sharedLock()->get();
```

Alternatively, you can use the `lockForUpdate` method. Using an "update" lock prevents the rows from being modified or selected with another shared lock:

```php
Db::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
```
