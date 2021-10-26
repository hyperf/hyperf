# 查询分页

在使用 [hyperf/database](https://github.com/hyperf-cloud/database) 来查询数据时，可以很方便的通过与 [hyperf/paginator](https://github.com/hyperf-cloud/paginator) 组件配合便捷地对查询结果进行分页。

# 使用方法

在您通过 [查询构造器](en/db/querybuilder.md) 或 [模型](en/db/model.md) 查询数据时，可以通过 `paginate` 方法来处理分页，该方法会自动根据用户正在查看的页面来设置限制和偏移量，默认情况下，通过当前 HTTP 请求所带的 `page` 参数的值来检测当前的页数：

> 由于 Hyperf 当前并不支持视图，所以分页组件尚未支持对视图的渲染，直接返回分页结果默认会以 application/json 格式输出。

## 查询构造器分页

```php
<?php
// 展示应用中的所有用户，每页显示 10 条数据
return Db::table('users')->paginate(10);
```

## 模型分页 

您可以直接通过静态方法调用 `paginate` 方法来进行分页：

```php
<?php
// 展示应用中的所有用户，每页显示 10 条数据
return User::paginate(10);
```

当然您也可以设置查询的条件或其它查询的设置方法：

```php
<?php 
// 展示应用中的所有用户，每页显示 10 条数据
return User::where('gender', 1)->paginate(10);
```

## 分页器实例方法

这里仅说明分页器在数据库查询上的使用方法，更多关于分页器的细节可阅读 [分页](en/paginator.md) 章节。