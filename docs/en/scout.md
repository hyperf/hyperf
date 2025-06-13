# Model full text search

## Preface

> [hyperf/scout](https://github.com/hyperf/scout) is derived from [laravel/scout](https://github.com/laravel/scout), we have made some coroutine transformation to it , but maintains the same API. I would like to thank the Laravel development team for implementing such a powerful and easy-to-use component. This document is partially excerpted from the official Laravel documentation translated by the Laravel China community organization.

Hyperf/Scout provides a simple, driver-based solution for full-text search of models. Using model watchers, Scout automatically synchronizes your search index and model records.

Currently, Scout comes with an Elasticsearch driver; writing a custom driver is simple, and you are free to extend Scout with your own search implementation.

## Install

### Introduce component package and Elasticsearch driver

```bash
composer require hyperf/scout
composer require hyperf/elasticsearch
```

After Scout is installed, use the vendor:publish command to generate the Scout configuration file. This command will generate a scout.php configuration file in your config directory.

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

Finally, add the Hyperf\Scout\Searchable trait to the model you want to search. This trait registers a model observer to keep the model in sync with all drivers:

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
## Configure

### Config file

Generate configuration file

```
php bin/hyperf.php vendor:publish hyperf/scout
```

Configuration file

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
            // If index is set to null, each model corresponds to an index, otherwise each model corresponds to a type
            'index' => null,
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'http://127.0.0.1:9200'),
            ],
        ],
    ],
];

```
### Configure model index

Each model is synchronized with a given search "index" that contains all searchable records for that model. In other words, you can think of each "index" as a MySQL table. By default, each model is persisted to an index that matches the model's "table" name (usually the plural of the model name). You can also customize the model's index by overriding the `searchableAs` method on the model:
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

<a name="configuring-searchable-data"></a>

### Configure searchable data

By default, "index" will read data from the model's `toArray` method for persistence. If you want to customize the data synced to the search index, you can override the `toSearchableArray` method on the model:
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

<a name="indexing"></a>
## index

<a name="batch-import"></a>
### Batch Import

If you want to install Scout into an existing project, you probably already have database records that you want to import into search-driven. Import all existing records into the search index using the `import` command provided by Scout:
```bash
    php bin/hyperf.php scout:import "App\Post"
```

<a name="adding-records"></a>
### Add record

When you add the Trait `Hyperf\Scout\Searchable` to a model, all you need to do is `save` a model instance and it will be automatically added to your search index. The update index operation will be done at the end of the coroutine and will not block the request.
```php
    $order = new App\Order;

    // ...

    $order->save();
```

#### Bulk add

If you want to add a collection of models to the search index via the model query builder, you can also chain the `searchable` method on the model query builder. `searchable` will chunk the query result of the constructor and add the record to your search index.
```php
    // Use the Model Query Builder to add...
    App\Order::where('price', '>', 100)->searchable();

    // Adding records using model relationships...
    $user->orders()->searchable();

    // Adding records using collections...
    $orders->searchable();
```

The `searchable` method can be thought of as an "upsert" operation. In other words, if the model record is already in your index, it will be updated. If it doesn't exist in the search index, add it to the index.

<a name="updating-records"></a>
### update record

To update a searchable model, simply update the properties of the model instance and `save` the model to the database. Scout will automatically sync updates to your search index:
```php
    $order = App\Order::find(1);

    // Update order...

    $order->save();
```

You can also use the `searchable` method on a model query statement to update a collection of models. If the model doesn't exist in the index you're retrieving, it will be created:
```php
    // Update with model query statement...
    App\Order::where('price', '>', 100)->searchable();

    // You can also use model relational updates...
    $user->orders()->searchable();

    // You can also use collection update...
    $orders->searchable();
```

<a name="removing-records"></a>
### Delete Record

Simply delete the model from the database using `delete` to remove the record in the index. This form of deletion is even compatible with the soft-deleted model:
```php
    $order = App\Order::find(1);

    $order->delete();
```

If you don't want to retrieve the model before deleting the record, you can use the `unsearchable` method on the model query instance or collection:
```php
    // Delete via model query...
    App\Order::where('price', '>', 100)->unsearchable();

    // Delete via model relationship...
    $user->orders()->unsearchable();

    // Delete by Collection...
    $orders->unsearchable();
```
<a name="pausing-indexing"></a>
### Pause indexing

You may need to perform a batch of model operations without syncing model data to the search index. At this point you can use the coroutine-safe `withoutSyncingToSearch` method to do this. This method accepts a callback that executes immediately. All operations in this callback will not be synchronized to the model's index:
```php
    App\Order::withoutSyncingToSearch(function () {
        // Execute model actions...
    });
```
<a name="searching"></a>
## search

You can use the `search` method to search for models. The `search` method accepts a string to search for the model. You also need to chain the `get` method on the search query to query the matching model with a given search statement:
```php
    $orders = App\Order::search('Star Trek')->get();
```

Scout searches return collections of model models, so you can return results directly from routes or controllers, and they will be automatically converted to JSON:
```php
    Route::get('/search', function () {
        return App\Order::search([])->get();
    });
```

If you want raw results before they are returned to the model, you should use the `raw` method:
```php
    $orders = App\Order::search('Star Trek')->raw();
```

Search queries are usually executed on the indexes specified by the model's [`searchableAs`](#configuring-model-indexes) method. Of course, you can also use the `within` method to specify a custom index that should be searched:
```php
    $orders = App\Order::search('Star Trek')
        ->within('tv_shows_popularity_desc')
        ->get();
```
<a name="where-clauses"></a>
### Where Statement

Scout allows you to add simple "where" clauses to your search queries. Currently, these statements only support basic numerical equality checks, and are primarily used for range search queries based on the owner's ID. Since search indexes are not relational databases, more advanced "where" statements are currently not supported:
```php
    $orders = App\Order::search('Star Trek')->where('user_id', 1)->get();
```

<a name="pagination"></a>
### Pagination

In addition to retrieving a collection of models, you can also use the `paginate` method to paginate search results. This method will return a `Paginator` instance like [traditional model query pagination](/en/db/paginator):
```php
    $orders = App\Order::search('Star Trek')->paginate();
```

You can specify how many models to retrieve per page by passing the number as the first argument to the `paginate` method:
```php
    $orders = App\Order::search('Star Trek')->paginate(15);
```

After obtaining the retrieval results, you can use your favorite template engine to render the pagination links to display the results, just like traditional model query pagination:
```php
    <div class="container">
        @foreach ($orders as $order)
            {{ $order->price }}
        @endforeach
    </div>

    {{ $orders->links() }}
```
<a name="custom-engines"></a>
## custom engine

#### write engine

If the built-in Scout search engine does not meet your needs, you can write a custom engine and register it with Scout. Your engine needs to inherit the `Hyperf\Scout\Engine\Engine` abstract class, which contains five methods that your custom engine must implement:
```php
    use Hyperf\Scout\Builder;

    abstract public function update($models);
    abstract public function delete($models);
    abstract public function search(Builder $builder);
    abstract public function paginate(Builder $builder, $perPage, $page);
    abstract public function map($results, $model);
```
It will be helpful to see these methods in the `Hyperf\Scout\Engine\ElasticsearchEngine` class. This class will provide a good starting point for you to learn how to implement these methods in your custom engine.

#### Registration engine

Once you have written your custom engine, you can specify the engine in the configuration file. For example, if you have written a `MySqlSearchEngine`, you can write this in the configuration file:
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

- Hyperf/Scout uses coroutines to efficiently synchronize search indexes and model records without relying on queue mechanisms.
- Hyperf/Scout provides the open source Elasticsearch engine by default instead of the closed source Algolia.
