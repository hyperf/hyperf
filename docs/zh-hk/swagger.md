# Hyperf Swagger

hyperf/swagger 組件基於 zircote/swagger-php 進行封裝

如需完整支持的註釋列表，請查看[OpenApi\Annotations 命名空間](https://github.com/zircote/swagger-php/blob/master/src/Annotations)或[文檔網站](https://zircote.github.io/swagger-php/guide/annotations.html#arrays-and-objects)


## 安裝

```
composer require hyperf/swagger
```

## 配置 

```
php bin/hyperf.php vendor:publish hyperf/swagger
```

| 參數名      | 作用                                                         |
| -------- | ------------------------------------------------------------ |
| enable   | 是否啓用 Swagger 文檔生成器                                     |
| port     | Swagger 文檔生成器的端口號                                    |
| json_dir | Swagger 文檔生成器生成的 JSON 文件保存目錄                       |
| html     | Swagger 文檔生成器生成的 HTML 文件保存路徑                       |
| url      | Swagger 文檔的 URL 路徑                                         |
| auto_generate | 是否自動生成 Swagger 文檔                                     |
| scan.paths | 需要掃描的 API 接口文件所在的路徑，一個數組 | 

## 生成文檔

如果配置了 `auto_generate` ，在框架初始化的事件中便會自動生成文檔，無需再次調用
```shell
php bin/hyperf.php gen:swagger
```

## 使用

> 以下出現的 OA 命名空間都為 `use Hyperf\Swagger\Annotation as OA`

框架可以啓動多個 Server，每個 Server 的路由可以根據 `OA\Hyperferver` 註解來區分，並生成不同的 swagger 文件（以該配置作為文件名）

可以配置在控制器類或者方法上
```php
#[OA\HyperfServer('http')]
```

```php
#[OA\Post(path: '/test', summary: 'POST 表單示例', tags: ['Api/Test'])]
#[OA\RequestBody(
    description: '請求參數',
    content: [
        new OA\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new OA\Schema(
                required: ['username', 'age'],
                properties: [
                    new OA\Property(property: 'username', description: '用户名字段描述', type: 'string'),
                    new OA\Property(property: 'age', description: '年齡字段描述', type: 'string'),
                    new OA\Property(property: 'city', description: '城市字段描述', type: 'string'),
                ]
            ),
        ),
    ],
)]
#[OA\Response(response: 200, description: '返回值的描述')]
public function test()
{
}
```

```php
#[OA\Get(path: '/test', summary: 'GET 示例', tags: ['Api/Test'])]
#[OA\Parameter(name: 'username', description: '用户名字段描述', in : 'query', required: true, schema: new OA\Schema(type: 'string'))]
#[OA\Parameter(name: 'age', description: '年齡字段描述', in : 'query', required: true, schema: new OA\Schema(type: 'string'))]
#[OA\Parameter(name: 'city', description: '城市字段描述', in : 'query', required: false, schema: new OA\Schema(type: 'string'))]
#[OA\Response(
    response: 200,
    description: '返回值的描述',
    content: new OA\JsonContent(
        example: '{"code":200,"data":[]}'
    ),
)]
public function list(ConversationRequest $request): array
{
}
```
