# API Resource Constructor

> Supports resource extensions that return Grpc responses.

## Introduction

When building an API, you often need a transformation layer to connect your Models to the JSON responses actually returned to users. Resource classes allow you to transform models and model collections into JSON in a more intuitive and straightforward way.

## Installation

```bash
composer require hyperf/resource
```

## Generating Resources

You can use the `gen:resource` command to generate a resource class. By default, the generated resources are placed in the `app/Resource` folder of the application. Resources inherit from the `Hyperf\Resource\Json\JsonResource` class:

```bash
php bin/hyperf.php gen:resource User
```

### Resource Collections

In addition to generating resources to transform a single model, you can also generate resource collections to transform collections of models. This allows you to include links and other meta-information related to a given resource in the response.

You need to add the `--collection` flag when generating the resource to generate a resource collection. Alternatively, you can include `Collection` directly in the resource name to indicate that a resource collection should be generated. Resource collections inherit from the `Hyperf\Resource\Json\ResourceCollection` class:

```bash
php bin/hyperf.php gen:resource Users --collection

php bin/hyperf.php gen:resource UserCollection
```

## gRPC Resources

> Requires `hyperf/resource-grpc` to be installed.

```bash
composer require hyperf/resource-grpc
```

```bash
php bin/hyperf.php gen:resource User --grpc
```

gRPC resources need to set the `message` class, which is implemented by overriding the `expect()` method of the resource class.

When the gRPC service returns, you must call `toMessage()`. This method will return an instantiated `message` class.

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

The default generated resource collection can be made to support gRPC returns by inheriting the `Hyperf\ResourceGrpc\GrpcResource` interface.

## Conceptual Overview

> This is a high-level overview of resources and resource collections. It is strongly recommended that you read the other parts of this documentation to gain a deeper understanding of how to better customize and use resources.

Before diving into how to customize and write your resources, let's first look at how to use resources in the framework. A resource class represents a single model that needs to be transformed into JSON format. For example, we have a simple `User` resource class:

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

Each resource class defines a `toArray` method, which returns an array of attributes that should be transformed into JSON when sending the response. Note that we can directly use the `$this` variable to access model attributes here. This is because the resource class automatically proxies attributes and methods to the underlying model for easy access. You can return the defined resource in the controller:

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

### Resource Collections

You can use the `collection` method in the controller to create a resource instance to return a collection of multiple resources or a paginated response:

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

Of course, using the method above, you will not be able to add any additional metadata to be returned with the collection. If you need to customize the resource collection response, you need to create a dedicated resource to represent the collection:

```bash
php bin/hyperf.php gen:resource UserCollection
```

You can easily define any metadata you want to return in the response in the generated resource collection class:

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

You can return the defined resource collection in the controller:

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

### Preserving Collection Keys

When returning a resource collection from a route, the keys of the collection will be reset so that they are in simple numerical order. However, you can add the `preserveKeys` property to the resource class to indicate whether the collection keys should be preserved:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Indicates whether the collection keys should be preserved.
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

When the `preserveKeys` property is set to `true`, the collection keys will be preserved:

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

### Customizing the Base Resource Class

Typically, the `$this->collection` property of a resource collection is automatically populated, and the result is that each item in the collection is mapped to its individual resource class. It is assumed that the individual resource class is the class name of the collection, but without the `Collection` string at the end.

For example, `UserCollection` maps the given user instance to the `User` resource. To customize this behavior, you can override the `$collects` property of the resource collection:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * The collects property defines the resource class.
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

## Writing Resources

> If you haven't read [Conceptual Overview](#conceptual-overview), it is strongly recommended that you read it before continuing with this documentation.

In essence, the role of a resource is simple. They only need to transform a given model into an array. Therefore, each resource contains a `toArray` method to transform your model attributes into an API-friendly array that can be returned to users:

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

You can return the defined resource in the controller:

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

### Relationships

If you wish to include related resources in the response, you only need to add them to the array returned by the `toArray` method. In the example below, we will use the `collection` method of the `Post` resource to add the user's posts to the resource response:
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

> If you only want to add related resources when the relationship has already been loaded, please check the relevant documentation.

### Resource Collections

A resource transforms a single model into an array, while a resource collection transforms a collection of multiple models into an array. All resources provide a `collection` method to generate a "temporary" resource collection, so you don't need to write a resource collection class for every model type:

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

To customize the metadata returned by the collection, you still need to define a resource collection:

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

Like a single resource, you can directly return the resource collection in the controller:

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

### Data Wrapping

By default, when a resource response is transformed into JSON, the top-level resource will be wrapped in a `data` key. Therefore, a typical resource collection response looks like this:

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

You can use the `withoutWrapping` method of the base resource class to disable the wrapping of the top-level resource.

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

> The `withoutWrapping` method only disables the wrapping of the top-level resource and will not delete the `data` key that you manually added to the resource collection. Moreover, it only takes effect in the current resource or resource collection and does not affect the global state.

#### Wrapping Nested Resources

You can completely decide how resource relationships are wrapped. If you wish to wrap all resource collections in a `data` key regardless of how they are nested, you need to define a resource collection class for each resource and wrap the returned collection in a `data` key.

Of course, you might worry that the top-level resource will be wrapped in two `data` keys. Rest assured, the component will never let your resources be double-wrapped, so you don't have to worry about the transformed resource collection being multi-nested:

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

#### Pagination

When returning a paginated collection in a resource response, even if you call the `withoutWrapping` method, the component will wrap your resource data in a `data` key. This is because pagination responses always have `meta` and `links` keys containing pagination status information:

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

You can pass a pagination instance to the `collection` method of a resource or a custom resource collection:

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

### Conditional Attributes

Sometimes, you may want to add attributes to the resource response only when a given condition is met. For example, you might want to add a value to the resource response only when the current user is an "administrator". In this case, the component provides some helper methods to help you solve the problem. The `when` method can be used to conditionally add attributes to a resource response:

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

In the example above, the `secret` key will only be returned in the resource response if the `isAdmin` method returns `true`. If the method returns `false`, the `secret` key will be removed before the resource response is sent to the client. The `when` method allows you to avoid using conditional statements to splice arrays, using a more elegant way to write your resources instead.

The `when` method also accepts a closure as its second argument, only calculating the value returned from the closure if the given condition is `true`:

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

#### Conditionally Merging Data

Sometimes, you may want to add multiple attributes to the resource response only when a given condition is met. In this case, you can use the `mergeWhen` method to add multiple attributes to the response when the given condition is `true`:

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

Similarly, if the given condition is `false`, these attributes will be removed before the resource response is sent to the client.

> The `mergeWhen` method should not be used in arrays that mix string and numeric keys. Furthermore, it should not be used in arrays with non-ordered numeric keys.

### Conditional Relationships

In addition to conditionally adding attributes, you can also conditionally include relationships in your resource response based on whether the model relationship has already been loaded. This allows you to decide in the controller which model relationships to load, so your resources can add them only after the model relationships have been loaded.

Doing this avoids "N+1" query problems in your resources. You should use the `whenLoaded` method to conditionally load relationships. To avoid loading unnecessary relationships, this method accepts the name of the relationship rather than the relationship itself as its argument:

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

In the example above, if the relationship has not been loaded, the `posts` key will be removed before the resource response is sent to the client.

#### Conditional Pivot Information

In addition to conditionally including relationships in your resource response, you can also use the `whenPivotLoaded` method to conditionally add data from the intermediate table of a many-to-many relationship. The first argument accepted by the `whenPivotLoaded` method is the name of the intermediate table. The second argument is a closure that defines the value to return if intermediate table information is available on the model:

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

If your intermediate table uses an accessor other than `pivot`, you can use the `whenPivotLoadedAs` method:

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

### Adding Metadata

Some JSON API standards require you to add metadata to resource and resource collection responses. This typically includes `links` to the resource or related resources, or some metadata about the resource itself. If you need to return other metadata about the resource, just include them in the `toArray` method. For example, you might need to add `links` information when transforming a resource collection:

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

When adding additional metadata to your resources, you don't need to worry about overriding the `links` or `meta` keys that are automatically added when returning a pagination response. Any other `links` you add will be merged with the `links` added by the pagination response.

#### Top-Level Metadata

Sometimes you may wish to add certain metadata to the resource response when the resource is returned as a top-level resource. This typically includes meta-information for the entire response. You can add a `with` method in the resource class to define metadata. This method should return an array of metadata, which will be included in the resource response when the resource is rendered as a top-level resource:

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

#### Adding Metadata When Constructing Resources

You can also add top-level data when constructing a resource instance in the controller. All resources can use the `additional` method to accept an array of data that should be added to the resource response:

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

## Responding to Resources

As you know, resources can be returned directly in the controller:

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

If you want to set response headers, status codes, etc., you can call the `toResponse()` method to get the response object and set them.
