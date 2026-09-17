# Swagger no Hyperf

O componente hyperf/swagger é baseado no zircote/swagger-php para empacotamento.

Para a lista completa de anotações suportadas, veja o [namespace OpenApi\\Annotations](https://github.com/zircote/swagger-php/blob/master/src/Annotations) ou o [site de documentação](https://zircote.github.io/swagger-php/guide/annotations.html#arrays-and-objects).


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
| port | Número da porta do gerador de documentação Swagger |
| json_dir | Diretório onde os arquivos JSON gerados pelo Swagger Document Generator são armazenados |
| html | Caminho para o arquivo HTML gerado pelo gerador de documentação Swagger |
| url | Caminho de URL do documento Swagger |
| auto_generate | Se deve gerar automaticamente a documentação Swagger |
| scan.paths | Caminhos dos arquivos de interface de API a serem escaneados (array) |

## Gerar documentação

Se `auto_generate` estiver configurado, a documentação será gerada automaticamente no evento de inicialização do framework, sem necessidade de chamar:
```shell
php bin/hyperf.php gen:swagger
```

## Uso

> O namespace SA que aparece abaixo é `use Hyperf\\Swagger\\Annotation as SA`

O framework pode iniciar múltiplos servidores, e as rotas de cada servidor podem ser diferenciadas com base na anotação `SA\\HyperfServer`, gerando arquivos swagger diferentes (usando essa configuração como nome do arquivo).

Isso pode ser configurado na classe do controller ou no método:
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

Nas anotações `SA\\Property` e `SA\\QueryParameter`, podemos adicionar o parâmetro `rules`,

e então, em conjunto com `SwaggerRequest`, validar a validade dos parâmetros no middleware.


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

### Substituir o dashboard do Swagger

A seguir está a página padrão de front-end do Swagger. Você pode modificar a configuração `swagger.html` para alterá-la.

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
      var r = window.location.search.substr(1).match(reg); //èŽ·å–urlä¸­"?"ç¬¦åŽçš„å­—ç¬¦ä¸²å¹¶æ­£åˆ™åŒ¹é…
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
      var r = window.location.search.substr(1).match(reg); //èŽ·å–urlä¸­"?"ç¬¦åŽçš„å­—ç¬¦ä¸²å¹¶æ­£åˆ™åŒ¹é…
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

