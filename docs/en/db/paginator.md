# Query pagination

When using [hyperf/database](https://github.com/hyperf/database) to query data, it is very convenient to use [hyperf/paginator](https://github.com/hyperf/paginator) component to easily paginate query results.

# Instructions

When you query data through [Query Builder](en/db/querybuilder.md) or [Model](en/db/model.md), pagination can be handled through the `paginate` method, which automatically The page being viewed is used to set the limit and offset. By default, the current number of pages is detected by the value of the `page` parameter carried by the current HTTP request:

> Since Hyperf does not currently support views, the paging component does not yet support rendering of views, and the paging results returned directly will be output in application/json format by default.

## query builder pagination

```php
<?php
// Show all users in the app, 10 pieces of data per page
return Db::table('users')->paginate(10);
```

## Model pagination

You can do pagination by calling the `paginate` method directly from a static method:

```php
<?php
// Show all users in the app, 10 pieces of data per page
return User::paginate(10);
```

You can also set the query conditions or other query settings:

```php
<?php 
// Show all users in the app, 10 pieces of data per page
return User::where('gender', 1)->paginate(10);
```

## Paginator instance methods

Only the usage of the paginator in database queries is described here. For more details about the paginator, please read the [Pagination](en/paginator.md) chapter.
