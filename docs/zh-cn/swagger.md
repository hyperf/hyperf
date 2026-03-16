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

> 以下出现的 SA 命名空间都为 `use Hyperf\Swagger\Annotation as SA`

框架可以启动多个 Server，每个 Server 的路由可以根据 `SA\Hyperferver` 注解来区分，并生成不同的 swagger 文件（以该配置作为文件名）

可以配置在控制器类或者方法上
```php
#[SA\HyperfServer('http')]
```

```php
#[SA\Post(path: '/test', summary: 'POST 表单示例', tags: ['Api/Test'])]
#[SA\RequestBody(
    description: '请求参数',
    content: [
        new SA\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new SA\Schema(
                required: ['username', 'age'],
                properties: [
                    new SA\Property(property: 'username', description: '用户名字段描述', type: 'string'),
                    new SA\Property(property: 'age', description: '年龄字段描述', type: 'string'),
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
#[SA\QueryParameter(name: 'age', description: '年龄字段描述', required: true, schema: new SA\Schema(type: 'string'))]
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

### 配合验证器

`SA\Property` 和 `SA\QueryParameter` 注解中，我们可以增加 `rules` 参数，然后配合 `SwaggerRequest` 即可在中间件中，验证参数是否合法。

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
    #[SA\QueryParameter(name: 'token', description: '鉴权 token', type: 'string', rules: 'required|string')]
    #[SA\RequestBody(content: new SA\JsonContent(properties: [
        new SA\Property(property: 'nickname', description: '昵称', type: 'integer', rules: 'required|string'),
        new SA\Property(property: 'gender', description: '性别', type: 'integer', rules: 'required|integer|in:0,1,2'),
    ]))]
    #[SA\Response(response: '200', content: new SA\JsonContent(ref: '#/components/schemas/SavedSchema'))]
    public function info(SwaggerRequest $request)
    {
        $result = $this->service->save($request->all());

        return $this->response->success($result);
    }
}
```

### 替换 Swagger 面板

以下是默认的 Swagger 前端页面，如果需要自定义，则可以修改 `swagger.html` 配置

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
      var r = window.location.search.substr(1).match(reg); //获取url中"?"符后的字符串并正则匹配
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

例如，当 `unpkg.hyperf.wiki` 出现故障时，可以手动替换为 `unpkg.com`，手动修改 `config/autoload/swagger.php` 配置

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
      var r = window.location.search.substr(1).match(reg); //获取url中"?"符后的字符串并正则匹配
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
