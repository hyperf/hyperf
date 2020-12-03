# API 资源构造器
 
> 支持返回 Grpc 响应的资源扩展

## 简介

当构建 API 时，你往往需要一个转换层来联结你的 Model 模型和实际返回给用户的 JSON 响应。资源类能够让你以更直观简便的方式将模型和模型集合转化成 JSON。

## 安装

```
composer require hyperf/resource
```

## 生成资源

你可以使用 `gen:resource` 命令来生成一个资源类。默认情况下生成的资源都会被放置在应用程序的 `app/Resource` 文件夹下。资源继承自 `Hyperf\Resource\Json\JsonResource` 类：

```bash
php bin/hyperf.php gen:resource User
```

### 资源集合

除了生成资源转换单个模型外，你还可以生成资源集合用来转换模型的集合。这允许你在响应中包含与给定资源相关的链接与其他元信息。

你需要在生成资源时添加 `--collection` 标志以生成一个资源集合。或者，你也可以直接在资源的名称中包含 `Collection` 表示应该生成一个资源集合。资源集合继承自 `Hyperf\Resource\Json\ResourceCollection` 类：

```bash
php bin/hyperf.php gen:resource Users --collection

php bin/hyperf.php gen:resource UserCollection
```

## gRPC 资源

> 需要额外安装 `hyperf/resource-grpc`

```
composer require hyperf/resource-grpc
```

```bash
php bin/hyperf.php gen:resource User --grpc
```

gRPC 资源需要设置 `message` 类. 通过重写该资源类的 `expect()` 方法来实现.

gRPC 服务返回时, 必须调用 `toMessage()`. 该方法会返回一个实例化的 `message` 类.

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

默认生成的资源集合, 可通过继承 `Hyperf\ResourceGrpc\GrpcResource` 接口来使其支持 gRPC 返回.

## 概念综述

> 这是对资源和资源集合的高度概述。强烈建议你阅读本文档的其他部分，以深入了解如何更好地自定义和使用资源。

在深入了解如何定制化编写你的资源之前，让我们先来看看在框架中如何使用资源。一个资源类表示一个单一模型需要被转换成 JSON 格式。例如，现在我们有一个简单的 `User` 资源类：

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

每一个资源类都定义了一个 `toArray` 方法，在发送响应时它会返回应该被转化成 JSON 的属性数组。注意在这里我们可以直接使用 `$this` 变量来访问模型属性。这是因为资源类将自动代理属性和方法到底层模型以方便访问。你可以在控制器中返回已定义的资源：

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

### 资源集合

你可以在控制器中使用 `collection` 方法来创建资源实例，以返回多个资源的集合或分页响应：

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

当然了，使用如上方法你将不能添加任何附加的元数据和集合一起返回。如果你需要自定义资源集合响应，你需要创建一个专用的资源来表示集合：

```bash
php bin/hyperf.php gen:resource UserCollection
```

你可以轻松的在已生成的资源集合类中定义任何你想在响应中返回的元数据：

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

你可以在控制器中返回已定义的资源集合：

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

### 保护集合的键

当从路由返回资源集合时，将重置集合的键，使它们以简单的数字顺序。但是，可以将 `preserveKeys` 属性添加到资源类中，指示是否应保留集合键：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * 指示是否应保留资源的集合键。
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

当 `preserveKeys` 属性被设置为 `true`，集合的键将会被保护：

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

### 自定义基础资源类

通常，资源集合的 `$this->collection` 属性会自动填充，结果是将集合的每个项映射到其单个资源类。假定单一资源类是集合的类名，但结尾没有 `Collection` 字符串。

例如，`UserCollection` 将给定的用户实例映射到 `User` 资源中。若要自定义此行为，你可以重写资源集合的 `$collects` 属性：

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * collects 属性定义了资源类。
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

## 编写资源

> 如果你还没有阅读 [概念综述](#概念综述)，那么在继续阅读本文档前，强烈建议你去阅读一下。

从本质上来说，资源的作用很简单。它们只需要将一个给定的模型转换成一个数组。所以每一个资源都包含一个 `toArray` 方法用来将你的模型属性转换成一个可以返回给用户的 API 友好数组：

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

你可以在控制器中返回已经定义的资源：

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

### 关联

如果你希望在响应中包含关联资源，你只需要将它们添加到 `toArray` 方法返回的数组中。在下面这个例子里，我们将使用 `Post` 资源的 `collection` 方法将用户的文章添加到资源响应中：
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

> 如果你只想在关联已经加载时才添加关联资源，请查看相关文档。

### 资源集合

资源是将单个模型转换成数组，而资源集合是将多个模型的集合转换成数组。所有的资源都提供了一个 `collection` 方法来生成一个 「临时」 资源集合，所以你没有必要为每一个模型类型都编写一个资源集合类：

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

要自定义返回集合的元数据，则仍需要定义一个资源集合：

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

和单个资源一样，你可以在控制器中直接返回资源集合：

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

### 数据包裹

默认情况下，当资源响应被转换成 JSON 时，顶层资源将会被包裹在 `data` 键中。因此一个典型的资源集合响应如下所示：

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

你可以使用资源基类的 `withoutWrapping` 方法来禁用顶层资源的包裹。

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

> withoutWrapping 方法只会禁用顶层资源的包裹，不会删除你手动添加到资源集合中的 data 键。而且只会在当前的资源或资源集合中生效，不影响全局。

#### 包裹嵌套资源

你可以完全自由地决定资源关联如何被包裹。如果你希望无论怎样嵌套，都将所有资源集合包裹在 `data` 键中，那么你需要为每个资源都定义一个资源集合类，并将返回的集合包裹在 `data` 键中。

当然，你可能会担心这样顶层资源将会被包裹在两个 `data `键中。请放心， 组件将永远不会让你的资源被双层包裹，因此你不必担心被转换的资源集合会被多重嵌套：

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

#### 分页

当在资源响应中返回分页集合时，即使你调用了 `withoutWrapping` 方法， 组件也会将你的资源数据包裹在 `data` 键中。这是因为分页响应中总会有 `meta` 和 `links` 键包含着分页状态信息：

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

你可以将分页实例传递给资源的 `collection` 方法或者自定义的资源集合：

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

分页响应中总有 `meta` 和 `links` 键包含着分页状态信息：

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

### 条件属性

有些时候，你可能希望在给定条件满足时添加属性到资源响应里。例如，你可能希望如果当前用户是 「管理员」 时添加某个值到资源响应中。在这种情况下组件提供了一些辅助方法来帮助你解决问题。 `when` 方法可以被用来有条件地向资源响应添加属性：

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

在上面这个例子中，只有当 `isAdmin` 方法返回 `true` 时， `secret` 键才会最终在资源响应中被返回。如果该方法返回 `false` ，则 `secret` 键将会在资源响应被发送给客户端之前被删除。 `when` 方法可以使你避免使用条件语句拼接数组，转而用更优雅的方式来编写你的资源。

`when` 方法也接受闭包作为其第二个参数，只有在给定条件为 `true` 时，才从闭包中计算返回的值：

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

#### 有条件的合并数据

有些时候，你可能希望在给定条件满足时添加多个属性到资源响应里。在这种情况下，你可以使用 `mergeWhen` 方法在给定的条件为 `true` 时将多个属性添加到响应中：

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

同理，如果给定的条件为 `false` 时，则这些属性将会在资源响应被发送给客户端之前被移除。

> `mergeWhen` 方法不应该被使用在混合字符串和数字键的数组中。此外，它也不应该被使用在不按顺序排列的数字键的数组中。

### 条件关联

除了有条件地添加属性之外，你还可以根据模型关联是否已加载来有条件地在你的资源响应中包含关联。这允许你在控制器中决定加载哪些模型关联，这样你的资源可以在模型关联被加载后才添加它们。

这样做可以避免在你的资源中出现 「N+1」 查询问题。你应该使用 `whenLoaded` 方法来有条件的加载关联。为了避免加载不必要的关联，此方法接受关联的名称而不是关联本身作为其参数：

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

在上面这个例子中，如果关联没有被加载，则 `posts` 键将会在资源响应被发送给客户端之前被删除。

#### 条件中间表信息

除了在你的资源响应中有条件地包含关联外，你还可以使用 `whenPivotLoaded`  方法有条件地从多对多关联的中间表中添加数据。 `whenPivotLoaded` 方法接受的第一个参数为中间表的名称。第二个参数是一个闭包，它定义了在模型上如果中间表信息可用时要返回的值：

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

如果你的中间表使用的是 `pivot` 以外的访问器，你可以使用 `whenPivotLoadedAs`方法：

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

### 添加元数据

一些 JSON API 标准需要你在资源和资源集合响应中添加元数据。这通常包括资源或相关资源的 `links` ，或一些关于资源本身的元数据。如果你需要返回有关资源的其他元数据，只需要将它们包含在 `toArray` 方法中即可。例如在转换资源集合时你可能需要添加 `links` 信息：

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

当添加额外的元数据到你的资源中时，你不必担心会覆盖在返回分页响应时自动添加的 `links` 或 `meta` 键。你添加的任何其他 `links` 会与分页响应添加的 `links` 相合并。

#### 顶层元数据

有时候你可能希望当资源被作为顶层资源返回时添加某些元数据到资源响应中。这通常包括整个响应的元信息。你可以在资源类中添加 `with` 方法来定义元数据。此方法应返回一个元数据数组，当资源被作为顶层资源渲染时，这个数组将会被包含在资源响应中：

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

#### 构造资源时添加元数据

你还可以在控制器中构造资源实例时添加顶层数据。所有资源都可以使用 `additional` 方法来接受应该被添加到资源响应中的数据数组：

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

## 响应资源

就像你知道的那样，资源可以直接在控制器中被返回：

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

如你想设置响应头信息, 状态码等, 通过调用 `toResponse()` 方法获取到响应对象进行设置.
