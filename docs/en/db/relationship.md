# Model Relationships

## Defining Relationships

Relationships are presented as methods in `Hyperf` model classes. Like `Hyperf` models themselves, relationships can also be used as powerful `query builders`, providing powerful method chaining and query capabilities. For example, we can attach a constraint to the method chaining of the role relationship:

```php
$user->role()->where('level', 1)->get();
```

### One To One

A one-to-one relationship is the most basic association. For example, a `User` model might be associated with a `Role` model. To define this relationship, we need to write a `role` method in the `User` model. Inside the `role` method, call the `hasOne` method and return its result:

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

The first argument to the `hasOne` method is the class name of the associated model. Once the model relationship is defined, we can use the `Hyperf` dynamic attribute to get related records. Dynamic attributes allow you to access relationship methods as if they were properties defined on the model:

```php
$role = User::query()->find(1)->role;
```

### One To Many

A "one-to-many" relationship is used to define that a single model owns any number of other associated models. For example, an author might write many books. Just like all other `Hyperf` relationships, a one-to-many relationship is also defined by writing a method in the `Hyperf` model:

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

Remember, `Hyperf` will automatically determine the foreign key attribute for the `Book` model. By convention, `Hyperf` will use the "snake case" form of the owning model name, plus the `_id` suffix as the foreign key field. Therefore, in the example above, `Hyperf` will assume that the foreign key from `User` to the `Book` model is `user_id`.

Once the relationship is defined, you can get a collection of books by accessing the `books` property of the `User` model. Remember, since Hyperf provides "dynamic attributes", we can access relationship methods as if they were properties of the model:

```php
$books = User::query()->find(1)->books;

foreach ($books as $book) {
    //
}
```

Of course, since all relationships can also be used as query builders, you can use method chaining to add additional constraints to the `books` method:

```php
$book = User::query()->find(1)->books()->where('title', 'Mastering Hyperf Framework in One Month')->first();
```

### One To Many (Inverse)

Now that we can get all works by an author, let's define an association to get the author from the book. This relationship is the inverse of the `hasMany` relationship and needs to be defined using the `belongsTo` method in the child model:

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

Once this relationship is defined, we can retrieve the associated `User` model by accessing the `author` "dynamic attribute" of the `Book` model:

```php
$book = Book::find(1);

echo $book->author->name;
```

### Many To Many

Many-to-many relationships are slightly more complex than `hasOne` and `hasMany` relationships. For example, a user can have many roles, and these roles are also shared by other users. For example, many users may have the "Administrator" role. To define this relationship, three database tables are required: `users`, `roles`, and `role_user`. The naming of the `role_user` table is based on the two associated models in alphabetical order, and contains `user_id` and `role_id` fields.

A many-to-many relationship is defined by the result returned by calling the internal `belongsToMany` method. For example, we define the `roles` method in the `User` model:

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

Once the relationship is defined, you can get the user roles via the `roles` dynamic attribute:

```php
$user = User::query()->find(1);

foreach ($user->roles as $role) {
    //
}
```

Of course, like all other associated models, you can use the `roles` method and use method chaining to add constraints to the query statement:

```php
$roles = User::find(1)->roles()->orderBy('name')->get();
```

As mentioned earlier, to determine the table name for the relationship's join table, `Hyperf` will join the names of the two associated models in alphabetical order. Of course, you can also not use this convention by passing a second argument to the `belongsToMany` method:

```php
return $this->belongsToMany(Role::class, 'role_user');
```

In addition to customizing the name of the join table, you can also define the key names of fields in that table by passing additional arguments to the `belongsToMany` method. The third argument is the foreign key name of the model defining the relationship in the join table, and the fourth argument is the foreign key name of the other model in the join table:

```php
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

#### Accessing Intermediate Table Fields

As you just learned, a many-to-many relationship requires an intermediate table for support. `Hyperf` provides some useful methods to interact with this table. For example, assume our `User` object is associated with many `Role` objects. After retrieving these associated objects, you can access the intermediate table data using the `pivot` attribute of the model:

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

It should be noted that each `Role` model object we retrieve is automatically assigned a `pivot` attribute, which represents a model object of the intermediate table and can be used like other `Hyperf` models.

By default, the `pivot` object only contains the primary keys of the two associated models. If your intermediate table has other extra fields, you must explicitly specify them when defining the relationship:

```php
return $this->belongsToMany(Role::class)->withPivot('column1', 'column2');
```

If you want the intermediate table to automatically maintain `created_at` and `updated_at` timestamps, simply attach the `withTimestamps` method when defining the relationship:

```php
return $this->belongsToMany(Role::class)->withTimestamps();
```

#### Customizing `pivot` Attribute Name

As previously mentioned, attributes from the intermediate table can be accessed using the `pivot` attribute. However, you are free to customize the name of this attribute to better reflect its purpose in the application.

For example, if your application contains users who might subscribe, there might be a many-to-many relationship between users and podcasts. If this is the case, you might want to rename the intermediate table accessor from `pivot` to `subscription`. This can be accomplished by using the `as` method when defining the relationship:

```php
return $this->belongsToMany(Podcast::class)->as('subscription')->withTimestamps();
```

Once defined, you can access the intermediate table data using the custom name:

```php
$users = User::with('podcasts')->get();

foreach ($users->flatMap->podcasts as $podcast) {
    echo $podcast->subscription->created_at;
}
```

#### Filtering Relationships via Intermediate Table

When defining relationships, you can also use `wherePivot` and `wherePivotIn` methods to filter the results returned by `belongsToMany`:

```php
return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);
```

## Eager Loading

When accessing `Hyperf` relationships as properties, the relationship data is "lazy loaded". This means the relationship data is not actually loaded until the first time the property is accessed. However, `Hyperf` can "eager load" child relationships when querying the parent model. Eager loading can alleviate the N + 1 query problem. To illustrate the N + 1 query problem, consider the case where the `User` model is associated with `Role`:

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

Now, let's fetch all users and their corresponding roles:

```php
$users = User::query()->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

This loop will execute one query to fetch all users, and then execute a query for each user to fetch their role. If we have 10 people, this loop will run 11 queries: 1 for querying users, and 10 additional queries corresponding to their roles.

Fortunately, we can use eager loading to compress the operation into only 2 queries. When querying, you can use the `with` method to specify the relationships you want to eager load:

```php
$users = User::query()->with('role')->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

In this example, only two queries were executed:

```
SELECT * FROM `user`;

SELECT * FROM `role` WHERE id in (1, 2, 3, ...);
```

## Polymorphic Relationships

Polymorphic relationships allow a target model to belong to more than one other type of model using a single association.

### One To One (Polymorphic)

#### Table Structure

A one-to-one polymorphic relationship is similar to a simple one-to-one relationship; however, the target model can belong to more than one type of model on a single association.
For example, a `Book` and a `User` may both share a relationship with an `Image` model. Using a one-to-one polymorphic relationship allows a single list of images to be used for both `Book` and `User`. Let's first look at the table structure:

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

The `imageable_id` field in the `image` table represents different meanings according to different `imageable_type`. By default, `imageable_type` is directly the associated model class name.

#### Model Example

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

#### Retrieving Relationships

After defining the models as described above, we can retrieve the corresponding models through model relationships.

For example, we retrieve the image of a certain user.

```php
use App\Model\User;

$user = User::find(1);

$image = $user->image;
```

Or we retrieve the user or book corresponding to a certain image. `imageable` will retrieve the corresponding `User` or `Book` based on `imageable_type`.

```php
use App\Model\Image;

$image = Image::find(1);

$imageable = $image->imageable;
```

### One To Many (Polymorphic)

#### Model Example

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

#### Retrieving Relationships

Retrieve all images of a user:

```php
use App\Model\User;

$user = User::query()->find(1);
foreach ($user->images as $image) {
    // ...
}
```

### Custom Polymorphic Mapping

By default, the framework requires `type` to store the corresponding model class name, for example, the aforementioned `imageable_type` must be the corresponding `User::class` and `Book::class`, but obviously, this is very inconvenient in practical applications. Therefore, we can customize the mapping relationship to decouple the database from the application's internal structure.

```php
use App\Model;
use Hyperf\Database\Model\Relations\Relation;
Relation::morphMap([
    'user' => Model\User::class,
    'book' => Model\Book::class,
]);
```

Because `Relation::morphMap` will remain resident in memory after modification, we can create the corresponding relationship mapping when the project starts. We can create the following listener:

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

### Nested Eager Loading `morphTo` Relationships

If you wish to load a `morphTo` relationship, and the nested relationships of various entities that the relationship might return, you can combine the `with` method with the `morphWith` method of the `morphTo` relationship.

For example, we intend to eager load the `book.user` relationship of an image.

```php

use App\Model\Book;
use App\Model\Image;
use Hyperf\Database\Model\Relations\MorphTo;

$images = Image::query()->with([
    'imageable' => function (MorphTo $morphTo) {
        $morphWith->morphWith([
            Book::class => ['user'],
        ]);
    },
])->get();
```

The corresponding SQL query is as follows:

```sql
// Query all images
select * from `images`;
// Query the list of users corresponding to images
select * from `user` where `user`.`id` in (1, 2);
// Query the list of books corresponding to images
select * from `book` where `book`.`id` in (1, 2, 3);
// Query the list of users corresponding to book list
select * from `user` where `user`.`id` in (1, 2);
```

### Polymorphic Relationship Query

To query the existence of a `MorphTo` relationship, you can use the `whereHasMorph` method and its corresponding methods:

The following example will query a list of images where the book or user `ID` is 1.

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
