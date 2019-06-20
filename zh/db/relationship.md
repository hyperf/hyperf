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
        return $this->hasOne(Book::class, 'user_id', 'id');
    }
}
```

记住一点，`Hyperf` 将会自动确定 `Book` 模型的外键属性。按照约定，`Hyperf` 将会使用所属模型名称的 『snake case』形式，再加上 _id 后缀作为外键字段。因此，在上面这个例子中，`Hyperf` 将假定 `User` 对应到 `Book` 模型上的外键就是 book_id。

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