# GraphQL

GraphQL 组件对 [thecodingmachine/graphqlite](https://github.com/thecodingmachine/graphqlite) 进行抽象。

## 安装

```bash
composer require hyperf/graphql
```

## 快速开始

### 简单查询
```php
<?php

namespace App\Controller;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Hyperf\Di\Annotation\Inject;
use Hyperf\GraphQL\Annotation\Query;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class GraphQLController
{
    #[Inject]
    protected Schema $schema;

    #[PostMapping(path: "/graphql")]
    public function test(RequestInterface $request)
    {
        $rawInput = $request->getBody()->getContents();
        $input = json_decode($rawInput, true);
        $query = $input['query'];
        $variableValues = isset($input['variables']) ? $input['variables'] : null;
        return GraphQL::executeQuery($this->schema, $query, null, null, $variableValues)->toArray();
    }

    #[Query]
    public function hello(string $name): string
    {
        return $name;
    }
}
```
查询:
```graphql
{
    hello(name: "graphql")
}
```
响应:
```json
{
    "data": {
        "hello": "graphql"
    }
}
```

### 类型映射

```php
<?php
namespace App\Model;

use Hyperf\GraphQL\Annotation\Type;
use Hyperf\GraphQL\Annotation\Field;

#[Type]
class Product
{
    protected $name;
    protected $price;

    public function __construct(string $name, float $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    #[Field]
    public function getName(): string
    {
        return $this->name;
    }

    #[Field]
    public function getPrice(): ?float
    {
        return $this->price;
    }
}
```

在 `GraphQLController` 中加入

```php
<?php
use App\Model\Product;
use Hyperf\GraphQL\Annotation\Query;

#[Query]
public function product(string $name, float $price): Product
{
    return new Product($name, $price);
}
```

查询:
```graphql
{
    hello(name: "graphql")
    product(name: "goods", price: 156.5) {
        name
        price
    }
}
```

响应:
```json
{
    "data": {
        "hello": "graphql",
        "product": {
            "name": "goods",
            "price": 156.5
        }
    }
}
```

更多使用方法可以查看 [GraphQLite](https://graphqlite.thecodingmachine.io/docs/queries) 的文档。
