# Hyperf Swagger

Swagger/OpenAPI integration for [Hyperf](https://hyperf.io). The package scans PHP attributes on Hyperf controllers and generates OpenAPI JSON documents, with optional YAML output.

## Requirements

- PHP 8.2 or later
- Hyperf 3.2
- `zircote/swagger-php` 6.x (the compatibility matrix also covers 4.x)

## Installation

```bash
composer require hyperf/swagger
php bin/hyperf.php vendor:publish hyperf/swagger
```

If request validation is needed, install Hyperf Validation:

```bash
composer require hyperf/validation
```

## Annotating a controller

The value passed to `HyperfServer` must match a key in `swagger.server`.

```php
<?php

namespace App\Controller;

use Hyperf\Swagger\Annotation as OA;
use Hyperf\Swagger\Request\SwaggerRequest;

#[OA\Info(title: 'Example API', version: '1.0.0')]
#[OA\HyperfServer('http')]
class UserController
{
    #[OA\Get('/users', summary: 'List users', tags: ['users'])]
    public function index(SwaggerRequest $request): array
    {
        return [];
    }
}
```

The package provides `Get`, `Post`, `Put`, `Patch`, `Delete`, `Head`, and `Options` attributes, together with OpenAPI attributes such as `Info`, `Schema`, `Response`, `RequestBody`, `JsonContent`, and `Property`.

## Request validation

Put validation rules on the corresponding Swagger attributes. `SwaggerRequest` reads them through `ValidationCollector` and exposes them to Hyperf Validation.

```php
#[OA\Post('/users', summary: 'Create user')]
#[OA\QueryParameter(
    name: 'name',
    required: true,
    rules: 'required|string|max:50',
    attribute: 'user name',
)]
public function create(SwaggerRequest $request): array
{
    return [];
}
```

Rules can also be placed on `OA\Property` inside a request body.

## Configuration

The published configuration is located at `config/autoload/swagger.php`:

```php
return [
    'enable' => true,
    'port' => 9500,
    'url' => '/swagger',
    'auto_generate' => true,
    'json_dir' => BASE_PATH . '/storage/swagger',
    'yaml_dir' => null,
    'scan' => [
        'paths' => null,
    ],
    'server' => [
        'http' => [
            'servers' => [
                ['url' => 'http://127.0.0.1:9501'],
            ],
            'info' => [
                'title' => 'Example API',
                'version' => '1.0.0',
            ],
        ],
    ],
];
```

The generated files are named `<server>.json`. Set `yaml_dir` to generate `<server>.yaml` as well.

## Generating the specification

When `auto_generate` is enabled, generation runs during application boot. It can also be triggered explicitly:

```bash
php bin/hyperf.php gen:swagger
php bin/hyperf.php gen:swagger-schema --model App\\Model\\User
```

With the default server configuration, Swagger UI is available at:

```text
http://127.0.0.1:9500/swagger?search=/http.json
```

## Compatibility tests

The component is tested on PHP 8.2, 8.4, and 8.5 with both swagger-php 4.x and 6.x. Run the component tests from the monorepo with:

```bash
vendor/bin/phpunit src/swagger
```

The 6.x path is the default dependency. The 4.x path is retained as a compatibility test and verifies backward compatibility with existing installations.

## License

MIT
