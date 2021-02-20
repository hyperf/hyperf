# API 資源構造器
 
> 支援返回 Grpc 響應的資源擴充套件

## 簡介

當構建 API 時，你往往需要一個轉換層來聯結你的 Model 模型和實際返回給使用者的 JSON 響應。資源類能夠讓你以更直觀簡便的方式將模型和模型集合轉化成 JSON。

## 安裝

```
composer require hyperf/resource
```

## 生成資源

你可以使用 `gen:resource` 命令來生成一個資源類。預設情況下生成的資源都會被放置在應用程式的 `app/Resource` 資料夾下。資源繼承自 `Hyperf\Resource\Json\JsonResource` 類：

```bash
php bin/hyperf.php gen:resource User
```

### 資源集合

除了生成資源轉換單個模型外，你還可以生成資源集合用來轉換模型的集合。這允許你在響應中包含與給定資源相關的連結與其他元資訊。

你需要在生成資源時新增 `--collection` 標誌以生成一個資源集合。或者，你也可以直接在資源的名稱中包含 `Collection` 表示應該生成一個資源集合。資源集合繼承自 `Hyperf\Resource\Json\ResourceCollection` 類：

```bash
php bin/hyperf.php gen:resource Users --collection

php bin/hyperf.php gen:resource UserCollection
```

## gRPC 資源

> 需要額外安裝 `hyperf/resource-grpc`

```
composer require hyperf/resource-grpc
```

```bash
php bin/hyperf.php gen:resource User --grpc
```

gRPC 資源需要設定 `message` 類. 通過重寫該資源類的 `expect()` 方法來實現.

gRPC 服務返回時, 必須呼叫 `toMessage()`. 該方法會返回一個例項化的 `message` 類.

```php
<?php
namespace HyperfTest\ResourceGrpc\Stubs\Resources;

use Hyperf\ResourceGrpc\GrpcResource;
use HyperfTest\ResourceGrpc\Stubs\Grpc\HiReply;

class HiReplyResource extends GrpcResource
{
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'user' => HiUserResource::make($this->user),
        ];
    }

    public function expect(): string
    {
        return HiReply::class;
    }
}

```

預設生成的資源集合, 可通過繼承 `Hyperf\ResourceGrpc\GrpcResource` 介面來使其支援 gRPC 返回.

## 概念綜述

> 這是對資源和資源集合的高度概述。強烈建議你閱讀本文件的其他部分，以深入瞭解如何更好地自定義和使用資源。

在深入瞭解如何定製化編寫你的資源之前，讓我們先來看看在框架中如何使用資源。一個資源類表示一個單一模型需要被轉換成 JSON 格式。例如，現在我們有一個簡單的 `User` 資源類：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}

```

每一個資源類都定義了一個 `toArray` 方法，在傳送響應時它會返回應該被轉化成 JSON 的屬性陣列。注意在這裡我們可以直接使用 `$this` 變數來訪問模型屬性。這是因為資源類將自動代理屬性和方法到底層模型以方便訪問。你可以在控制器中返回已定義的資源：

```php
<?php

namespace App\Controller;

use App\Resource\User as UserResource;
use App\Model\User;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserResource(User::first()))->toResponse();
    }
}

```

### 資源集合

你可以在控制器中使用 `collection` 方法來建立資源例項，以返回多個資源的集合或分頁響應：

```php

namespace App\Controller;

use App\Resource\User as UserResource;
use App\Model\User;

class IndexController extends AbstractController
{
    public function index()
    {
        return UserResource::collection(User::all())->toResponse();
    }
}

```

當然了，使用如上方法你將不能新增任何附加的元資料和集合一起返回。如果你需要自定義資源集合響應，你需要建立一個專用的資源來表示集合：

```bash
php bin/hyperf.php gen:resource UserCollection
```

你可以輕鬆的在已生成的資源集合類中定義任何你想在響應中返回的元資料：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array
     */
    public function toArray() :array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}

```

你可以在控制器中返回已定義的資源集合：

```php
<?php
namespace App\Controller;

use App\Model\User;
use App\Resource\UserCollection;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserCollection(User::all()))->toResponse();
    }
}

```

### 保護集合的鍵

當從路由返回資源集合時，將重置集合的鍵，使它們以簡單的數字順序。但是，可以將 `preserveKeys` 屬性新增到資源類中，指示是否應保留集合鍵：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * 指示是否應保留資源的集合鍵。
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}

```

當 `preserveKeys` 屬性被設定為 `true`，集合的鍵將會被保護：

```php
<?php

namespace App\Controller;

use App\Model\User;
use App\Resource\User as UserResource;

class IndexController extends AbstractController
{
    public function index()
    {
        return UserResource::collection(User::all()->keyBy->id)->toResponse();
    }
}

```

### 自定義基礎資源類

通常，資源集合的 `$this->collection` 屬性會自動填充，結果是將集合的每個項對映到其單個資源類。假定單一資源類是集合的類名，但結尾沒有 `Collection` 字串。

例如，`UserCollection` 將給定的使用者例項對映到 `User` 資源中。若要自定義此行為，你可以重寫資源集合的 `$collects` 屬性：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * collects 屬性定義了資源類。
     *
     * @var string
     */
    public $collects = 'App\Resource\Member';

    /**
     * Transform the resource collection into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}

```

## 編寫資源

> 如果你還沒有閱讀 [概念綜述](#概念綜述)，那麼在繼續閱讀本文件前，強烈建議你去閱讀一下。

從本質上來說，資源的作用很簡單。它們只需要將一個給定的模型轉換成一個數組。所以每一個資源都包含一個 `toArray` 方法用來將你的模型屬性轉換成一個可以返回給使用者的 API 友好陣列：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}

```

你可以在控制器中返回已經定義的資源：

```php
<?php

namespace App\Controller;

use App\Model\User;
use App\Resource\User as UserResource;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserResource(User::find(1)))->toResponse();
    }
}

```

### 關聯

如果你希望在響應中包含關聯資源，你只需要將它們新增到 `toArray` 方法返回的陣列中。在下面這個例子裡，我們將使用 `Post` 資源的 `collection` 方法將使用者的文章新增到資源響應中：
```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts' => PostResource::collection($this->posts),
        ];
    }
}

```

> 如果你只想在關聯已經載入時才新增關聯資源，請檢視相關文件。

### 資源集合

資源是將單個模型轉換成陣列，而資源集合是將多個模型的集合轉換成陣列。所有的資源都提供了一個 `collection` 方法來生成一個 「臨時」 資源集合，所以你沒有必要為每一個模型型別都編寫一個資源集合類：

```php
<?php
namespace App\Controller;

use App\Resource\User as UserResource;
use App\Model\User;

class IndexController extends AbstractController
{
    public function index()
    {
        return UserResource::collection(User::all())->toResponse();
    }
}

```

要自定義返回集合的元資料，則仍需要定義一個資源集合：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}

```

和單個資源一樣，你可以在控制器中直接返回資源集合：

```php
<?php
namespace App\Controller;

use App\Model\User;
use App\Resource\UserCollection;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserCollection(User::all()))->toResponse();
    }
}

```

### 資料包裹

預設情況下，當資源響應被轉換成 JSON 時，頂層資源將會被包裹在 `data` 鍵中。因此一個典型的資源集合響應如下所示：

```json

    {
        "data": [
            {
                "id": 1,
                "name": "Eladio Schroeder Sr.",
                "email": "therese28@example.com"
            },
            {
                "id": 2,
                "name": "Liliana Mayert",
                "email": "evandervort@example.com"
            }
        ]
    }

```

你可以使用資源基類的 `withoutWrapping` 方法來禁用頂層資源的包裹。

```php
<?php
namespace App\Controller;

use App\Model\User;
use App\Resource\UserCollection;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserCollection(User::all()))->withoutWrapping()->toResponse();
    }
}

```

> withoutWrapping 方法只會禁用頂層資源的包裹，不會刪除你手動新增到資源集合中的 data 鍵。而且只會在當前的資源或資源集合中生效，不影響全域性。

#### 包裹巢狀資源

你可以完全自由地決定資源關聯如何被包裹。如果你希望無論怎樣巢狀，都將所有資源集合包裹在 `data` 鍵中，那麼你需要為每個資源都定義一個資源集合類，並將返回的集合包裹在 `data` 鍵中。

當然，你可能會擔心這樣頂層資源將會被包裹在兩個 `data `鍵中。請放心， 元件將永遠不會讓你的資源被雙層包裹，因此你不必擔心被轉換的資源集合會被多重巢狀：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}

```

#### 分頁

當在資源響應中返回分頁集合時，即使你呼叫了 `withoutWrapping` 方法， 元件也會將你的資源資料包裹在 `data` 鍵中。這是因為分頁響應中總會有 `meta` 和 `links` 鍵包含著分頁狀態資訊：

```json

    {
        "data": [
            {
                "id": 1,
                "name": "Eladio Schroeder Sr.",
                "email": "therese28@example.com"
            },
            {
                "id": 2,
                "name": "Liliana Mayert",
                "email": "evandervort@example.com"
            }
        ],
        "links":{
            "first": "/pagination?page=1",
            "last": "/pagination?page=1",
            "prev": null,
            "next": null
        },
        "meta":{
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "/pagination",
            "per_page": 15,
            "to": 10,
            "total": 10
        }
    }
```

你可以將分頁例項傳遞給資源的 `collection` 方法或者自定義的資源集合：

```php
<?php

namespace App\Controller;

use App\Model\User;
use App\Resource\UserCollection;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserCollection(User::paginate()))->toResponse();
    }
}
```

分頁響應中總有 `meta` 和 `links` 鍵包含著分頁狀態資訊：

```json

    {
        "data": [
            {
                "id": 1,
                "name": "Eladio Schroeder Sr.",
                "email": "therese28@example.com"
            },
            {
                "id": 2,
                "name": "Liliana Mayert",
                "email": "evandervort@example.com"
            }
        ],
        "links":{
            "first": "/pagination?page=1",
            "last": "/pagination?page=1",
            "prev": null,
            "next": null
        },
        "meta":{
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "/pagination",
            "per_page": 15,
            "to": 10,
            "total": 10
        }
    }
```

### 條件屬性

有些時候，你可能希望在給定條件滿足時新增屬性到資源響應裡。例如，你可能希望如果當前使用者是 「管理員」 時新增某個值到資源響應中。在這種情況下元件提供了一些輔助方法來幫助你解決問題。 `when` 方法可以被用來有條件地向資源響應新增屬性：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'secret' => $this->when(Auth::user()->isAdmin(), 'secret-value'),
        ];
    }
}

```

在上面這個例子中，只有當 `isAdmin` 方法返回 `true` 時， `secret` 鍵才會最終在資源響應中被返回。如果該方法返回 `false` ，則 `secret` 鍵將會在資源響應被髮送給客戶端之前被刪除。 `when` 方法可以使你避免使用條件語句拼接陣列，轉而用更優雅的方式來編寫你的資源。

`when` 方法也接受閉包作為其第二個引數，只有在給定條件為 `true` 時，才從閉包中計算返回的值：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'secret' => $this->when(Auth::user()->isAdmin(), function () {
                return 'secret-value';
            }),
        ];
    }
}

```

#### 有條件的合併資料

有些時候，你可能希望在給定條件滿足時新增多個屬性到資源響應裡。在這種情況下，你可以使用 `mergeWhen` 方法在給定的條件為 `true` 時將多個屬性新增到響應中：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            $this->mergeWhen(Auth::user()->isAdmin(), [
                'first-secret' => 'value',
                'second-secret' => 'value',
            ]),
        ];
    }
}

```

同理，如果給定的條件為 `false` 時，則這些屬性將會在資源響應被髮送給客戶端之前被移除。

> `mergeWhen` 方法不應該被使用在混合字串和數字鍵的陣列中。此外，它也不應該被使用在不按順序排列的數字鍵的陣列中。

### 條件關聯

除了有條件地新增屬性之外，你還可以根據模型關聯是否已載入來有條件地在你的資源響應中包含關聯。這允許你在控制器中決定載入哪些模型關聯，這樣你的資源可以在模型關聯被載入後才新增它們。

這樣做可以避免在你的資源中出現 「N+1」 查詢問題。你應該使用 `whenLoaded` 方法來有條件的載入關聯。為了避免載入不必要的關聯，此方法接受關聯的名稱而不是關聯本身作為其引數：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts' => PostResource::collection($this->whenLoaded('posts')),
        ];
    }
}

```

在上面這個例子中，如果關聯沒有被載入，則 `posts` 鍵將會在資源響應被髮送給客戶端之前被刪除。

#### 條件中間表資訊

除了在你的資源響應中有條件地包含關聯外，你還可以使用 `whenPivotLoaded`  方法有條件地從多對多關聯的中間表中新增資料。 `whenPivotLoaded` 方法接受的第一個引數為中間表的名稱。第二個引數是一個閉包，它定義了在模型上如果中間表資訊可用時要返回的值：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'expires_at' => $this->whenPivotLoaded('role_user', function () {
                return $this->pivot->expires_at;
            }),
        ];
    }
}

```

如果你的中間表使用的是 `pivot` 以外的訪問器，你可以使用 `whenPivotLoadedAs`方法：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'expires_at' => $this->whenPivotLoadedAs('subscription', 'role_user', function () {
                return $this->subscription->expires_at;
            }),
        ];
    }
}

```

### 新增元資料

一些 JSON API 標準需要你在資源和資源集合響應中新增元資料。這通常包括資源或相關資源的 `links` ，或一些關於資源本身的元資料。如果你需要返回有關資源的其他元資料，只需要將它們包含在 `toArray` 方法中即可。例如在轉換資源集合時你可能需要新增 `links` 資訊：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}

```

當新增額外的元資料到你的資源中時，你不必擔心會覆蓋在返回分頁響應時自動新增的 `links` 或 `meta` 鍵。你新增的任何其他 `links` 會與分頁響應新增的 `links` 相合並。

#### 頂層元資料

有時候你可能希望當資源被作為頂層資源返回時新增某些元資料到資源響應中。這通常包括整個響應的元資訊。你可以在資源類中新增 `with` 方法來定義元資料。此方法應返回一個元資料陣列，當資源被作為頂層資源渲染時，這個陣列將會被包含在資源響應中：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }

    public function with() : array
    {
        return [
            'meta' => [
                'key' => 'value',
            ],
        ];
    }
}

```

#### 構造資源時新增元資料

你還可以在控制器中構造資源例項時新增頂層資料。所有資源都可以使用 `additional` 方法來接受應該被新增到資源響應中的資料陣列：

```php
<?php

namespace App\Controller;

use App\Model\User;
use App\Resource\UserCollection;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserCollection(User::all()->load('roles')))
            ->additional(['meta' => [
                'key' => 'value',
            ]])->toResponse();    
    }
}

```

## 響應資源

就像你知道的那樣，資源可以直接在控制器中被返回：

```php
<?php

namespace App\Controller;

use App\Model\User;
use App\Resource\User as UserResource;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserResource(User::find(1)))->toResponse();
    }

    public function info()
    {
        return new UserResource(User::find(1));
    }
}

```

如你想設定響應頭資訊, 狀態碼等, 通過呼叫 `toResponse()` 方法獲取到響應物件進行設定.
