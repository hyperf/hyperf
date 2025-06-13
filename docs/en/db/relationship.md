# Model association

## Define association

Associations are presented as methods in the `Hyperf` model class. Like the `Hyperf` model itself, associations can also be used as powerful `query builder`, providing powerful chaining and querying capabilities. For example, we can attach a constraint to the chained calls associated with role:

```php
$user->role()->where('level', 1)->get();
```

### One to one

One-to-one is the most basic relationship. For example, a `User` model might be associated with a `Role` model. To define this association, we need to write a `role` method in the `User` model. Call the `hasOne` method inside the `role` method and return its result:

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

The first parameter of the `hasOne` method is the class name of the associated model. Once the model associations are defined, we can use the `Hyperf` dynamic properties to get the related records. Dynamic properties allow you to access relationship methods just like properties defined in the model:

```php
$role = User::query()->find(1)->role;
```

### One-to-many

A "one-to-many" association is used to define a single model with any number of other associated models. For example, an author may have written multiple books. As with all other `Hyperf` relationships, the definition of a one-to-many relationship is to write a method in the `Hyperf` model:

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

Remember that `Hyperf` will automatically determine the foreign key properties of the `Book` model. By convention, `Hyperf` will use the "snake case" form of the owning model name, plus the `_id` suffix as the foreign key field. Therefore, in the above example, `Hyperf` will assume that the foreign key corresponding to `User` on the `Book` model is `user_id`.

Once the relationship is defined, the collection of comments can be obtained by accessing the `books` property of the `User` model. Remember, since Hyperf provides "dynamic properties", we can access associated methods just like properties of the model:

```php
$books = User::query()->find(1)->books;

foreach ($books as $book) {
    //
}
```

Of course, since all associations can also be used as query constructors, you can use chained calls to add additional constraints to the books method:

```php
$book = User::query()->find(1)->books()->where('title', 'Mastering the Hyperf framework in one month')->first();
```

### One-to-many (reverse)

Now that we can get all the works of an author, let's define an association to get its author through the book. This association is the inverse of the `hasMany` association and needs to be defined in the child model using the `belongsTo` method:

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

After this relationship is defined, we can get the associated `User` model by accessing the "dynamic property" of the author of the `Book` model:

```php
$book = Book::find(1);

echo $book->author->name;
```

### many-to-many

Many-to-many associations are slightly more complicated than `hasOne` and `hasMany` associations. For example, a user can have many roles, and these roles are also shared by other users. For example, many users may have the role of "Administrator". To define this association, three database tables are required: `users`, `roles` and `role_user`. The `role_user` table is named alphabetically by the associated two models, and contains the `user_id` and `role_id` fields.

Many-to-many associations are defined by calling the result returned by the internal method `belongsToMany`. For example, we define the `roles` method in the `User` model:

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

Once the relationship is defined, you can get user roles via the `roles` dynamic property:

```php
$user = User::query()->find(1);

foreach ($user->roles as $role) {
    //
}
```

Of course, like all other relational models, you can use the `roles` method to add constraints to queries using chained calls:

```php
$roles = User::find(1)->roles()->orderBy('name')->get();
```

As mentioned earlier, in order to determine the table name of the relational join table, `Hyperf` will concatenate the names of the two relational models in alphabetical order. Of course, you can also skip this convention and pass the second parameter to the belongsToMany method:

```php
return $this->belongsToMany(Role::class, 'role_user');
```

In addition to customizing the table name of the join table, you can also define the key name of the field in the table by passing additional parameters to the `belongsToMany` method. The third parameter is the foreign key name of the model that defines this association in the join table, and the fourth parameter is the foreign key name of another model in the join table:

```php
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

#### Get intermediate table fields

As you just learned, many-to-many relationships require an intermediate table to support, and `Hyperf` provides some useful methods to interact with this table. For example, let's say our `User` object has multiple `Role` objects associated with it. After obtaining these association objects, the data in the intermediate table can be accessed using the model's `pivot` attribute:

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

It should be noted that each `Role` model object we get is automatically assigned a `pivot` attribute, which represents a model object of the intermediate table and can be used like other `Hyperf` models.

By default, the `pivot` object contains only the primary keys of the two relational models. If you have additional fields in the intermediate table, you must specify them when defining the relation:

```php
return $this->belongsToMany(Role::class)->withPivot('column1', 'column2');
```

If you want the intermediate table to automatically maintain the `created_at` and `updated_at` timestamps, then add the `withTimestamps` method when defining the association:

```php
return $this->belongsToMany(Role::class)->withTimestamps();
```

#### custom `pivot` attribute name

As mentioned earlier, properties from intermediate tables can be accessed using the `pivot` attribute. However, you are free to customize the name of this property to better reflect its use in your application.

For example, if your app includes users who may subscribe, there may be a many-to-many relationship between users and blogs. If this is the case, you may wish to name the intermediate table accessor `subscription` instead of `pivot` . This can be done using the `as` method when defining the relationship:

```php
return $this->belongsToMany(Podcast::class)->as('subscription')->withTimestamps();
```

Once defined, you can access the intermediate table data with a custom name:

```php
$users = User::with('podcasts')->get();

foreach ($users->flatMap->podcasts as $podcast) {
    echo $podcast->subscription->created_at;
}
```

#### Filter relations by intermediate table

When defining a relationship, you can also use the `wherePivot` and `wherePivotIn` methods to filter the results returned by `belongsToMany`:

```php
return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);
```


## Preloading

When accessing a `Hyperf` relationship as an attribute, the associated data is "lazy loaded". This means that the associated data is not actually loaded until the property is accessed for the first time. However, `Hyperf` can "preload" child associations when querying the parent model. Eager loading can alleviate the N+1 query problem. To illustrate the N + 1 query problem, consider a `User` model associated with a `Role`:

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

Now, let's get all users and their corresponding roles

```php
$users = User::query()->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

This loop will execute a query to get all users, and then execute a query to get roles for each user. If we have 10 people, this loop will run 11 queries: 1 for users and 10 additional queries for roles.

Thankfully, we were able to squeeze the operation down to just 2 queries using eager loading. At query time, you can use the with method to specify which associations you want to preload:

```php
$users = User::query()->with('role')->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

In this example, only two queries are executed

```
SELECT * FROM `user`;

SELECT * FROM `role` WHERE id in (1, 2, 3, ...);
```

## Polymorphic association

Polymorphic association allows the target model to associate multiple models with the help of association relationships.

### One-to-one (polymorphic)

#### Table Structure

A one-to-one polymorphic association is similar to a simple one-to-one association however, the target model can belong to multiple models on a single association.
For example, Book and User might share a relationship to the Image model. Using a one-to-one polymorphic association allows using a unique image list for both Book and User. Let's look at the table structure first:

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

The imageable_id field in the image table will have different meanings depending on the imageable_type. By default, imageable_type is directly the relevant model class name.

#### Model example

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

#### Get association

After defining the model as above, we can obtain the corresponding model through the model relationship.

For example, we get a picture of a user.

```php
use App\Model\User;

$user = User::find(1);

$image = $user->image;
```

Or we get a picture corresponding to a user or book. `imageable` will get the corresponding `User` or `Book` according to `imageable_type`.

```php
use App\Model\Image;

$image = Image::find(1);

$imageable = $image->imageable;
```

### One-to-many (polymorphic)

#### Model example

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

#### Get association

Get all pictures of user

```php
use App\Model\User;

$user = User::query()->find(1);
foreach ($user->images as $image) {
    // ...
}
```

### Custom polymorphic mapping

By default, the framework requires that `type` must store the corresponding model class name. For example, the above `imageable_type` must be the corresponding `User::class` and `Book::class`, but obviously in actual applications, this is very inconsistent. convenient. So we can customize the mapping relationship to decouple the database and the internal structure of the application.

```php
use App\Model;
use Hyperf\Database\Model\Relations\Relation;
Relation::morphMap([
    'user' => Model\User::class,
    'book' => Model\Book::class,
]);
```

Because `Relation::morphMap` will be resident in memory after modification, we can create the corresponding relationship mapping when the project starts. We can create the following listener:

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

### Nested preloading `morphTo` association

If you wish to load a `morphTo` relationship, along with nested relationships of various entities that the relationship may return, you can use the `with` method in conjunction with the `morphWith` method of the `morphTo` relationship.

For example, we plan to preload the relationship of book.user of image.

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

The corresponding SQL query is as follows:

```sql
// Search all pictures
select * from `images`;
// Query the user list corresponding to the image
select * from `user` where `user`.`id` in (1, 2);
// Query the list of books corresponding to the image
select * from `book` where `book`.`id` in (1, 2, 3);
// Query the user list corresponding to the book list
select * from `user` where `user`.`id` in (1, 2);
```

### Polymorphic relational query

To query for the existence of a `MorphTo` association, you can use the `whereHasMorph` method and its corresponding method:

The following example will query the list of images with the book or user ID 1.

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
