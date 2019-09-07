# Model association

## Define association

The association is presented as a method in the `Hyperf` model class. As with the `Hyperf` model itself, associations can also be used as powerful `query constructors`, providing powerful chained calling and query capabilities. For example, we can attach a constraint to the chained call of the role association:

```php
$user->role()->where('level', 1)->get();
```

### One to one

One to one is the most basic relationship. For example, a `User` model might be associated with a `Role` model. To define this association, we need to write a `role` method in the `User` model. Call the `hasOne` method inside the `role` method and return its result:

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

The first argument to the `hasOne` method is the class name of the associated model. Once the model association is defined, we can use the `Hyperf` dynamic property to get the relevant records. Dynamic properties allow you to access relational methods just like accessing properties defined in the model:

```php
$role = User::query()->find(1)->role;
```

### One to many

A "one-to-many" association is used to define a single model with any number of other associated models. For example, an author may have written many books. As with all other `Hyperf` associations, the one-to-many association definition also writes a method in the `Hyperf` model:

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

Keep in mind that `Hyperf` will automatically determine the foreign key properties of the `Book` model. By convention, `Hyperf` will use the "snake case" form of the model name, plus the `_id` suffix as the foreign key field. Therefore, in the above example, `Hyperf` will assume that the foreign key corresponding to `User` to the `Book` model is `book_id`.

Once the relationship is defined, you can get a collection of comments by accessing the `books` property of the `User` model. Keep in mind that since Hyperf provides "dynamic properties", we can access the association methods just like accessing the properties of the model:

```php
$books = User::query()->find(1)->books;

foreach ($books as $book) {
    //
}
```

Of course, since all associations can also be used as query constructors, you can add additional constraints on the books method using chained calls:

```php
$book = User::query()->find(1)->books()->where('title', 'One month proficient in the Hyperf framework')->first();
```

### One to many (reverse)

Now, we have been able to get all the works of an author, and then define a relationship that is obtained by the author of the book. This association is a reverse association of the `hasMany` association, which needs to be defined in the child model using the `belongsTo` method:

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

Once this relationship is defined, we can get the associated `User` model by accessing the author's "dynamic property" of the `Book` model:

```php
$book = Book::find(1);

echo $book->author->name;
```

### Many to many

Many-to-many associations are slightly more complicated than `hasOne` and `hasMany`. For example, a user can have many roles and these roles are shared by other users. For example, many users may have the role of "administrator". To define this association, you need three database tables: `users`, `roles` and `role_user`. The `role_user` table is named alphabetically by the two associated models and contains the `user_id` and `role_id` fields.

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

Once the association is defined, you can get the user role through the `roles` dynamic property:

```php
$user = User::query()->find(1);

foreach ($user->roles as $role) {
    //
}
```

Of course, like all other associated models, you can use the `roles` method to add constraints to the query using chained calls:

```php
$roles = User::find(1)->roles()->orderBy('name')->get();
```

As mentioned earlier, in order to determine the table name of the associated join table, `Hyperf` will connect the names of the two associated models in alphabetical order. Of course, you can also pass the second parameter to the belongsToMany method without using this convention:

```php
return $this->belongsToMany(Role::class, 'role_user');
```

In addition to customizing the table name of the join table, you can also define the key name of the field in the table by passing additional parameters to the `belongsToMany` method. The third parameter is the foreign key name of the model that defines this association in the join table, and the fourth parameter is the foreign key name of another model in the join table:

```php
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

#### Get the intermediate table field

As you just learned, many-to-many associations require an intermediate table to provide support, and `Hyperf` provides some useful ways to interact with this table. For example, suppose our `User` object is associated with multiple `Role` objects. After obtaining these associated objects, you can access the data of the intermediate table using the model's `pivot` property:

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

It should be noted that each `Role` model object we get is automatically assigned the `pivot` attribute, which represents a model object of the intermediate table, and can be used like other `Hyperf` models.

By default, the `pivot` object contains only the primary keys of the two associated models. If you have other extra fields in your intermediate table, you must explicitly state when defining the association:

```php
return $this->belongsToMany(Role::class)->withPivot('column1', 'column2');
```

If you want the intermediate table to automatically maintain the `created_at` and `updated_at` timestamps, then attach the `withTimestamps` method when defining the association:

```php
return $this->belongsToMany(Role::class)->withTimestamps();
```

#### Custom `pivot` attribute name

As mentioned earlier, properties from intermediate tables can be accessed using the `pivot` property. However, you are free to customize the name of this property to better reflect its use in the application.

For example, if your app contains users who might subscribe, there may be a many-to-many relationship between the user and the blog. If this is the case, you might want to name the intermediate table accessor `ʻsubscription` instead of `pivot` . This can be done using the `as` method when defining the relationship:

```php
return $this->belongsToMany(Podcast::class)->as('subscription')->withTimestamps();
```

Once the definition is complete, you can access the intermediate table data with a custom name:

```php
$users = User::with('podcasts')->get();

foreach ($users->flatMap->podcasts as $podcast) {
    echo $podcast->subscription->created_at;
}
```

#### Filter relationships through intermediate tables

When defining relationships, you can also use the `wherePivot` and `wherePivotIn` methods to filter the results returned by `belongsToMany`:

```php
return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);
```


## Preloading

When the `Hyperf` association is accessed as an attribute, the associated data is "lazy loaded". This will not be loaded until the first time the property is accessed. However, `Hyperf` can "preload" the child association when querying the parent model. Preloading can alleviate N + 1 query problems. To illustrate the N + 1 query problem, consider the case where the `User` model is associated with `Role`:

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

Now let's get all the users and their corresponding roles.

```php
$users = User::query()->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

This loop will execute a query that gets all the users and then performs a query to get the role for each user. If we have 10 people, this loop will run 11 queries: 1 for querying users and 10 for additional roles.

Thankfully, we were able to compress the operation to only 2 queries using preloading. When querying, you can use the with method to specify the association you want to preload:

```php
$users = User::query()->with('role')->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

In this example, only two queries were executed

```sql
SELECT * FROM `user`;

SELECT * FROM `role` WHERE id in (1, 2, 3, ...);
```

