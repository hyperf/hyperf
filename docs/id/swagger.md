# Hyperf Swagger

Komponen `hyperf/swagger` didasarkan pada `zircote/swagger-php` untuk pembungkusan (packaging).

Untuk daftar lengkap anotasi yang didukung, silakan lihat [OpenApi\Annotations namespace](https://github.com/zircote/swagger-php/blob/master/src/Annotations) atau [Dokumentasi situs](https://zircote.github.io/swagger-php/guide/annotations.html#arrays-and-objects).

## Instalasi

```
composer require hyperf/swagger
```

## Konfigurasi

```
php bin/hyperf.php vendor:publish hyperf/swagger
```

| Nama parameter | Peran |
| -------- | ------------------------------------------------------------ |
| enable | Mengaktifkan atau menonaktifkan generator dokumen Swagger |
| port | Nomor port generator dokumen Swagger |
| json_dir | Direktori tempat file JSON yang dihasilkan oleh Generator Dokumen Swagger disimpan |
| html | Path ke file HTML yang dihasilkan oleh generator dokumen Swagger |
| url | Path URL ke dokumen Swagger |
| auto_generate | Apakah akan menghasilkan dokumen Swagger secara otomatis |
| scan.paths | Path ke file API interface yang akan dipindai, bertipe array | 

## Menghasilkan dokumentasi

Jika `auto_generate` dikonfigurasi, dokumentasi akan dihasilkan secara
otomatis pada event inisialisasi framework, tanpa perlu memanggil:
```shell
php bin/hyperf.php gen:swagger
```

## Penggunaan

> Namespace SA yang muncul di bawah adalah `use Hyperf\Swagger\Annotation as SA`

Framework dapat menjalankan beberapa server, dan route dari masing-masing
server dapat dibedakan berdasarkan anotasi `SA\HyperfServer`, lalu menghasilkan
file swagger yang berbeda (menggunakan konfigurasi tersebut sebagai nama
file).

Ini dapat dikonfigurasi pada class controller atau method:
```php
#[SA\HyperfServer('http')]
```

```php
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

### Kombinasi validator

Pada anotasi `SA\Property` dan `SA\QueryParameter`, kita dapat menambahkan
parameter `rules`,

lalu bekerja sama dengan `SwaggerRequest` untuk memverifikasi validitas
parameter di dalam middleware.

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

### Mengganti Swagger Dashboard

Berikut ini adalah halaman default front-end Swagger. Anda dapat mengubah
konfigurasi `swagger.html` untuk menggantinya.

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

Sebagai contoh, ketika domain `unpkg.hyperf.wiki` tidak dapat diakses, Anda
dapat menggantinya menjadi `unpkg.com`.

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
