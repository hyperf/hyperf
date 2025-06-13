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

> 以下出現的 SA 命名空間都為 `use Hyperf\Swagger\Annotation as SA`

框架可以啓動多個 Server，每個 Server 的路由可以根據 `SA\Hyperferver` 註解來區分，並生成不同的 swagger 文件（以該配置作為文件名）

可以配置在控制器類或者方法上
```php
#[SA\HyperfServer('http')]
```

```php
#[SA\Post(path: '/test', summary: 'POST 表單示例', tags: ['Api/Test'])]
#[SA\RequestBody(
    description: '請求參數',
    content: [
        new SA\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new SA\Schema(
                required: ['username', 'age'],
                properties: [
                    new SA\Property(property: 'username', description: '用户名字段描述', type: 'string'),
                    new SA\Property(property: 'age', description: '年齡字段描述', type: 'string'),
                    new SA\Property(property: 'city', description: '城市字段描述', type: 'string'),
                ]
            ),
        ),
    ],
)]
#[SA\Response(response: 200, description: '返回值的描述')]
public function test()
{
}
```

```php
#[SA\Get(path: '/test', summary: 'GET 示例', tags: ['Api/Test'])]
#[SA\QueryParameter(name: 'username', description: '用户名字段描述', required: true, schema: new SA\Schema(type: 'string'))]
#[SA\QueryParameter(name: 'age', description: '年齡字段描述', required: true, schema: new SA\Schema(type: 'string'))]
#[SA\QueryParameter(name: 'city', description: '城市字段描述', required: false, schema: new SA\Schema(type: 'string'))]
#[SA\Response(
    response: 200,
    description: '返回值的描述',
    content: new SA\JsonContent(
        example: '{"code":200,"data":[]}'
    ),
)]
public function list(ConversationRequest $request): array
{
}
```

### 配合驗證器

`SA\Property` 和 `SA\QueryParameter` 註解中，我們可以增加 `rules` 參數，然後配合 `SwaggerRequest` 即可在中間件中，驗證參數是否合法。

```php
<?php
namespace App\Controller;

use App\Schema\SavedSchema;
use Hyperf\Swagger\Request\SwaggerRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;

#[SA\HyperfServer(name: 'http')]
class CardController extends Controller
{
    #[SA\Post('/user/save', summary: '保存用户信息', tags: ['用户管理'])]
    #[SA\QueryParameter(name: 'token', description: '鑑權 token', type: 'string', rules: 'required|string')]
    #[SA\RequestBody(content: new SA\JsonContent(properties: [
        new SA\Property(property: 'nickname', description: '暱稱', type: 'integer', rules: 'required|string'),
        new SA\Property(property: 'gender', description: '性別', type: 'integer', rules: 'required|integer|in:0,1,2'),
    ]))]
    #[SA\Response(response: '200', content: new SA\JsonContent(ref: '#/components/schemas/SavedSchema'))]
    public function info(SwaggerRequest $request)
    {
        $result = $this->service->save($request->all());

        return $this->response->success($result);
    }
}
```

### 替換 Swagger 面板

以下是默認的 Swagger 前端頁面，如果需要自定義，則可以修改 `swagger.html` 配置

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta
      name="description"
      content="SwaggerUI"
    />
    <title>SwaggerUI</title>
    <link rel="stylesheet" href="https://unpkg.hyperf.wiki/swagger-ui-dist@4.5.0/swagger-ui.css" />
  </head>
  <body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.hyperf.wiki/swagger-ui-dist@4.5.0/swagger-ui-bundle.js" crossorigin></script>
  <script src="https://unpkg.hyperf.wiki/swagger-ui-dist@4.5.0/swagger-ui-standalone-preset.js" crossorigin></script>
  <script>
    window.onload = () => {
      window.ui = SwaggerUIBundle({
        url: GetQueryString("search"),
        dom_id: '#swagger-ui',
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        layout: "StandaloneLayout",
      });
    };
    function GetQueryString(name) {
      var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
      var r = window.location.search.substr(1).match(reg); //獲取url中"?"符後的字符串並正則匹配
      var context = "";
      if (r != null)
        context = decodeURIComponent(r[2]);
      reg = null;
      r = null;
      return context == null || context == "" || context == "undefined" ? "/http.json" : context;
    }
  </script>
  </body>
</html>
```

例如，當 `unpkg.hyperf.wiki` 出現故障時，可以手動替換為 `unpkg.com`，手動修改 `config/autoload/swagger.php` 配置

```php
<?php

declare(strict_types=1);

return [
    'enable' => true,
    'port' => 9500,
    'json_dir' => BASE_PATH . '/storage/swagger',
    'html' => <<<'HTML'
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta
      name="description"
      content="SwaggerUI"
    />
    <title>SwaggerUI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui.css" />
  </head>
  <body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui-bundle.js" crossorigin></script>
  <script src="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui-standalone-preset.js" crossorigin></script>
  <script>
    window.onload = () => {
      window.ui = SwaggerUIBundle({
        url: GetQueryString("search"),
        dom_id: '#swagger-ui',
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        layout: "StandaloneLayout",
      });
    };
    function GetQueryString(name) {
      var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
      var r = window.location.search.substr(1).match(reg); //獲取url中"?"符後的字符串並正則匹配
      var context = "";
      if (r != null)
        context = decodeURIComponent(r[2]);
      reg = null;
      r = null;
      return context == null || context == "" || context == "undefined" ? "/http.json" : context;
    }
  </script>
  </body>
</html>
HTML,
    'url' => '/swagger',
    'auto_generate' => true,
    'scan' => [
        'paths' => null,
    ],
];

```
