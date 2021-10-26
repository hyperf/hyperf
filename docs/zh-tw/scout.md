# 模型全文檢索

## 前言

> [hyperf/scout](https://github.com/hyperf/scout) 衍生於 [laravel/scout](https://github.com/laravel/scout)，我們對它進行了一些協程化改造，但保持了相同的 API。在這裡感謝一下 Laravel 開發組，實現瞭如此強大好用的元件。本文件部分節選自 Laravel China 社群組織翻譯的 Laravel 官方文件。

Hyperf/Scout 為模型的全文搜尋提供了一個簡單的、基於驅動程式的解決方案。使用模型觀察員，Scout 會自動同步你的搜尋索引和模型記錄。

目前，Scout 自帶了一個 Elasticsearch 驅動；而編寫自定義驅動程式很簡單，你可以自由地使用自己的搜尋實現來擴充套件 Scout。

## 安裝

### 引入元件包和 Elasticsearch 驅動

```bash
composer require hyperf/scout
composer require hyperf/elasticsearch
```

Scout 安裝完成後，使用 vendor:publish 命令來生成 Scout 配置檔案。這個命令將在你的 config 目錄下生成一個 scout.php 配置檔案。

```bash
php bin/hyperf.php vendor:publish hyperf/scout
```

最後，在你要做搜尋的模型中新增 Hyperf\Scout\Searchable trait。這個 trait 會註冊一個模型觀察者來保持模型和所有驅動的同步：

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

### 配置檔案

生成配置檔案

```
php bin/hyperf.php vendor:publish hyperf/scout
```

配置檔案

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
            // 如果 index 設定為 null，則每個模型會對應一個索引，反之每個模型對應一個型別
            'index' => null,
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'http://127.0.0.1:9200'),
            ],
        ],
    ],
];

```
### 配置模型索引

每個模型與給定的搜尋「索引」同步，這個「索引」包含該模型的所有可搜尋記錄。換句話說，你可以把每一個「索引」設想為一張 MySQL 資料表。預設情況下，每個模型都會被持久化到與模型的「表」名（通常是模型名稱的複數形式）相匹配的索引。你也可以通過覆蓋模型上的 `searchableAs` 方法來自定義模型的索引：

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

### 配置可搜尋的資料

預設情況下，「索引」會從模型的 `toArray` 方法中讀取資料來做持久化。如果要自定義同步到搜尋索引的資料，可以覆蓋模型上的 `toSearchableArray` 方法：

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
### 批量匯入

如果你想要將 Scout 安裝到現有的專案中，你可能已經有了想要匯入搜尋驅動的資料庫記錄。使用 Scout 提供的命令 `import` 把所有現有記錄匯入到搜尋索引裡：

    php bin/hyperf.php scout:import "App\Post"

<a name="adding-records"></a>
### 新增記錄

當你將 Trait `Hyperf\Scout\Searchable` 新增到模型中，你需要做的是 `save` 一個模型例項，它就會自動新增到你的搜尋索引。更新索引操作將會在協程結束時進行，不會堵塞請求。

    $order = new App\Order;

    // ...

    $order->save();

#### 批量新增

如果你想通過模型查詢構造器將模型集合新增到搜尋索引中，你也可以在模型查詢構造器上鍊式呼叫 `searchable` 方法。`searchable` 會把構造器的查詢結果分塊並且將記錄新增到你的搜尋索引裡。

    // 使用模型查詢構造器增加...
    App\Order::where('price', '>', 100)->searchable();

    // 使用模型關係增加記錄...
    $user->orders()->searchable();

    // 使用集合增加記錄...
    $orders->searchable();

`searchable` 方法可以被看做是「更新插入」的操作。換句話說，如果模型記錄已經在你的索引裡了，它就會被更新。如果搜尋索引中不存在，則將其新增到索引中。

<a name="updating-records"></a>
### 更新記錄

要更新可搜尋的模型，只需要更新模型例項的屬性並將模型 `save` 到資料庫。Scout 會自動將更新同步到你的搜尋索引中：

    $order = App\Order::find(1);

    // 更新 order...

    $order->save();

你也可以在模型查詢語句上使用 `searchable` 方法來更新一個模型的集合。如果這個模型不存在你檢索的索引裡，就會被建立：

    // 使用模型查詢語句更新...
    App\Order::where('price', '>', 100)->searchable();

    // 你也可以使用模型關係更新...
    $user->orders()->searchable();

    // 你也可以使用集合更新...
    $orders->searchable();

<a name="removing-records"></a>
### 刪除記錄

簡單地使用 `delete` 從資料庫中刪除該模型就可以移除索引裡的記錄。這種刪除形式甚至與軟刪除的模型相容:

    $order = App\Order::find(1);

    $order->delete();

如果你不想在刪除記錄之前檢索模型，可以在模型查詢例項或集合上使用 `unsearchable` 方法：

    // 通過模型查詢刪除...
    App\Order::where('price', '>', 100)->unsearchable();

    // 通過模型關係刪除...
    $user->orders()->unsearchable();

    // 通過集合刪除...
    $orders->unsearchable();

<a name="pausing-indexing"></a>
### 暫停索引

你可能需要在執行一批模型操作的時候，不同步模型資料到搜尋索引。此時你可以使用協程安全的 `withoutSyncingToSearch` 方法來執行此操作。這個方法接受一個立即執行的回撥。該回調中所有的操作都不會同步到模型的索引：

    App\Order::withoutSyncingToSearch(function () {
        // 執行模型動作...
    });

<a name="searching"></a>
## 搜尋

你可以使用 `search` 方法來搜尋模型。`search` 方法接受一個用於搜尋模型的字串。你還需在搜尋查詢上鍊式呼叫 `get` 方法，才能用給定的搜尋語句查詢與之匹配的模型模型：

    $orders = App\Order::search('Star Trek')->get();
Scout 搜尋返回模型模型的集合，因此你可以直接從路由或控制器返回結果，它們會被自動轉換成 JSON 格式：

    Route::get('/search', function () {
        return App\Order::search([])->get();
    });

如果你想在它們返回模型模型前得到原結果，你應該使用`raw` 方法:

    $orders = App\Order::search('Star Trek')->raw();

搜尋查詢通常會在模型的 [`searchableAs`](#configuring-model-indexes) 方法指定的索引上執行。當然，你也可以使用 `within` 方法指定應該搜尋的自定義索引:

    $orders = App\Order::search('Star Trek')
        ->within('tv_shows_popularity_desc')
        ->get();

<a name="where-clauses"></a>
### Where 語句

Scout 允許你在搜尋查詢中增加簡單的「where」語句。目前，這些語句只支援基本的數值等式檢查，並且主要是用於根據擁有者的 ID 進行的範圍搜尋查詢。由於搜尋索引不是關係型資料庫，因此當前不支援更高階的「where」語句：

    $orders = App\Order::search('Star Trek')->where('user_id', 1)->get();

<a name="pagination"></a>
### 分頁

除了檢索模型的集合，你也可以使用 `paginate` 方法對搜尋結果進行分頁。這個方法會返回一個就像 [傳統的模型查詢分頁](/zh-tw/db/paginator) 一樣的 `Paginator`  例項：

    $orders = App\Order::search('Star Trek')->paginate();

你可以通過將數量作為第一個引數傳遞給 `paginate` 方法來指定每頁檢索多少個模型：

    $orders = App\Order::search('Star Trek')->paginate(15);

獲取到檢索結果後，就可以使用喜歡的模板引擎來渲染分頁連結從而顯示結果，就像傳統的模型查詢分頁一樣：

    <div class="container">
        @foreach ($orders as $order)
            {{ $order->price }}
        @endforeach
    </div>

    {{ $orders->links() }}

<a name="custom-engines"></a>
## 自定義引擎

#### 寫引擎

如果內建的 Scout 搜尋引擎不能滿足你的需求，你可以寫自定義的引擎並且將它註冊到 Scout。你的引擎需要繼承 `Hyperf\Scout\Engine\Engine` 抽象類，這個抽象類包含了你自定義的引擎必須要實現的五種方法：

    use Hyperf\Scout\Builder;

    abstract public function update($models);
    abstract public function delete($models);
    abstract public function search(Builder $builder);
    abstract public function paginate(Builder $builder, $perPage, $page);
    abstract public function map($results, $model);

在 `Hyperf\Scout\Engine\ElasticsearchEngine` 類裡檢視這些方法會對你有較大的幫助。這個類會為你在學習如何在自定義引擎中實現這些方法提供一個好的起點。

#### 註冊引擎

一旦你寫好了自定義引擎，您就可以在配置檔案中指定引擎了。舉個例子，如果你寫好了一個 `MySqlSearchEngine`，您就可以在配置檔案中這樣寫：
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

## 與 laravel/scout 不同之處

- Hyperf/Scout 是使用協程來高效同步搜尋索引和模型記錄的，無需依賴佇列機制。
- Hyperf/Scout 預設提供的是開源的 Elasticsearch 引擎，而不是閉源的 Algolia。









