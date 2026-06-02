# Database Pagination

When using [hyperf/database](https://github.com/hyperf/database) to query data, you can conveniently paginate query results by using the [hyperf/paginator](https://github.com/hyperf/paginator) component.

# How to use

When you query data through the [Query Builder](/en/db/querybuilder.md) or [Model](/en/db/model.md), you can use the `paginate` method to process pagination. This method automatically sets limits and offsets based on the page the user is viewing. By default, the current page number is detected via the `page` parameter value in the current HTTP request:

> Since Hyperf does not currently support views, the pagination component does not yet support rendering for views. Directly returning pagination results will output in `application/json` format by default.

## Query Builder Pagination

```php
<?php
// Display all users in the application, showing 10 items per page
return Db::table('users')->paginate(10);
```

## Model Pagination

You can call the `paginate` method directly via a static method to perform pagination:

```php
<?php
// Display all users in the application, showing 10 items per page
return User::paginate(10);
```

Of course, you can also set query conditions or other query settings:

```php
<?php 
// Display all users in the application, showing 10 items per page
return User::where('gender', 1)->paginate(10);
```

## Paginator Instance Methods

This section only describes the usage of the paginator in database queries. For more details about the paginator, please read the [Pagination](/en/paginator.md) chapter.
