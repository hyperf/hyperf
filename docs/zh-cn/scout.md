# 模型全文检索

## 前言

> [hyperf/scout](https://github.com/hyperf/scout) 衍生于 [laravel/scout](https://github.com/laravel/scout)，我们对它进行了一些协程化改造，但保持了相同的 API。在这里感谢一下 Laravel 开发组，实现了如此强大好用的组件。本文档部分节选自 Laravel China 社区组织翻译的 Laravel 官方文档。

Hyperf/Scout 为模型的全文搜索提供了一个简单的、基于驱动程序的解决方案。使用模型观察员，Scout 会自动同步你的搜索索引和模型记录。

目前，Scout 自带了一个 Elasticsearch 驱动；而编写自定义驱动程序很简单，你可以自由地使用自己的搜索实现来扩展 Scout。

## 安装

### 引入组件包和 Elasticsearch 驱动

```bash
composer require hyperf/scout
composer require hyperf/elasticsearch
```

Scout 安装完成后，使用 vendor:publish 命令来生成 Scout 配置文件。这个命令将在你的 config 目录下生成一个 scout.php 配置文件。

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

最后，在你要做搜索的模型中添加 Hyperf\Scout\Searchable trait。这个 trait 会注册一个模型观察者来保持模型和所有驱动的同步：

```php
<?php

namespace App;

use Hyperf\Database\Model\Model;
use Hyperf\Scout\Searchable;

class Post extends Model
{
    use Searchable;
}
```
## 配置

### 配置文件

生成配置文件

```
php bin/hyperf.php vendor:publish hyperf/scout
```

配置文件

```php
<?php

declare(strict_types=1);

return [
    'default' => env('SCOUT_ENGINE', 'elasticsearch'),
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'prefix' => env('SCOUT_PREFIX', ''),
    'soft_delete' => false,
    'concurrency' => 100,
    'engine' => [
        'elasticsearch' => [
            'driver' => Hyperf\Scout\Provider\ElasticsearchProvider::class,
            // 如果 index 设置为 null，则每个模型会对应一个索引，反之每个模型对应一个类型
            'index' => null,
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'http://127.0.0.1:9200'),
            ],
        ],
    ],
];

```
### 配置模型索引

每个模型与给定的搜索「索引」同步，这个「索引」包含该模型的所有可搜索记录。换句话说，你可以把每一个「索引」设想为一张 MySQL 数据表。默认情况下，每个模型都会被持久化到与模型的「表」名（通常是模型名称的复数形式）相匹配的索引。你也可以通过覆盖模型上的 `searchableAs` 方法来自定义模型的索引：

    <?php

    namespace App;

    use Hyperf\Scout\Searchable;
    use Hyperf\Database\Model\Model;

    class Post extends Model
    {
        use Searchable;

        /**
         * Get the index name for the model.
         *
         * @return string
         */
        public function searchableAs()
        {
            return 'posts_index';
        }
    }

<a name="configuring-searchable-data"></a>

### 配置可搜索的数据

默认情况下，「索引」会从模型的 `toArray` 方法中读取数据来做持久化。如果要自定义同步到搜索索引的数据，可以覆盖模型上的 `toSearchableArray` 方法：

    <?php

    namespace App;

    use Hyperf\Scout\Searchable;
    use Hyperf\Database\Model\Model;

    class Post extends Model
    {
        use Searchable;

        /**
         * Get the indexable data array for the model.
         *
         * @return array
         */
        public function toSearchableArray()
        {
            $array = $this->toArray();

            // Customize array...

            return $array;
        }
    }

<a name="indexing"></a>
## 索引

<a name="batch-import"></a>
### 批量导入

如果你想要将 Scout 安装到现有的项目中，你可能已经有了想要导入搜索驱动的数据库记录。使用 Scout 提供的命令 `import` 把所有现有记录导入到搜索索引里：

    php bin/hyperf.php scout:import "App\Post"

<a name="adding-records"></a>
### 添加记录

当你将 Trait `Hyperf\Scout\Searchable` 添加到模型中，你需要做的是 `save` 一个模型实例，它就会自动添加到你的搜索索引。更新索引操作将会在协程结束时进行，不会堵塞请求。

    $order = new App\Order;

    // ...

    $order->save();

#### 批量添加

如果你想通过模型查询构造器将模型集合添加到搜索索引中，你也可以在模型查询构造器上链式调用 `searchable` 方法。`searchable` 会把构造器的查询结果分块并且将记录添加到你的搜索索引里。

    // 使用模型查询构造器增加...
    App\Order::where('price', '>', 100)->searchable();

    // 使用模型关系增加记录...
    $user->orders()->searchable();

    // 使用集合增加记录...
    $orders->searchable();

`searchable` 方法可以被看做是「更新插入」的操作。换句话说，如果模型记录已经在你的索引里了，它就会被更新。如果搜索索引中不存在，则将其添加到索引中。

<a name="updating-records"></a>
### 更新记录

要更新可搜索的模型，只需要更新模型实例的属性并将模型 `save` 到数据库。Scout 会自动将更新同步到你的搜索索引中：

    $order = App\Order::find(1);

    // 更新 order...

    $order->save();

你也可以在模型查询语句上使用 `searchable` 方法来更新一个模型的集合。如果这个模型不存在你检索的索引里，就会被创建：

    // 使用模型查询语句更新...
    App\Order::where('price', '>', 100)->searchable();

    // 你也可以使用模型关系更新...
    $user->orders()->searchable();

    // 你也可以使用集合更新...
    $orders->searchable();

<a name="removing-records"></a>
### 删除记录

简单地使用 `delete` 从数据库中删除该模型就可以移除索引里的记录。这种删除形式甚至与软删除的模型兼容:

    $order = App\Order::find(1);

    $order->delete();

如果你不想在删除记录之前检索模型，可以在模型查询实例或集合上使用 `unsearchable` 方法：

    // 通过模型查询删除...
    App\Order::where('price', '>', 100)->unsearchable();

    // 通过模型关系删除...
    $user->orders()->unsearchable();

    // 通过集合删除...
    $orders->unsearchable();

<a name="pausing-indexing"></a>
### 暂停索引

你可能需要在执行一批模型操作的时候，不同步模型数据到搜索索引。此时你可以使用协程安全的 `withoutSyncingToSearch` 方法来执行此操作。这个方法接受一个立即执行的回调。该回调中所有的操作都不会同步到模型的索引：

    App\Order::withoutSyncingToSearch(function () {
        // 执行模型动作...
    });

<a name="searching"></a>
## 搜索

你可以使用 `search` 方法来搜索模型。`search` 方法接受一个用于搜索模型的字符串。你还需在搜索查询上链式调用 `get` 方法，才能用给定的搜索语句查询与之匹配的模型模型：

    $orders = App\Order::search('Star Trek')->get();
Scout 搜索返回模型模型的集合，因此你可以直接从路由或控制器返回结果，它们会被自动转换成 JSON 格式：

    Route::get('/search', function () {
        return App\Order::search([])->get();
    });

如果你想在它们返回模型模型前得到原结果，你应该使用`raw` 方法:

    $orders = App\Order::search('Star Trek')->raw();

搜索查询通常会在模型的 [`searchableAs`](#configuring-model-indexes) 方法指定的索引上执行。当然，你也可以使用 `within` 方法指定应该搜索的自定义索引:

    $orders = App\Order::search('Star Trek')
        ->within('tv_shows_popularity_desc')
        ->get();

<a name="where-clauses"></a>
### Where 语句

Scout 允许你在搜索查询中增加简单的「where」语句。目前，这些语句只支持基本的数值等式检查，并且主要是用于根据拥有者的 ID 进行的范围搜索查询。由于搜索索引不是关系型数据库，因此当前不支持更高级的「where」语句：

    $orders = App\Order::search('Star Trek')->where('user_id', 1)->get();

<a name="pagination"></a>
### 分页

除了检索模型的集合，你也可以使用 `paginate` 方法对搜索结果进行分页。这个方法会返回一个就像 [传统的模型查询分页](/zh-cn/db/paginator) 一样的 `Paginator`  实例：

    $orders = App\Order::search('Star Trek')->paginate();

你可以通过将数量作为第一个参数传递给 `paginate` 方法来指定每页检索多少个模型：

    $orders = App\Order::search('Star Trek')->paginate(15);

获取到检索结果后，就可以使用喜欢的模板引擎来渲染分页链接从而显示结果，就像传统的模型查询分页一样：

    <div class="container">
        @foreach ($orders as $order)
            {{ $order->price }}
        @endforeach
    </div>

    {{ $orders->links() }}

<a name="custom-engines"></a>
## 自定义引擎

#### 写引擎

如果内置的 Scout 搜索引擎不能满足你的需求，你可以写自定义的引擎并且将它注册到 Scout。你的引擎需要继承 `Hyperf\Scout\Engine\Engine` 抽象类，这个抽象类包含了你自定义的引擎必须要实现的五种方法：

    use Hyperf\Scout\Builder;

    abstract public function update($models);
    abstract public function delete($models);
    abstract public function search(Builder $builder);
    abstract public function paginate(Builder $builder, $perPage, $page);
    abstract public function map($results, $model);

在 `Hyperf\Scout\Engine\ElasticsearchEngine` 类里查看这些方法会对你有较大的帮助。这个类会为你在学习如何在自定义引擎中实现这些方法提供一个好的起点。

#### 注册引擎

一旦你写好了自定义引擎，您就可以在配置文件中指定引擎了。举个例子，如果你写好了一个 `MySqlSearchEngine`，您就可以在配置文件中这样写：
```php
<?php
return [
    'default' => 'mysql',
    'engine' => [
        'mysql' => [
            'driver' => MySqlSearchEngine::class,
        ],
        'elasticsearch' => [
            'driver' => \Hyperf\Scout\Provider\ElasticsearchProvider::class,
        ],
    ],
];
```

## 与 laravel/scout 不同之处

- Hyperf/Scout 是使用协程来高效同步搜索索引和模型记录的，无需依赖队列机制。
- Hyperf/Scout 默认提供的是开源的 Elasticsearch 引擎，而不是闭源的 Algolia。









