# Query paging

When using [hyperf/database](https://github.com/hyperf-cloud/database) to query data, it is convenient to pass [hyperf/paginator](https://github.com/hyperf-cloud The /paginator) component facilitates pagination of query results.

# Instructions

When you query data through [Query Constructor](en/db/querybuilder.md) or [Model](en/db/model.md), you can handle paging by the `paginate` method, which is automatically based on the user. The page being viewed to set limits and offsets. By default, the current page count is detected by the value of the `page` parameter with the current HTTP request:

> Since Hyperf does not currently support views, the paging component does not yet support rendering of views. Directly returning pagination results are output by default in application/json format.

## Query constructor paging

```php
<?php
// Show all users in the app, showing 10 data per page
return Db::table('users')->paginate(10);
```

## Model paging

You can make a pagination by calling the `paginate` method directly via a static method:

```php
<?php
// Show all users in the app, showing 10 data per page
return User::paginate(10);
```

Of course, you can also set the conditions of the query or the setting method of other queries:

```php
<?php 
// Show all users in the app, showing 10 data per page
return User::where('gender', 1)->paginate(10);
```

## Pager instance method

Only the use of the pager in database queries is explained here. More details on the pager can be found in the [Paging](en/paginator.md) section.