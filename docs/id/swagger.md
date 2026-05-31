# Hyperf Swagger

Komponen `hyperf/swagger` dikemas berdasarkan `zircote/swagger-php`.

Untuk daftar lengkap anotasi yang didukung, lihat [OpenApi\Annotations namespace](https://github.com/zircote/swagger-php/blob/master/src/Annotations) atau [situs dokumentasi](https://zircote.github.io/swagger-php/guide/annotations.html#arrays-and-objects).

## Instalasi

```bash
composer require hyperf/swagger
```

## Konfigurasi

```bash
php bin/hyperf.php vendor:publish hyperf/swagger
```

| Parameter | Deskripsi |
| --------- | ----------- |
| enable | Apakah akan mengaktifkan generator dokumentasi Swagger |
| port | Nomor port dari generator dokumentasi Swagger |
| json_dir | Direktori tempat file JSON yang dihasilkan oleh generator dokumentasi Swagger disimpan |
| html | Jalur file tempat file HTML yang dihasilkan oleh generator dokumentasi Swagger disimpan |
| url | Jalur URL dari dokumentasi Swagger |
| auto_generate | Apakah akan secara otomatis menghasilkan dokumentasi Swagger |
| scan.paths | Array dari jalur file API interface yang perlu di-scan |

## Menghasilkan Dokumentasi

Jika `auto_generate` dikonfigurasi, dokumentasi akan dihasilkan secara otomatis selama event inisialisasi framework, sehingga tidak perlu dipanggil secara manual.

```shell
php bin/hyperf.php gen:swagger
```

## Penggunaan

> Namespace SA yang disebutkan di bawah mengacu pada `use Hyperf\Swagger\Annotation as SA`.

Framework dapat menjalankan banyak server. Route dari setiap server dapat dibedakan dengan anotasi `SA\HyperfServer`, dan file Swagger yang berbeda dapat dihasilkan (menggunakan konfigurasi ini sebagai nama file).

Ini dapat dikonfigurasi pada class controller atau method:

```php
#[SA\HyperfServer('http')]
```

```php
#[SA\Post(path: '/test', summary: 'CONTOH POST form', tags: ['Api/Test'])]
#[SA\RequestBody(
    description: 'Parameter request',
    content: [
        new SA\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new SA\Schema(
                required: ['username', 'age'],
                properties: [
                    new SA\Property(property: 'username', description: 'Deskripsi field nama pengguna', type: 'string'),
                    new SA\Property(property: 'age', description: 'Deskripsi field usia', type: 'string'),
                    new SA\Property(property: 'city', description: 'Deskripsi field kota', type: 'string'),
                ]
            ),
        ),
    ],
)]
#[SA\Response(response: 200, description: 'Deskripsi nilai kembalian')]
public function test()
{
}
```

```php
#[SA\Get(path: '/test', summary: 'CONTOH GET', tags: ['Api/Test'])]
#[SA\QueryParameter(name: 'username', description: 'Deskripsi field nama pengguna', required: true, schema: new SA\Schema(type: 'string'))]
#[SA\QueryParameter(name: 'age', description: 'Deskripsi field usia', required: true, schema: new SA\Schema(type: 'string'))]
#[SA\QueryParameter(name: 'city', description: 'Deskripsi field kota', required: false, schema: new SA\Schema(type: 'string'))]
#[SA\Response(
    response: 200,
    description: 'Deskripsi nilai kembalian',
    content: new SA\JsonContent(
        example: '{"code":200,"data":[]}'
    ),
)]
public function list(ConversationRequest $request): array
{
}
```

### Bekerja dengan Validator

Dalam anotasi `SA\Property` dan `SA\QueryParameter`, kita dapat menambahkan parameter `rules`, lalu menggunakan `SwaggerRequest` pada middleware untuk memvalidasi parameter tersebut.

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
    #[SA\Post('/user/save', summary: 'Simpan informasi pengguna', tags: ['Manajemen Pengguna'])]
    #[SA\QueryParameter(name: 'token', description: 'Token autentikasi', type: 'string', rules: 'required|string')]
    #[SA\RequestBody(content: new SA\JsonContent(properties: [
        new SA\Property(property: 'nickname', description: 'Nama panggilan', type: 'integer', rules: 'required|string'),
        new SA\Property(property: 'gender', description: 'Jenis kelamin', type: 'integer', rules: 'required|integer|in:0,1,2'),
    ]))]
    #[SA\Response(response: '200', content: new SA\JsonContent(ref: '#/components/schemas/SavedSchema'))]
    public function info(SwaggerRequest $request)
    {
        $result = $this->service->save($request->all());

        return $this->response->success($result);
    }
}
```

### Mengganti Swagger UI

Berikut adalah halaman front-end default Swagger. Jika perlu dikustomisasi, ubah konfigurasi `swagger.html`.

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
      var r = window.location.search.substr(1).match(reg); // Ambil string setelah '?' di URL dan cocokkan dengan regular expression
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

Sebagai contoh, jika `unpkg.hyperf.wiki` tidak tersedia, Anda dapat menggantinya secara manual dengan `unpkg.com` dengan memodifikasi konfigurasi `config/autoload/swagger.php`.

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
      var r = window.location.search.substr(1).match(reg); // Ambil string setelah '?' di URL dan cocokkan dengan regular expression
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
