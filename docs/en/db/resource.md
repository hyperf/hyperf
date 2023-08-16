# API resource constructor
 
> Support for resource extensions that return Grpc responses

## Introduction

When building APIs, you often need a translation layer to connect your Model with the actual JSON response returned to the user. Resource classes allow you to convert models and collections of models to JSON in a more intuitive and easy way.

## Install

```
composer require hyperf/resource
```

## Generate resources

You can use the `gen:resource` command to generate a resource class. By default generated resources are placed in the application's `app/Resource` folder. Resources inherit from the `Hyperf\Resource\Json\JsonResource` class:

```bash
php bin/hyperf.php gen:resource User
```

### Resource Collection

In addition to generating resources to transform a single model, you can also generate a collection of resources to transform a collection of models. This allows you to include links and other meta information related to a given resource in the response.

You need to add the `--collection` flag when generating resources to generate a collection of resources. Alternatively, you can include `Collection` directly in the resource name to indicate that a collection of resources should be generated. Resource collections inherit from the `Hyperf\Resource\Json\ResourceCollection` class:

```bash
php bin/hyperf.php gen:resource Users --collection

php bin/hyperf.php gen:resource UserCollection
```

## gRPC resources

> Requires additional installation of `hyperf/resource-grpc`

```
composer require hyperf/resource-grpc
```

```bash
php bin/hyperf.php gen:resource User --grpc
```

gRPC resources need to set the `message` class. This is achieved by overriding the `expect()` method of the resource class.

When the gRPC service returns, `toMessage()` must be called. This method returns an instantiated `message` class.

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

The default generated resource collection can support gRPC return by extending the `Hyperf\ResourceGrpc\GrpcResource` interface.

## Concept overview

> This is a high-level overview of resources and resource collections. It is strongly recommended that you read the rest of this document for an in-depth understanding of how to better customize and use resources.

Before diving into how to custom write your resources, let's take a look at how resources are used in the framework. A resource class representing a single model needs to be converted into JSON format. For example, now we have a simple `User` resource class:

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

Each resource class defines a `toArray` method which returns an array of properties that should be converted to JSON when sending the response. Note that here we can directly use the `$this` variable to access model properties. This is because the resource class will automatically proxy properties and methods to the underlying model for easy access. You can return defined resources in your controller:

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

### Resource Collection

You can use the `collection` method in a controller to create resource instances to return collections of multiple resources or paginated responses:

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

Of course, using the above method you will not be able to add any additional metadata to return with the collection. If you need a custom resource collection response, you need to create a dedicated resource to represent the collection:

```bash
php bin/hyperf.php gen:resource UserCollection
```

You can easily define any metadata you want returned in the response in the generated resource collection class:

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

You can return a defined collection of resources in your controller:

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

### Protected collection keys

When a resource collection is returned from a route, the collection's keys are reset so that they are in simple numerical order. However, a `preserveKeys` attribute can be added to a resource class to indicate whether collection keys should be preserved:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * A collection key indicating whether the resource should be preserved.
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

When the `preserveKeys` property is set to `true`, the keys of the collection will be protected:

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

### Custom basic resource class

Typically, the `$this->collection` property of a resource collection is automatically populated, resulting in a mapping of each item of the collection to its individual resource class. The single resource class is assumed to be the class name of the collection without the `Collection` string at the end.

For example, `UserCollection` maps a given user instance into a `User` resource. To customize this behavior, you can override the `$collects` property of the resource collection:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * collects properties define resource classes.
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

## write resources

> If you haven't read [Concept Overview](#Concept Overview), it is strongly recommended that you do so before continuing with this document.

Essentially, the role of resources is simple. They just need to convert a given model into an array. So every resource contains a `toArray` method to convert your model properties into an API-friendly array that can be returned to the user:

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

You can return an already defined resource in a controller:

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

### Association

If you wish to include associated resources in the response, you only need to add them to the array returned by the `toArray` method. In the following example, we will use the `collection` method of the `Post` resource to add the user's post to the resource response:

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

> If you only want to add an associated resource when the association is already loaded, see the related documentation.

### Resource Collection

A resource converts a single model into an array, and a resource collection converts a collection of multiple models into an array. All resources provide a `collection` method to generate a "temporary" collection of resources, so you don't have to write a resource collection class for each model type:

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

To customize the metadata of the returned collection, you still need to define a resource collection:

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

As with individual resources, you can return collections of resources directly in your controller:

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

### Data package

By default, when the resource response is converted to JSON, the top-level resource will be wrapped in the `data` key. So a typical resource collection response looks like this:

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

You can disable wrapping of top-level resources using the `withoutWrapping` method of the resource base class.

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

> The withoutWrapping method will only disable wrapping of the top-level resource, it will not remove the data key that you manually added to the resource collection. And it will only take effect in the current resource or resource collection, without affecting the global.

#### Wrapping nested resources

You are completely free to decide how resource associations are wrapped. If you want all resource collections to be wrapped in a `data` key, no matter how nested, then you need to define a resource collection class for each resource and wrap the returned collection in a `data` key.

Of course, you might worry that the top-level resource would then be wrapped in two `data` keys. Rest assured, components will never have your resources double-wrapped, so you don't have to worry about multiple nesting of transformed resource collections:

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

When returning a paginated collection in a resource response, even if you call the `withoutWrapping` method, the component will wrap your resource data in the `data` key. This is because the `meta` and `links` keys in the pagination response always contain pagination status information:

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

You can pass a pagination instance to the resource's collection method or a custom resource collection:

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

There are always `meta` and `links` keys in pagination responses that contain pagination status information:

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

### Conditional properties

Sometimes you may wish to add attributes to the resource response when a given condition is met. For example, you might want to add a value to the resource response if the current user is an "admin". In this case the component provides some helper methods to help you solve the problem. The `when` method can be used to conditionally add attributes to resource responses:

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

In the above example, the `secret` key will eventually be returned in the resource response only if the `isAdmin` method returns `true`. If this method returns `false`, the `secret` key will be deleted before the resource response is sent to the client. The `when` method allows you to avoid concatenating arrays with conditional statements and instead write your resources in a more elegant way.

The `when` method also accepts a closure as its second argument, from which the returned value is computed only if the given condition is `true`:

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

#### Conditional merge data

Sometimes, you may wish to add multiple attributes to the resource response when a given condition is met. In this case, you can use the `mergeWhen` method to add multiple properties to the response when a given condition is `true`:

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

Likewise, if the given condition is `false`, these attributes will be removed before the resource response is sent to the client.

> The `mergeWhen` method should not be used on arrays with mixed string and numeric keys. Also, it shouldn't be used in arrays of out-of-order numeric keys.

### Conditional association

In addition to adding properties conditionally, you can also conditionally include associations in your resource responses based on whether the model association is loaded. This allows you to decide in the controller which model associations to load, so that your resources can add them after the model associations are loaded.

Doing this will avoid the "N+1" query problem in your resources. You should use the `whenLoaded` method to conditionally load associations. To avoid loading unnecessary associations, this method accepts the name of the association rather than the association itself as its parameter:

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

In the above example, if the association is not loaded, the `posts` key will be deleted before the resource response is sent to the client.

#### Conditional intermediate table information

In addition to conditionally including associations in your resource responses, you can also conditionally add data from intermediate tables in many-to-many associations using the `whenPivotLoaded` method. The first parameter accepted by the `whenPivotLoaded` method is the name of the intermediate table. The second parameter is a closure that defines the value to return on the model if intermediate table information is available:

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

If your intermediate table uses accessors other than `pivot`, you can use the `whenPivotLoadedAs` method:

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

### Add metadata

Some JSON API standards require you to add metadata to resource and resource collection responses. This usually includes `links` for the resource or related resources, or some metadata about the resource itself. If you need to return additional metadata about the resource, just include them in the `toArray` method. For example, you may need to add `links` information when converting resource collections:

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

When adding extra metadata to your resource, you don't have to worry about overwriting the `links` or `meta` keys that are automatically added when returning paginated responses. Any other `links` you add will be merged with the `links` added by the pagination response.

#### top-level metadata

Sometimes you may wish to add certain metadata to the resource response when the resource is returned as a top-level resource. This usually includes meta information for the entire response. You can add a `with` method to your resource class to define metadata. This method should return an array of metadata that will be included in the resource response when the resource is rendered as a top-level resource:

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

#### Add metadata when constructing resources

You can also add top-level data when constructing a resource instance in a controller. All resources can use the `additional` method to accept an array of data that should be added to the resource response:

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

## Response resource

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

If you want to set the response header information, status code, etc., get the response object by calling the `toResponse()` method to set it.
