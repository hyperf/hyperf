# 查詢構造器

## 簡介

Hyperf 的資料庫查詢構造器為建立和執行資料庫查詢提供了一個方便的介面。它可用於執行應用程式中大部分資料庫操作，且可在所有支援的資料庫系統上執行。

Hyperf 的查詢構造器使用 PDO 引數繫結來保護您的應用程式免受 SQL 注入攻擊。因此沒有必要清理作為繫結傳遞的字串。

這裡只提供一部分常用的教程，具體教程可以到 Laravel 官網檢視。
[Laravel Query Builder](https://laravel.com/docs/5.8/queries)

## 獲取結果

```php
use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM user;');
$users = Db::table('user')->get();
$users = Db::table('user')->select('name', 'gender as user_gender')->get();
```

`Db::select()` 方法會返回一個 array，而 `get` 方法會返回 `Hyperf\Collection\Collection`。其中元素是 `stdClass`，所以可以透過以下程式碼返回各個元素的資料

```php
<?php

foreach ($users as $user) {
    echo $user->name;
}
```

### 將結果轉為陣列格式

在某些場景下，您可能會希望查詢出來的結果內採用 `陣列(Array)` 而不是 `stdClass` 物件結構時，而 `Eloquent` 又去除了透過配置的形式配置預設的 `FetchMode`，那麼此時可以透過監聽器來監聽 `Hyperf\Database\Events\StatementPrepared` 事件來變更該配置：

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

### 獲取一行的值

如果想獲取一行的值, 則可以使用 `first` 方法

```php
<?php
use Hyperf\DbConnection\Db;

$row = Db::table('user')->first(); // sql 會自動加上 limit 1
var_dump($row);
```

### 獲取單個值

如果想獲取單個值, 則可以使用 `value` 方法

```php
<?php
use Hyperf\DbConnection\Db;

$id = Db::table('user')->value('id');
var_dump($id);
```

### 獲取一列的值

如果你想獲取包含單列值的集合，則可以使用 `pluck` 方法。在下面的例子中，我們將獲取角色表中標題的集合：

```php
<?php
use Hyperf\DbConnection\Db;

$names = Db::table('user')->pluck('name');

foreach ($names as $name) {
    echo $name;
}

```

你還可以在返回的集合中指定欄位的自定義鍵值：

```php
<?php
use Hyperf\DbConnection\Db;

$roles = Db::table('roles')->pluck('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}

```

### 分塊結果

如果你需要處理上千條資料庫記錄，你可以考慮使用 `chunk` 方法。該方法一次獲取結果集的一小塊，並將其傳遞給 `閉包` 函式進行處理。該方法在 `Command` 編寫數千條處理資料的時候非常有用。例如，我們可以將全部 user 表資料切割成一次處理 100 條記錄的一小塊：

```php
<?php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        //
    }
});
```

你可以透過在 閉包 中返回 `false` 來終止繼續獲取分塊結果：

```php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {

    return false;
});
```

如果要在分塊結果時更新資料庫記錄，則塊結果可能會和預計的返回結果不一致。 因此，在分塊更新記錄時，最好使用 chunkById 方法。 此方法將根據記錄的主鍵自動對結果進行分頁：

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

> 在塊的回撥裡面更新或刪除記錄時，對主鍵或外來鍵的任何更改都可能影響塊查詢。 這可能會導致記錄沒有包含在分塊結果中。

### 聚合查詢

框架還提供了聚合類方法，例如 `count`, `max`, `min`, `avg`, `sum`。

```php
use Hyperf\DbConnection\Db;

$count = Db::table('user')->count();
```

#### 判斷記錄是否存在

除了透過 `count` 方法可以確定查詢條件的結果是否存在之外，還可以使用 `exists` 和 `doesntExist` 方法：

```php
return Db::table('orders')->where('finalized', 1)->exists();

return Db::table('orders')->where('finalized', 1)->doesntExist();
```

## 查詢

### 指定一個 Select 語句

當然你可能並不總是希望從資料庫表中獲取所有列。使用 select 方法，你可以自定義一個 select 查詢語句來查詢指定的欄位：

```php
$users = Db::table('user')->select('name', 'email as user_email')->get();
```

`distinct` 方法會強制讓查詢返回的結果不重複：

```php
$users = Db::table('user')->distinct()->get();
```

如果你已經有了一個查詢構造器例項，並且希望在現有的查詢語句中加入一個欄位，那麼你可以使用 addSelect 方法：

```php
$query = Db::table('users')->select('name');

$users = $query->addSelect('age')->get();
```

## 原始表示式

有時你需要在查詢中使用原始表示式，例如實現 `COUNT(0) AS count`，這就需要用到 `raw` 方法。

```php
use Hyperf\DbConnection\Db;

$res = Db::table('user')->select('gender', Db::raw('COUNT(0) AS `count`'))->groupBy('gender')->get();
```

### 強制索引

資料庫出現的慢查問題, 90% 以上是索引不對, 其中有部分查詢是因為資料庫伺服器的 `查詢最佳化器` 沒有使用最佳索引, 這時候就需要使用強制索引:

```php
Db::table(Db::raw("{$table} FORCE INDEX({$index})"));
```

### 原生方法

可以使用以下方法代替 `Db::raw`，將原生表示式插入查詢的各個部分。

`selectRaw` 方法可以代替 `select(Db::raw(...))`。該方法的第二個引數是可選項，值是一個繫結引數的陣列：

```php
$orders = Db::table('order')
    ->selectRaw('price * ? as price_with_tax', [1.0825])
    ->get();
```

`whereRaw` 和 `orWhereRaw` 方法將原生的 `where` 注入到你的查詢中。這兩個方法的第二個引數還是可選項，值還是繫結引數的陣列：

```php
$orders = Db::table('order')
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->get();
```

`havingRaw` 和 `orHavingRaw` 方法可以用於將原生字串設定為 `having` 語句的值：

```php
$orders = Db::table('order')
    ->select('department', Db::raw('SUM(price) as total_sales'))
    ->groupBy('department')
    ->havingRaw('SUM(price) > ?', [2500])
    ->get();
```

`orderByRaw` 方法可用於將原生字串設定為 `order by` 子句的值：

```php
$orders = Db::table('order')
    ->orderByRaw('updated_at - created_at DESC')
    ->get();
```

## 表連線

### Inner Join Clause

查詢構造器也可以編寫 `join` 方法。若要執行基本的`「內連結」`，你可以在查詢構造器例項上使用 `join` 方法。傳遞給 `join` 方法的第一個引數是你需要連線的表的名稱，而其他引數則使用指定連線的欄位約束。你還可以在單個查詢中連線多個數據表：

```php
$users = Db::table('users')
    ->join('contacts', 'users.id', '=', 'contacts.user_id')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.*', 'contacts.phone', 'orders.price')
    ->get();
```

### Left Join

如果你想使用`「左連線」`或者`「右連線」`代替`「內連線」`，可以使用 `leftJoin` 或者 `rightJoin` 方法。這兩個方法與 `join` 方法用法相同：

```php
$users = Db::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
$users = Db::table('users')
    ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

### Cross Join 語句

使用 `crossJoin` 方法和你想要連線的表名做`「交叉連線」`。交叉連線在第一個表和被連線的表之間會生成笛卡爾積：

```php
$users = Db::table('sizes')
    ->crossJoin('colours')
    ->get();
```

### 高階 Join 語句

你可以指定更高階的 `join` 語句。比如傳遞一個 `閉包` 作為 `join` 方法的第二個引數。此 `閉包` 接收一個 `JoinClause` 物件，從而指定 `join` 語句中指定的約束：

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')->orOn(...);
    })
    ->get();
```

如果你想要在連線上使用`「where」`風格的語句，你可以在連線上使用 `where` 和 `orWhere` 方法。這些方法會將列和值進行比較，而不是列和列進行比較：

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
                ->where('contacts.user_id', '>', 5);
    })
    ->get();
```

### 子連線查詢

你可以使用 `joinSub`，`leftJoinSub` 和 `rightJoinSub` 方法關聯一個查詢作為子查詢。他們每一種方法都會接收三個引數：子查詢，表別名和定義關聯欄位的閉包：

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

## 聯合查詢

查詢構造器還提供了將兩個查詢 「聯合」 的快捷方式。比如，你可以先建立一個查詢，然後使用 `union` 方法將其和第二個查詢進行聯合：

```php
$first = Db::table('users')->whereNull('first_name');

$users = Db::table('users')
    ->whereNull('last_name')
    ->union($first)
    ->get();
```

## Where 語句

### 簡單的 Where 語句

在構造 `where` 查詢例項的中，你可以使用 `where` 方法。呼叫 `where` 最基本的方式是需要傳遞三個引數：第一個引數是列名，第二個引數是任意一個數據庫系統支援的運算子，第三個是該列要比較的值。

例如，下面是一個要驗證  gender 欄位的值等於 1 的查詢：

```php
$users = Db::table('user')->where('gender', '=', 1)->get();
```

為了方便，如果你只是簡單比較列值和給定數值是否相等，可以將數值直接作為 `where` 方法的第二個引數：

```php
$users = Db::table('user')->where('gender', 1)->get();
```

當然，你也可以使用其他的運算子來編寫 where 子句：

```php
$users = Db::table('users')->where('gender', '>=', 0)->get();

$users = Db::table('users')->where('gender', '<>', 1)->get();

$users = Db::table('users')->where('name', 'like', 'T%')->get();
```

你還可以傳遞條件陣列到 where 函式中：

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
])->get();
```

你還可以使用閉包的方式建立查詢陣列

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
    [function ($query) {
        $query->where('type', 3)->orWhere('type', 6);
    }]
])->get();
```

### Or 語句

你可以一起鏈式呼叫 `where` 約束，也可以在查詢中新增 `or` 字句。 `orWhere` 方法和 `where` 方法接收的引數一樣：

```php
$users = Db::table('user')
    ->where('gender', 1)
    ->orWhere('name', 'John')
    ->get();
```

### 其他 Where 語句

#### whereBetween

`whereBetween` 方法驗證欄位值是否在給定的兩個值之間：

```php
$users = Db::table('users')->whereBetween('votes', [1, 100])->get();
```

#### whereNotBetween

`whereNotBetween` 方法驗證欄位值是否在給定的兩個值之外：

```php
$users = Db::table('users')->whereNotBetween('votes', [1, 100])->get();
```

#### whereIn / whereNotIn

`whereIn` 方法驗證欄位的值必須存在指定的數組裡:

```php
$users = Db::table('users')->whereIn('id', [1, 2, 3])->get();
```

`whereNotIn` 方法驗證欄位的值必須不存在於指定的數組裡:

```php
$users = Db::table('users')->whereNotIn('id', [1, 2, 3])->get();
```

### 引數分組

有時候你需要建立更高階的 `where` 子句，例如`「where exists」`或者巢狀的引數分組。查詢構造器也能夠處理這些。下面，讓我們看一個在括號中進行分組約束的例子:

```php
Db::table('users')->where('name', '=', 'John')
    ->where(function ($query) {
        $query->where('votes', '>', 100)
                ->orWhere('title', '=', 'Admin');
    })
    ->get();
```

你可以看到，透過一個 `Closure` 寫入 `where` 方法構建一個查詢構造器 來約束一個分組。這個 `Closure` 接收一個查詢例項，你可以使用這個例項來設定應該包含的約束。上面的例子將生成以下 SQL:

```sql
select * from users where name = 'John' and (votes > 100 or title = 'Admin')
```

> 你應該用 orWhere 呼叫這個分組，以避免應用全域性作用出現意外.

#### Where Exists 語句

`whereExists` 方法允許你使用 `where exists SQL` 語句。 `whereExists` 方法接收一個 `Closure` 引數，該 `whereExists` 方法接受一個 `Closure` 引數，該閉包獲取一個查詢構建器例項從而允許你定義放置在 `exists` 字句中查詢：

```php
Db::table('users')->whereExists(function ($query) {
    $query->select(Db::raw(1))
            ->from('orders')
            ->whereRaw('orders.user_id = users.id');
})
->get();
```

上述查詢將產生如下的 SQL 語句：

```sql
select * from users
where exists (
    select 1 from orders where orders.user_id = users.id
)
```

#### JSON Where 語句

`Hyperf` 也支援查詢 `JSON` 型別的欄位（僅在對 `JSON` 型別支援的資料庫上）。

```php
$users = Db::table('users')
    ->where('options->language', 'en')
    ->get();

$users = Db::table('users')
    ->where('preferences->dining->meal', 'salad')
    ->get();
```

你也可以使用 `whereJsonContains` 來查詢 `JSON` 陣列：

```php
$users = Db::table('users')
    ->whereJsonContains('options->languages', 'en')
    ->get();
```

你可以使用 `whereJsonLength` 來查詢 `JSON` 陣列的長度：

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

`orderBy` 方法允許你透過給定欄位對結果集進行排序。 `orderBy` 的第一個引數應該是你希望排序的欄位，第二個引數控制排序的方向，可以是 `asc` 或 `desc`

```php
$users = Db::table('users')
    ->orderBy('name', 'desc')
    ->get();
```

### latest / oldest

`latest` 和 `oldest` 方法可以使你輕鬆地透過日期排序。它預設使用 `created_at` 列作為排序依據。當然，你也可以傳遞自定義的列名：

```php
$user = Db::table('users')->latest()->first();
```

### inRandomOrder

`inRandomOrder` 方法被用來將結果隨機排序。例如，你可以使用此方法隨機找到一個使用者。

```php
$randomUser = Db::table('users')->inRandomOrder()->first();
```

### groupBy / having

`groupBy` 和 `having` 方法可以將結果分組。 `having` 方法的使用與 `where` 方法十分相似：

```php
$users = Db::table('users')
    ->groupBy('account_id')
    ->having('account_id', '>', 100)
    ->get();
```

你可以向 `groupBy` 方法傳遞多個引數：

```php
$users = Db::table('users')
    ->groupBy('first_name', 'status')
    ->having('account_id', '>', 100)
    ->get();
```

> 對於更高階的 having 語法，參見 havingRaw 方法。

### skip / take

要限制結果的返回數量，或跳過指定數量的結果，你可以使用 `skip` 和 `take` 方法：

```php
$users = Db::table('users')->skip(10)->take(5)->get();
```

或者你也可以使用 limit 和 offset 方法：

```php
$users = Db::table('users')->offset(10)->limit(5)->get();
```

## 條件語句

有時候你可能想要子句只適用於某個情況為真是才執行查詢。例如你可能只想給定值在請求中存在的情況下才應用 `where` 語句。 你可以透過使用 `when` 方法：

```php
$role = $request->input('role');

$users = Db::table('users')
    ->when($role, function ($query, $role) {
        return $query->where('role_id', $role);
    })
    ->get();
```

`when` 方法只有在第一個引數為 `true` 的時候才執行給的的閉包。如果第一個引數為 `false` ，那麼這個閉包將不會被執行

你可以傳遞另一個閉包作為 `when` 方法的第三個引數。 該閉包會在第一個引數為 `false` 的情況下執行。為了說明如何使用這個特性，我們來配置一個查詢的預設排序：

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

## 插入

查詢構造器還提供了 `insert` 方法用於插入記錄到資料庫中。 `insert` 方法接收陣列形式的欄位名和欄位值進行插入操作：

```php
Db::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

你甚至可以將陣列傳遞給 `insert` 方法，將多個記錄插入到表中

```php
Db::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);
```

### 自增 ID

如果資料表有自增 `ID` ，使用 `insertGetId` 方法來插入記錄並返回 `ID` 值

```php
$id = Db::table('users')->insertGetId(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

## 更新

當然， 除了插入記錄到資料庫中，查詢構造器也可以透過 `update` 方法更新已有的記錄。 `update` 方法和 `insert` 方法一樣，接受包含要更新的欄位及值的陣列。你可以透過 `where` 子句對 `update` 查詢進行約束：

```php
Db::table('users')->where('id', 1)->update(['votes' => 1]);
```

### 更新或者新增

有時您可能希望更新資料庫中的現有記錄，或者如果不存在匹配記錄則建立它。 在這種情況下，可以使用 `updateOrInsert` 方法。 `updateOrInsert` 方法接受兩個引數：一個用於查詢記錄的條件陣列，以及一個包含要更改記錄的鍵值對陣列。

`updateOrInsert` 方法將首先嚐試使用第一個引數的鍵和值對來查詢匹配的資料庫記錄。 如果記錄存在，則使用第二個引數中的值去更新記錄。 如果找不到記錄，將插入一個新記錄，更新的資料是兩個陣列的集合：

```php
Db::table('users')->updateOrInsert(
    ['email' => 'john@example.com', 'name' => 'John'],
    ['votes' => '2']
);
```

### 更新 JSON 欄位

更新 JSON 欄位時，你可以使用 -> 語法訪問 JSON 物件中相應的值，此操作只能支援 MySQL 5.7+：

```php
Db::table('users')->where('id', 1)->update(['options->enabled' => true]);
```

### 自增與自減

查詢構造器還為給定欄位的遞增或遞減提供了方便的方法。此方法提供了一個比手動編寫 `update` 語句更具表達力且更精練的介面。

這兩種方法都至少接收一個引數：需要修改的列。第二個引數是可選的，用於控制列遞增或遞減的量：

```php
Db::table('users')->increment('votes');

Db::table('users')->increment('votes', 5);

Db::table('users')->decrement('votes');

Db::table('users')->decrement('votes', 5);
```

你也可以在操作過程中指定要更新的欄位：

```php
Db::table('users')->increment('votes', 1, ['name' => 'John']);
```

## 刪除

查詢構造器也可以使用 `delete` 方法從表中刪除記錄。 在使用 `delete` 前，可以新增 `where` 子句來約束 `delete` 語法：

```php
Db::table('users')->delete();

Db::table('users')->where('votes', '>', 100)->delete();
```

如果你需要清空表，你可以使用 `truncate` 方法，它將刪除所有行，並重置自增 `ID` 為零：

```php
Db::table('users')->truncate();
```

## 悲觀鎖

查詢構造器也包含一些可以幫助你在 `select` 語法上實現`「悲觀鎖定」`的函式。若想在查詢中實現一個`「共享鎖」`， 你可以使用 `sharedLock` 方法。 共享鎖可防止選中的資料列被篡改，直到事務被提交為止

```php
Db::table('users')->where('votes', '>', 100)->sharedLock()->get();
```

或者，你可以使用 `lockForUpdate` 方法。使用`「update」`鎖可避免行被其它共享鎖修改或選取：

```php
Db::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
```

