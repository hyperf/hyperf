# 模型關聯

## 定義關聯

關聯在 `Hyperf` 模型類中以方法的形式呈現。如同 `Hyperf` 模型本身，關聯也可以作為強大的 `查詢語句構造器` 使用，提供了強大的鏈式調用和查詢功能。例如，我們可以在 role 關聯的鏈式調用中附加一個約束條件：

```php
$user->role()->where('level', 1)->get();
```

### 一對一

一對一是最基本的關聯關係。例如，一個 `User` 模型可能關聯一個 `Role` 模型。為了定義這個關聯，我們要在 `User` 模型中寫一個 `role` 方法。在 `role` 方法內部調用 `hasOne` 方法並返回其結果:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function role()
    {
        return $this->hasOne(Role::class, 'user_id', 'id');
    }
}
```

`hasOne` 方法的第一個參數是關聯模型的類名。一旦定義了模型關聯，我們就可以使用 `Hyperf` 動態屬性獲得相關的記錄。動態屬性允許你訪問關係方法就像訪問模型中定義的屬性一樣：

```php
$role = User::query()->find(1)->role;
```

### 一對多

『一對多』關聯用於定義單個模型擁有任意數量的其它關聯模型。例如，一個作者可能寫有多本書。正如其它所有的 `Hyperf` 關聯一樣，一對多關聯的定義也是在 `Hyperf` 模型中寫一個方法：

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function books()
    {
        return $this->hasMany(Book::class, 'user_id', 'id');
    }
}
```

記住一點，`Hyperf` 將會自動確定 `Book` 模型的外鍵屬性。按照約定，`Hyperf` 將會使用所屬模型名稱的 『snake case』形式，再加上 `_id` 後綴作為外鍵字段。因此，在上面這個例子中，`Hyperf` 將假定 `User` 對應到 `Book` 模型上的外鍵就是 `user_id`。

一旦關係被定義好以後，就可以通過訪問 `User` 模型的 `books` 屬性來獲取評論的集合。記住，由於 Hyperf 提供了『動態屬性』 ，所以我們可以像訪問模型的屬性一樣訪問關聯方法：

```php
$books = User::query()->find(1)->books;

foreach ($books as $book) {
    //
}
```

當然，由於所有的關聯還可以作為查詢語句構造器使用，因此你可以使用鏈式調用的方式，在 books 方法上添加額外的約束條件：

```php
$book = User::query()->find(1)->books()->where('title', '一個月精通Hyperf框架')->first();
```

### 一對多（反向）

現在，我們已經能獲得一個作者的所有作品，接着再定義一個通過書獲得其作者的關聯關係。這個關聯是 `hasMany` 關聯的反向關聯，需要在子級模型中使用 `belongsTo` 方法定義它：

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class Book extends Model
{
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
```

這個關係定義好以後，我們就可以通過訪問 `Book` 模型的 author 這個『動態屬性』來獲取關聯的 `User` 模型了：

```php
$book = Book::find(1);

echo $book->author->name;
```

### 多對多

多對多關聯比 `hasOne` 和 `hasMany` 關聯稍微複雜些。舉個例子，一個用户可以擁有很多種角色，同時這些角色也被其他用户共享。例如，許多用户可能都有 「管理員」 這個角色。要定義這種關聯，需要三個數據庫表： `users`，`roles` 和 `role_user`。`role_user` 表的命名是由關聯的兩個模型按照字母順序來的，並且包含了 `user_id` 和 `role_id` 字段。

多對多關聯通過調用 `belongsToMany` 這個內部方法返回的結果來定義，例如，我們在 `User` 模型中定義 `roles` 方法：

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
```

一旦關聯關係被定義後，你可以通過 `roles` 動態屬性獲取用户角色：

```php
$user = User::query()->find(1);

foreach ($user->roles as $role) {
    //
}
```

當然，像其它所有關聯模型一樣，你可以使用 `roles` 方法，利用鏈式調用對查詢語句添加約束條件：

```php
$roles = User::find(1)->roles()->orderBy('name')->get();
```

正如前面所提到的，為了確定關聯連接表的表名，`Hyperf` 會按照字母順序連接兩個關聯模型的名字。當然，你也可以不使用這種約定，傳遞第二個參數到 belongsToMany 方法即可：

```php
return $this->belongsToMany(Role::class, 'role_user');
```

除了自定義連接表的表名，你還可以通過傳遞額外的參數到 `belongsToMany` 方法來定義該表中字段的鍵名。第三個參數是定義此關聯的模型在連接表裏的外鍵名，第四個參數是另一個模型在連接表裏的外鍵名：

```php
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

#### 獲取中間表字段

就如你剛才所瞭解的一樣，多對多的關聯關係需要一箇中間表來提供支持， `Hyperf` 提供了一些有用的方法來和這張表進行交互。例如，假設我們的 `User` 對象關聯了多個 `Role` 對象。在獲得這些關聯對象後，可以使用模型的 `pivot` 屬性訪問中間表的數據：

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

需要注意的是，我們獲取的每個 `Role` 模型對象，都會被自動賦予 `pivot` 屬性，它代表中間表的一個模型對象，並且可以像其他的 `Hyperf` 模型一樣使用。

默認情況下，`pivot` 對象只包含兩個關聯模型的主鍵，如果你的中間表裏還有其他額外字段，你必須在定義關聯時明確指出：

```php
return $this->belongsToMany(Role::class)->withPivot('column1', 'column2');
```

如果你想讓中間表自動維護 `created_at` 和 `updated_at` 時間戳，那麼在定義關聯時附加上 `withTimestamps` 方法即可：

```php
return $this->belongsToMany(Role::class)->withTimestamps();
```

#### 自定義 `pivot` 屬性名稱

如前所述，來自中間表的屬性可以使用 `pivot` 屬性訪問。但是，你可以自由定製此屬性的名稱，以便更好的反應其在應用中的用途。

例如，如果你的應用中包含可能訂閲的用户，則用户與博客之間可能存在多對多的關係。如果是這種情況，你可能希望將中間表訪問器命名為 `subscription` 取代 `pivot` 。這可以在定義關係時使用 `as` 方法完成：

```php
return $this->belongsToMany(Podcast::class)->as('subscription')->withTimestamps();
```

一旦定義完成，你可以使用自定義名稱訪問中間表數據：

```php
$users = User::with('podcasts')->get();

foreach ($users->flatMap->podcasts as $podcast) {
    echo $podcast->subscription->created_at;
}
```

#### 通過中間表過濾關係

在定義關係時，你還可以使用 `wherePivot` 和 `wherePivotIn` 方法來過濾 `belongsToMany` 返回的結果：

```php
return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);
```

## 預加載

當以屬性方式訪問 `Hyperf` 關聯時，關聯數據「懶加載」。這着直到第一次訪問屬性時關聯數據才會被真實加載。不過 `Hyperf` 能在查詢父模型時「預先載入」子關聯。預加載可以緩解 N + 1 查詢問題。為了説明 N + 1 查詢問題，考慮 `User` 模型關聯到 `Role` 的情形：

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function role()
    {
        return $this->hasOne(Role::class, 'user_id', 'id');
    }
}
```

現在，我們來獲取所有的用户及其對應角色

```php
$users = User::query()->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

此循環將執行一個查詢，用於獲取全部用户，然後為每個用户執行獲取角色的查詢。如果我們有 10 個人，此循環將運行 11 個查詢：1 個用於查詢用户，10 個附加查詢對應的角色。

謝天謝地，我們能夠使用預加載將操作壓縮到只有 2 個查詢。在查詢時，可以使用 with 方法指定想要預加載的關聯：

```php
$users = User::query()->with('role')->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

在這個例子中，僅執行了兩個查詢

```
SELECT * FROM `user`;

SELECT * FROM `role` WHERE id in (1, 2, 3, ...);
```

## 多態關聯

多態關聯允許目標模型藉助關聯關係，關聯多個模型。

### 一對一（多態）

#### 表結構

一對一多態關聯與簡單的一對一關聯類似；不過，目標模型能夠在一個關聯上從屬於多個模型。
例如，Book 和 User 可能共享一個關聯到 Image 模型的關係。使用一對一多態關聯允許使用一個唯一圖片列表同時用於 Book 和 User。讓我們先看看錶結構：

```
book
  id - integer
  title - string

user 
  id - integer
  name - string

image
  id - integer
  url - string
  imageable_id - integer
  imageable_type - string
```

image 表中的 imageable_id 字段會根據 imageable_type 的不同代表不同含義，默認情況下，imageable_type 直接是相關模型類名。

#### 模型示例

```php
<?php
namespace App\Model;

class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }
}

class Book extends Model
{
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}

class User extends Model
{
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
```

#### 獲取關聯

按照上述定義模型後，我們就可以通過模型關係獲取對應的模型。

比如，我們獲取某用户的圖片。

```php
use App\Model\User;

$user = User::find(1);

$image = $user->image;
```

或者我們獲取某個圖片對應用户或書本。`imageable` 會根據 `imageable_type` 獲取對應的 `User` 或者 `Book`。

```php
use App\Model\Image;

$image = Image::find(1);

$imageable = $image->imageable;
```

### 一對多（多態）

#### 模型示例

```php
<?php
namespace App\Model;

class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }
}

class Book extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}

class User extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
```

#### 獲取關聯

獲取用户所有的圖片

```php
use App\Model\User;

$user = User::query()->find(1);
foreach ($user->images as $image) {
    // ...
}
```

### 自定義多態映射

默認情況下，框架要求 `type` 必須存儲對應模型類名，比如上述 `imageable_type` 必須是對應的 `User::class` 和 `Book::class`，但顯然在實際應用中，這是十分不方便的。所以我們可以自定義映射關係，來解耦數據庫與應用內部結構。

```php
use App\Model;
use Hyperf\Database\Model\Relations\Relation;
Relation::morphMap([
    'user' => Model\User::class,
    'book' => Model\Book::class,
]);
```

因為 `Relation::morphMap` 修改後會常駐內存，所以我們可以在項目啟動時，就創建好對應的關係映射。我們可以創建以下監聽器：

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Listener;

use App\Model;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

#[Listener]
class MorphMapRelationListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        Relation::morphMap([
            'user' => Model\User::class,
            'book' => Model\Book::class,
        ]);
    }
}

```

### 嵌套預加載 `morphTo` 關聯

如果你希望加載一個 `morphTo` 關係，以及該關係可能返回的各種實體的嵌套關係，可以將 `with` 方法與 `morphTo` 關係的 `morphWith` 方法結合使用。

比如我們打算預加載 image 的 book.user 的關係。

```php

use App\Model\Book;
use App\Model\Image;
use Hyperf\Database\Model\Relations\MorphTo;

$images = Image::query()->with([
    'imageable' => function (MorphTo $morphTo) {
        $morphTo->morphWith([
            Book::class => ['user'],
        ]);
    },
])->get();
```

對應的 SQL 查詢如下：

```sql
// 查詢所有圖片
select * from `images`;
// 查詢圖片對應的用户列表
select * from `user` where `user`.`id` in (1, 2);
// 查詢圖片對應的書本列表
select * from `book` where `book`.`id` in (1, 2, 3);
// 查詢書本列表對應的用户列表
select * from `user` where `user`.`id` in (1, 2);
```

### 多態關聯查詢

要查詢 `MorphTo` 關聯的存在，可以使用 `whereHasMorph` 方法及其相應的方法：

以下示例會查詢，書本或用户 `ID` 為 1 的圖片列表。

```php
use App\Model\Book;
use App\Model\Image;
use App\Model\User;
use Hyperf\Database\Model\Builder;

$images = Image::query()->whereHasMorph(
    'imageable',
    [
        User::class,
        Book::class,
    ],
    function (Builder $query) {
        $query->where('imageable_id', 1);
    }
)->get();
```
