# Hyperf Swagger

hyperf/swagger 组件基于 zircote/swagger-php 进行封装

如需完整支持的注释列表，请查看[OpenApi\Annotations 命名空间](https://github.com/zircote/swagger-php/blob/master/src/Annotations)或[文档网站](https://zircote.github.io/swagger-php/guide/annotations.html#arrays-and-objects)


## 安装

```
composer require hyperf/swagger
```

## 配置 

```
php bin/hyperf.php vendor:publish hyperf/swagger
```

| 参数名      | 作用                                                         |
| -------- | ------------------------------------------------------------ |
| enable   | 是否启用 Swagger 文档生成器                                     |
| port     | Swagger 文档生成器的端口号                                    |
| json_dir | Swagger 文档生成器生成的 JSON 文件保存目录                       |
| html     | Swagger 文档生成器生成的 HTML 文件保存路径                       |
| url      | Swagger 文档的 URL 路径                                         |
| auto_generate | 是否自动生成 Swagger 文档                                     |
| scan.paths | 需要扫描的 API 接口文件所在的路径，一个数组 | 

## 生成文档

如果配置了 `auto_generate` ，在框架初始化的事件中便会自动生成文档，无需再次调用
```shell
php bin/hyperf.php gen:swagger
```

## 使用

> 以下出现的 OA 命名空间都为 `use Hyperf\Swagger\Annotation as OA`

框架可以启动多个 Server，每个 Server 的路由可以根据 `OA\Hyperferver` 注解来区分，并生成不同的 swagger 文件（以该配置作为文件名）

可以配置在控制器类或者方法上
```php
#[OA\HyperfServer('http')]
```

```php
#[OA\Post(path: '/test', summary: 'POST 表单示例', tags: ['Api/Test'])]
#[OA\RequestBody(
    description: '请求参数',
    content: [
        new OA\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new OA\Schema(
                required: ['username', 'age'],
                properties: [
                    new OA\Property(property: 'username', description: '用户名字段描述', type: 'string'),
                    new OA\Property(property: 'age', description: '年龄字段描述', type: 'string'),
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
#[OA\Parameter(name: 'age', description: '年龄字段描述', in : 'query', required: true, schema: new OA\Schema(type: 'string'))]
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
