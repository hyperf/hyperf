# 模型关联

## 定义关联

关联在 `Hyperf` 模型类中以方法的形式呈现。如同 `Hyperf` 模型本身，关联也可以作为强大的 `查询语句构造器` 使用，提供了强大的链式调用和查询功能。例如，我们可以在 role 关联的链式调用中附加一个约束条件：

```php
$user->role()->where('level', 1)->get();
```

### 一对一

一对一是最基本的关联关系。例如，一个 `User` 模型可能关联一个 `Role` 模型。为了定义这个关联，我们要在 `User` 模型中写一个 `role` 方法。在 `role` 方法内部调用 `hasOne` 方法并返回其结果:

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

`hasOne` 方法的第一个参数是关联模型的类名。一旦定义了模型关联，我们就可以使用 `Hyperf` 动态属性获得相关的记录。动态属性允许你访问关系方法就像访问模型中定义的属性一样：

```php
$role = User::query()->find(1)->role;
```

### 一对多

『一对多』关联用于定义单个模型拥有任意数量的其它关联模型。例如，一个作者可能写有多本书。正如其它所有的 `Hyperf` 关联一样，一对多关联的定义也是在 `Hyperf` 模型中写一个方法：

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

记住一点，`Hyperf` 将会自动确定 `Book` 模型的外键属性。按照约定，`Hyperf` 将会使用所属模型名称的 『snake case』形式，再加上 `_id` 后缀作为外键字段。因此，在上面这个例子中，`Hyperf` 将假定 `User` 对应到 `Book` 模型上的外键就是 `user_id`。

一旦关系被定义好以后，就可以通过访问 `User` 模型的 `books` 属性来获取评论的集合。记住，由于 Hyperf 提供了『动态属性』 ，所以我们可以像访问模型的属性一样访问关联方法：

```php
$books = User::query()->find(1)->books;

foreach ($books as $book) {
    //
}
```

当然，由于所有的关联还可以作为查询语句构造器使用，因此你可以使用链式调用的方式，在 books 方法上添加额外的约束条件：

```php
$book = User::query()->find(1)->books()->where('title', '一个月精通Hyperf框架')->first();
```

### 一对多（反向）

现在，我们已经能获得一个作者的所有作品，接着再定义一个通过书获得其作者的关联关系。这个关联是 `hasMany` 关联的反向关联，需要在子级模型中使用 `belongsTo` 方法定义它：

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

这个关系定义好以后，我们就可以通过访问 `Book` 模型的 author 这个『动态属性』来获取关联的 `User` 模型了：

```php
$book = Book::find(1);

echo $book->author->name;
```

### 多对多

多对多关联比 `hasOne` 和 `hasMany` 关联稍微复杂些。举个例子，一个用户可以拥有很多种角色，同时这些角色也被其他用户共享。例如，许多用户可能都有 「管理员」 这个角色。要定义这种关联，需要三个数据库表： `users`，`roles` 和 `role_user`。`role_user` 表的命名是由关联的两个模型按照字母顺序来的，并且包含了 `user_id` 和 `role_id` 字段。

多对多关联通过调用 `belongsToMany` 这个内部方法返回的结果来定义，例如，我们在 `User` 模型中定义 `roles` 方法：

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

一旦关联关系被定义后，你可以通过 `roles` 动态属性获取用户角色：

```php
$user = User::query()->find(1);

foreach ($user->roles as $role) {
    //
}
```

当然，像其它所有关联模型一样，你可以使用 `roles` 方法，利用链式调用对查询语句添加约束条件：

```php
$roles = User::find(1)->roles()->orderBy('name')->get();
```

正如前面所提到的，为了确定关联连接表的表名，`Hyperf` 会按照字母顺序连接两个关联模型的名字。当然，你也可以不使用这种约定，传递第二个参数到 belongsToMany 方法即可：

```php
return $this->belongsToMany(Role::class, 'role_user');
```

除了自定义连接表的表名，你还可以通过传递额外的参数到 `belongsToMany` 方法来定义该表中字段的键名。第三个参数是定义此关联的模型在连接表里的外键名，第四个参数是另一个模型在连接表里的外键名：

```php
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

#### 获取中间表字段

就如你刚才所了解的一样，多对多的关联关系需要一个中间表来提供支持， `Hyperf` 提供了一些有用的方法来和这张表进行交互。例如，假设我们的 `User` 对象关联了多个 `Role` 对象。在获得这些关联对象后，可以使用模型的 `pivot` 属性访问中间表的数据：

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

需要注意的是，我们获取的每个 `Role` 模型对象，都会被自动赋予 `pivot` 属性，它代表中间表的一个模型对象，并且可以像其他的 `Hyperf` 模型一样使用。

默认情况下，`pivot` 对象只包含两个关联模型的主键，如果你的中间表里还有其他额外字段，你必须在定义关联时明确指出：

```php
return $this->belongsToMany(Role::class)->withPivot('column1', 'column2');
```

如果你想让中间表自动维护 `created_at` 和 `updated_at` 时间戳，那么在定义关联时附加上 `withTimestamps` 方法即可：

```php
return $this->belongsToMany(Role::class)->withTimestamps();
```

#### 自定义 `pivot` 属性名称

如前所述，来自中间表的属性可以使用 `pivot` 属性访问。但是，你可以自由定制此属性的名称，以便更好的反应其在应用中的用途。

例如，如果你的应用中包含可能订阅的用户，则用户与博客之间可能存在多对多的关系。如果是这种情况，你可能希望将中间表访问器命名为 `subscription` 取代 `pivot` 。这可以在定义关系时使用 `as` 方法完成：

```php
return $this->belongsToMany(Podcast::class)->as('subscription')->withTimestamps();
```

一旦定义完成，你可以使用自定义名称访问中间表数据：

```php
$users = User::with('podcasts')->get();

foreach ($users->flatMap->podcasts as $podcast) {
    echo $podcast->subscription->created_at;
}
```

#### 通过中间表过滤关系

在定义关系时，你还可以使用 `wherePivot` 和 `wherePivotIn` 方法来过滤 `belongsToMany` 返回的结果：

```php
return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);
```

## 预加载

当以属性方式访问 `Hyperf` 关联时，关联数据「懒加载」。这着直到第一次访问属性时关联数据才会被真实加载。不过 `Hyperf` 能在查询父模型时「预先载入」子关联。预加载可以缓解 N + 1 查询问题。为了说明 N + 1 查询问题，考虑 `User` 模型关联到 `Role` 的情形：

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

现在，我们来获取所有的用户及其对应角色

```php
$users = User::query()->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

此循环将执行一个查询，用于获取全部用户，然后为每个用户执行获取角色的查询。如果我们有 10 个人，此循环将运行 11 个查询：1 个用于查询用户，10 个附加查询对应的角色。

谢天谢地，我们能够使用预加载将操作压缩到只有 2 个查询。在查询时，可以使用 with 方法指定想要预加载的关联：

```php
$users = User::query()->with('role')->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

在这个例子中，仅执行了两个查询

```
SELECT * FROM `user`;

SELECT * FROM `role` WHERE id in (1, 2, 3, ...);
```

## 多态关联

多态关联允许目标模型借助关联关系，关联多个模型。

### 一对一（多态）

#### 表结构

一对一多态关联与简单的一对一关联类似；不过，目标模型能够在一个关联上从属于多个模型。
例如，Book 和 User 可能共享一个关联到 Image 模型的关系。使用一对一多态关联允许使用一个唯一图片列表同时用于 Book 和 User。让我们先看看表结构：

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

image 表中的 imageable_id 字段会根据 imageable_type 的不同代表不同含义，默认情况下，imageable_type 直接是相关模型类名。

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

#### 获取关联

按照上述定义模型后，我们就可以通过模型关系获取对应的模型。

比如，我们获取某用户的图片。

```php
use App\Model\User;

$user = User::find(1);

$image = $user->image;
```

或者我们获取某个图片对应用户或书本。`imageable` 会根据 `imageable_type` 获取对应的 `User` 或者 `Book`。

```php
use App\Model\Image;

$image = Image::find(1);

$imageable = $image->imageable;
```

### 一对多（多态）

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

#### 获取关联

获取用户所有的图片

```php
use App\Model\User;

$user = User::query()->find(1);
foreach ($user->images as $image) {
    // ...
}
```

### 自定义多态映射

默认情况下，框架要求 `type` 必须存储对应模型类名，比如上述 `imageable_type` 必须是对应的 `User::class` 和 `Book::class`，但显然在实际应用中，这是十分不方便的。所以我们可以自定义映射关系，来解耦数据库与应用内部结构。

```php
use App\Model;
use Hyperf\Database\Model\Relations\Relation;
Relation::morphMap([
    'user' => Model\User::class,
    'book' => Model\Book::class,
]);
```

因为 `Relation::morphMap` 修改后会常驻内存，所以我们可以在项目启动时，就创建好对应的关系映射。我们可以创建以下监听器：

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

### 嵌套预加载 `morphTo` 关联

如果你希望加载一个 `morphTo` 关系，以及该关系可能返回的各种实体的嵌套关系，可以将 `with` 方法与 `morphTo` 关系的 `morphWith` 方法结合使用。

比如我们打算预加载 image 的 book.user 的关系。

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

对应的 SQL 查询如下：

```sql
// 查询所有图片
select * from `images`;
// 查询图片对应的用户列表
select * from `user` where `user`.`id` in (1, 2);
// 查询图片对应的书本列表
select * from `book` where `book`.`id` in (1, 2, 3);
// 查询书本列表对应的用户列表
select * from `user` where `user`.`id` in (1, 2);
```

### 多态关联查询

要查询 `MorphTo` 关联的存在，可以使用 `whereHasMorph` 方法及其相应的方法：

以下示例会查询，书本或用户 `ID` 为 1 的图片列表。

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
