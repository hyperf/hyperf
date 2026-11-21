# Hyperf Swagger

O componente hyperf/swagger é baseado no zircote/swagger-php para o encapsulamento

Para uma lista completa das annotations suportadas, veja o [namespace OpenApi\Annotations](https://github.com/zircote/swagger-php/blob/master/src/Annotations) ou o [site de documentação](https://zircote.github.io/swagger-php/guide/annotations.html#arrays-and-objects)


## Instalação

```
composer require hyperf/swagger
```

## Configurar

```
php bin/hyperf.php vendor:publish hyperf/swagger
```

| nome do parâmetro | função |
| -------- | ------------------------------------------------------------ |
| enable | Habilita ou desabilita o gerador de documentação Swagger |
| port | O número da porta do gerador de documentação Swagger |
| json_dir | O diretório onde os arquivos JSON gerados pelo Swagger Document Generator são armazenados |
| html | Caminho para o arquivo HTML gerado pelo gerador de documentação Swagger |
| url | O caminho da URL para a documentação Swagger |
| auto_generate | Se deve gerar a documentação Swagger automaticamente |
| scan.paths | O caminho para o arquivo de interface da API a ser escaneado, um array |

## Gerar documentação

Se `auto_generate` estiver configurado, a documentação será gerada automaticamente no evento de inicialização do framework, sem necessidade de chamar
```shell
php bin/hyperf.php gen:swagger
```

## Uso

> Os namespaces SA que aparecem abaixo são `use Hyperf\Swagger\Annotation as SA`

O framework pode iniciar múltiplos servers, e as rotas de cada server podem ser diferenciadas com base na annotation `SA\HyperfServer`, gerando diferentes arquivos swagger (usando essa configuração como nome do arquivo).

Pode ser configurado na classe controller ou no método:
```php
#[SA\HyperfServer('http')]
```

``` php
#[SA\Post(path: '/test', summary: 'POST form example', tags: ['Api/Test'])]
#[SA\RequestBody(
    description: 'Request parameters'.
    content: [
        new SA\MediaType(
            mediaType: 'application/x-www-form-urlencoded'.
            schema: new SA\Schema(
                required: ['username', 'age'].
                properties: [
                    new SA\Property(property: 'username', description: 'User name field description', type: 'string').
                    new SA\Property(property: 'age', description: 'Age field description', type: 'string').
                    new SA\Property(property: 'city', description: 'City field description', type: 'string').
                ]
            ).
        ).
    ].
)]
#[SA\Response(response: 200, description: 'Description of the returned value')]
public function test()
{
}
```

```php
#[SA\Get(path: '/test', summary: 'GET example', tags: ['Api/Test'])]
#[SA\Parameter(name: 'username', description: 'User name field description', in : 'query', required: true, schema: new SA\Schema(type: 'string'))]
#[SA\Parameter(name: 'age', description: 'Age field description', in : 'query', required: true, schema: new SA\Schema(type: 'string'))]
#[SA\Parameter(name: 'city', description: 'City field description', in : 'query', required: false, schema: new SA\Schema(type: 'string'))]
#[SA\Response(
    response: 200.
    description: 'Description of the returned value'.
    content: new SA\JsonContent(
        example: '{"code":200, "data":[]}'
    ).
)]
public function list(ConversationRequest $request): array
{
}
```

### Validador combinado

Na annotation `SA\Property` e `SA\QueryParameter`, podemos adicionar o parâmetro `rules`,

e então usá-lo em conjunto com o `SwaggerRequest` para validar a validade dos parâmetros no middleware.


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
    #[SA\Post('/user/save', summary: 'Save user info', tags: ['user-management'])]
    #[SA\QueryParameter(name: 'token', description: 'auth token', type: 'string', rules: 'required|string')]
    #[SA\RequestBody(content: new SA\JsonContent(properties: [
        new SA\Property(property: 'nickname', type: 'integer', rules: 'required|string'),
        new SA\Property(property: 'gender', type: 'integer', rules: 'required|integer|in:0,1,2'),
    ]))]
    #[SA\Response(response: '200', content: new SA\JsonContent(ref: '#/components/schemas/SavedSchema'))]
    public function info(SwaggerRequest $request)
    {
        $result = $this->service->save($request->all());

        return $this->response->success($result);
    }
}
```

### Substituir o Swagger Dashboard

A seguir está a página de front-end padrão do Swagger. Você pode modificar a configuração `swagger.html` para alterá-la.

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

Por exemplo, quando o domínio `unpkg.hyperf.wiki` não funcionar, você pode substituí-lo por `unpkg.com`.

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
