# Model Full-text Search

## Preface

> [hyperf/scout](https://github.com/hyperf/scout) is derived from [laravel/scout](https://github.com/laravel/scout). We have carried out some coroutine adaptations, but it maintains the same API. Here, I would like to thank the Laravel development team for implementing such a powerful and easy-to-use component. Some parts of this documentation are excerpted from the official Laravel documentation translated by the Laravel China community.

Hyperf/Scout provides a simple, driver-based solution for full-text search of models. Using model observers, Scout automatically synchronizes your search index with your model records.

Currently, Scout comes with an Elasticsearch driver; writing custom drivers is simple, and you are free to extend Scout with your own search implementation.

## Installation

### Introduce Component Package and Elasticsearch Driver

```bash
composer require hyperf/scout
composer require hyperf/elasticsearch
```

After Scout is installed, use the `vendor:publish` command to generate the Scout configuration file. This command will generate a `scout.php` configuration file in your `config` directory.

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

Finally, add the `Hyperf\Scout\Searchable` trait to the model you want to search. This trait registers a model observer to keep the model and all drivers in sync:

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

## Configuration

### Configuration File

Generate configuration file:

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

Configuration file:

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
            // If index is set to null, each model will correspond to an index; otherwise, each model corresponds to a type.
            'index' => null,
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'http://127.0.0.1:9200'),
            ],
        ],
    ],
];
```

### Configuring Model Indexes

Each model is synchronized with a given search "index", which contains all searchable records for that model. In other words, you can think of each "index" as a MySQL table. By default, each model is persisted to an index matching the model's "table" name (usually the plural form of the model name). You can also customize the model's index by overriding the `searchableAs` method on the model:

```php
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
```

### Configuring Searchable Data

By default, the "index" reads data from the model's `toArray` method for persistence. If you want to customize the data synchronized to the search index, you can override the `toSearchableArray` method on the model:

```php
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
```

## Indexing

### Batch Import

If you want to install Scout into an existing project, you may already have database records that you want to import into your search driver. Use the `import` command provided by Scout to import all existing records into the search index:

```bash
php bin/hyperf.php scout:import "App\Post"
```

### Adding Records

When you add the `Hyperf\Scout\Searchable` trait to a model, all you need to do is `save` a model instance, and it will be automatically added to your search index. The index update operation will be performed at the end of the coroutine and will not block the request.

```php
$order = new App\Order;

// ...

$order->save();
```

#### Batch Adding

If you want to add a collection of models to the search index via the model query builder, you can also chain the `searchable` method on the model query builder. `searchable` will chunk the query results and add the records to your search index.

```php
// Use model query builder to add...
App\Order::where('price', '>', 100)->searchable();

// Use model relationship to add records...
$user->orders()->searchable();

// Use collection to add records...
$orders->searchable();
```

The `searchable` method can be seen as an "update or insert" operation. In other words, if the model record is already in your index, it will be updated. If it does not exist in the search index, it will be added to the index.

### Updating Records

To update a searchable model, just update the properties of the model instance and `save` the model to the database. Scout will automatically synchronize the update to your search index:

```php
$order = App\Order::find(1);

// Update order...

$order->save();
```

You can also use the `searchable` method on model queries to update a collection of models. If this model does not exist in the index you are retrieving, it will be created:

```php
// Use model query to update...
App\Order::where('price', '>', 100)->searchable();

// You can also use model relationship to update...
$user->orders()->searchable();

// You can also use collection to update...
$orders->searchable();
```

### Removing Records

Simply use `delete` to remove the model from the database, and the record in the index will also be removed. This form of deletion is even compatible with models that are soft-deleted:

```php
$order = App\Order::find(1);

$order->delete();
```

If you do not want to retrieve the model before deleting the record, you can use the `unsearchable` method on the model query instance or collection:

```php
// Delete via model query...
App\Order::where('price', '>', 100)->unsearchable();

// Delete via model relationship...
$user->orders()->unsearchable();

// Delete via collection...
$orders->unsearchable();
```

### Pausing Indexing

You may need to perform a batch of model operations without synchronizing model data to the search index. At this time, you can use the coroutine-safe `withoutSyncingToSearch` method to perform this operation. This method accepts a callback that is executed immediately. All operations within this callback will not be synchronized to the model's index:

```php
App\Order::withoutSyncingToSearch(function () {
    // Perform model actions...
});
```

## Searching

You can use the `search` method to search for models. The `search` method accepts a string used to search the models. You also need to chain the `get` method on the search query to query the models matching the given search statement:

```php
$orders = App\Order::search('Star Trek')->get();
```

Scout search returns a collection of models, so you can return the results directly from routes or controllers, and they will be automatically converted to JSON format:

```php
Route::get('/search', function () {
    return App\Order::search([])->get();
});
```

If you want to get the raw results before they are returned as models, you should use the `raw` method:

```php
$orders = App\Order::search('Star Trek')->raw();
```

Search queries are typically executed on the index specified by the model's [`searchableAs`](#configuring-model-indexes) method. Of course, you can also use the `within` method to specify a custom index that should be searched:

```php
$orders = App\Order::search('Star Trek')
    ->within('tv_shows_popularity_desc')
    ->get();
```

### Where Clauses

Scout allows you to add simple "where" statements to search queries. Currently, these statements only support basic numerical equality checks and are mainly used for range search queries based on owner ID. Since the search index is not a relational database, more advanced "where" statements are not supported at this time:

```php
$orders = App\Order::search('Star Trek')->where('user_id', 1)->get();
```

### Pagination

In addition to retrieving a collection of models, you can also use the `paginate` method to paginate search results. This method returns a `Paginator` instance similar to [traditional model query pagination](/en/db/paginator):

```php
$orders = App\Order::search('Star Trek')->paginate();
```

You can specify how many models to retrieve per page by passing the number as the first argument to the `paginate` method:

```php
$orders = App::search('Star Trek')->paginate(15);
```

After obtaining the retrieval results, you can use your favorite template engine to render pagination links to display the results, just like traditional model query pagination:

```html
<div class="container">
    @foreach ($orders as $order)
        {{ $order->price }}
    @endforeach
</div>

{{ $orders->links() }}
```

## Custom Engines

#### Writing Engines

If the built-in Scout search engines do not meet your needs, you can write a custom engine and register it with Scout. Your engine needs to inherit from the `Hyperf\Scout\Engine\Engine` abstract class, which contains five methods that your custom engine must implement:

```php
use Hyperf\Scout\Builder;

abstract public function update($models);
abstract public function delete($models);
abstract public function search(Builder $builder);
abstract public function paginate(Builder $builder, $perPage, $page);
abstract public function map($results, $model);
```

Checking these methods in the `Hyperf\Scout\Engine\ElasticsearchEngine` class will be very helpful for you. This class provides a good starting point for learning how to implement these methods in a custom engine.

#### Registering Engines

Once you have written a custom engine, you can specify the engine in the configuration file. For example, if you have written a `MySqlSearchEngine`, you can write it like this in the configuration file:

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

## Differences from laravel/scout

- Hyperf/Scout uses coroutines to efficiently synchronize search indexes and model records, without relying on queue mechanisms.
- Hyperf/Scout provides the open-source Elasticsearch engine by default, rather than the closed-source Algolia.
