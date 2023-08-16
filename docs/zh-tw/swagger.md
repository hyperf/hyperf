# Hyperf Swagger

hyperf/swagger 元件基於 zircote/swagger-php 進行封裝

如需完整支援的註釋列表，請檢視[OpenApi\Annotations 名稱空間](https://github.com/zircote/swagger-php/blob/master/src/Annotations)或[文件網站](https://zircote.github.io/swagger-php/guide/annotations.html#arrays-and-objects)


## 安裝

```
composer require hyperf/swagger
```

## 配置 

```
php bin/hyperf.php vendor:publish hyperf/swagger
```

| 引數名      | 作用                                                         |
| -------- | ------------------------------------------------------------ |
| enable   | 是否啟用 Swagger 文件生成器                                     |
| port     | Swagger 文件生成器的埠號                                    |
| json_dir | Swagger 文件生成器生成的 JSON 檔案儲存目錄                       |
| html     | Swagger 文件生成器生成的 HTML 檔案儲存路徑                       |
| url      | Swagger 文件的 URL 路徑                                         |
| auto_generate | 是否自動生成 Swagger 文件                                     |
| scan.paths | 需要掃描的 API 介面檔案所在的路徑，一個數組 | 

## 生成文件

如果配置了 `auto_generate` ，在框架初始化的事件中便會自動生成文件，無需再次呼叫
```shell
php bin/hyperf.php gen:swagger
```

## 使用

> 以下出現的 OA 名稱空間都為 `use Hyperf\Swagger\Annotation as OA`

框架可以啟動多個 Server，每個 Server 的路由可以根據 `OA\Hyperferver` 註解來區分，並生成不同的 swagger 檔案（以該配置作為檔名）

可以配置在控制器類或者方法上
```php
#[OA\HyperfServer('http')]
```

```php
#[OA\Post(path: '/test', summary: 'POST 表單示例', tags: ['Api/Test'])]
#[OA\RequestBody(
    description: '請求引數',
    content: [
        new OA\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new OA\Schema(
                required: ['username', 'age'],
                properties: [
                    new OA\Property(property: 'username', description: '使用者名稱欄位描述', type: 'string'),
                    new OA\Property(property: 'age', description: '年齡欄位描述', type: 'string'),
                    new OA\Property(property: 'city', description: '城市欄位描述', type: 'string'),
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
#[OA\Parameter(name: 'username', description: '使用者名稱欄位描述', in : 'query', required: true, schema: new OA\Schema(type: 'string'))]
#[OA\Parameter(name: 'age', description: '年齡欄位描述', in : 'query', required: true, schema: new OA\Schema(type: 'string'))]
#[OA\Parameter(name: 'city', description: '城市欄位描述', in : 'query', required: false, schema: new OA\Schema(type: 'string'))]
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
