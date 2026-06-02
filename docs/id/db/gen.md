# Script Pembuatan Model

Hyperf menyediakan command untuk membuat model berdasarkan tabel database. Command ini menghasilkan model lewat `AST`, jadi saat Anda menambahkan method, Anda juga bisa memakai script ini untuk mereset model dengan mudah.

```bash
php bin/hyperf.php gen:model table_name
```

## Membuat Model

Parameter opsionalnya adalah sebagai berikut:

| Parameter | Tipe | Nilai Default | Keterangan |
| :---: | :---: | :---: | :---: |
| --pool | string | `default` | Connection pool, script akan membuat berdasarkan konfigurasi pool saat ini |
| --path | string | `app/Model` | Path model |
| --force-casts | bool | `false` | Apakah memaksa reset parameter `casts` |
| --prefix | string | String Kosong | Table prefix |
| --inheritance | string | `Model` | Parent class |
| --uses | string | `Hyperf\DbConnection\Model\Model` | Digunakan bersama dengan `inheritance` |
| --refresh-fillable | bool | `false` | Apakah me-refresh parameter `fillable` |
| --table-mapping | array | `[]` | Mapping nama tabel ke model, misalnya ['users:Account'] |
| --ignore-tables | array | `[]` | Tabel yang tidak perlu dibuatkan model, misalnya ['users'] |
| --with-comments | bool | `false` | Apakah menambahkan field comments |
| --property-case | int | `0` | Tipe field: 0 snake_case, 1 camelCase |

Saat mengonversi tipe field ke camelCase dengan `--property-case`, Anda juga perlu menambahkan `Hyperf\Database\Model\Concerns\CamelCase` secara manual ke model.

Konfigurasi yang sama juga bisa diatur di `databases.{pool}.commands.gen:model`:

> Semua tanda hubung perlu dikonversi menjadi garis bawah.

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // Abaikan konfigurasi lainnya
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
                'uses' => '',
                'refresh_fillable' => true,
                'table_mapping' => [],
                'with_comments' => true,
                'property_case' => ModelOption::PROPERTY_SNAKE_CASE,
            ],
        ],
    ],
];
```

Model yang dibuat adalah sebagai berikut:

```php
<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property $id
 * @property $name
 * @property $gender
 * @property $created_at
 * @property $updated_at
 */
class User extends Model
{
    /**
     * Tabel yang terkait dengan model.
     */
    protected ?string $table = 'user';

    /**
     * Atribut yang bisa diisi secara massal.
     */
    protected array $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * Atribut yang harus di-cast ke tipe native.
     */
    protected array $casts = ['id' => 'integer', 'gender' => 'integer'];
}
```

## Visitors

Framework menyediakan beberapa `Visitors` untuk memperluas kemampuan script. Cara pakainya mudah, tinggal tambahkan `Visitor` yang diinginkan ke konfigurasi `visitors`.

```php
<?php

declare(strict_types=1);

return [
    'default' => [
        // Abaikan konfigurasi lainnya
        'commands' => [
            'gen:model' => [
                'visitors' => [
                    Hyperf\Database\Commands\Ast\ModelRewriteKeyInfoVisitor::class
                ],
            ],
        ],
    ],
];
```

### Visitors Opsional

- Hyperf\Database\Commands\Ast\ModelRewriteKeyInfoVisitor

Visitor ini menghasilkan `$incrementing`, `$primaryKey`, dan `$keyType` sesuai primary key di database.

- Hyperf\Database\Commands\Ast\ModelRewriteSoftDeletesVisitor

Visitor ini mengecek apakah model memiliki soft delete fields berdasarkan konstanta `DELETED_AT`. Jika ada, ia menambahkan Trait `SoftDeletes`.

- Hyperf\Database\Commands\Ast\ModelRewriteTimestampsVisitor

Visitor ini otomatis menentukan apakah perlu mengaktifkan pencatatan `created and modified time` berdasarkan `created_at` dan `updated_at`.

- Hyperf\Database\Commands\Ast\ModelRewriteGetterSetterVisitor

Visitor ini menghasilkan `getter` dan `setter` berdasarkan field database.

## Overriding Visitor

Di Hyperf, saat menggunakan `gen:model`, secara default hanya `tinyint`, `smallint`, `mediumint`, `int`, dan `bigint` yang dideklarasikan sebagai `int`; `bool` dan `boolean` sebagai `boolean`; sisanya sebagai `string`. Ini bisa disesuaikan dengan override.

Sebagai berikut:

```php
<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property int $count
 * @property string $float_num // decimal
 * @property string $str
 * @property string $json
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserExt extends Model
{
    /**
     * Tabel yang terkait dengan model.
     */
    protected ?string $table = 'user_ext';

    /**
     * Atribut yang bisa diisi secara massal.
     */
    protected array $fillable = ['id', 'count', 'float_num', 'str', 'json', 'created_at', 'updated_at'];

    /**
     * Atribut yang harus di-cast ke tipe native.
     */
    protected array $casts = ['id' => 'integer', 'count' => 'integer', 'float_num' => 'string', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
```

Kita bisa memodifikasi perilaku ini dengan override `ModelUpdateVisitor`.

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Kernel\Visitor;

use Hyperf\Database\Commands\Ast\ModelUpdateVisitor as Visitor;
use Hyperf\Stringable\Str;

class ModelUpdateVisitor extends Visitor
{
    /**
     * Digunakan oleh atribut `casts`.
     */
    protected function formatDatabaseType(string $type): ?string
    {
        switch ($type) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                return 'integer';
            case 'decimal':
                // Set ke decimal dan set presisi yang sesuai
                return 'decimal:2';
            case 'float':
            case 'double':
            case 'real':
                return 'float';
            case 'bool':
            case 'boolean':
                return 'boolean';
            default:
                return null;
        }
    }

    /**
     * Digunakan oleh @property docs.
     */
    protected function formatPropertyType(string $type, ?string $cast): ?string
    {
        if (! isset($cast)) {
            $cast = $this->formatDatabaseType($type) ?? 'string';
        }

        switch ($cast) {
            case 'integer':
                return 'int';
            case 'date':
            case 'datetime':
                return '\Carbon\Carbon';
            case 'json':
                return 'array';
        }

        if (Str::startsWith($cast, 'decimal')) {
            // Jika cast adalah decimal, @property berubah menjadi string
            return 'string';
        }

        return $cast;
    }
}
```

Konfigurasikan mapping relationship di `dependencies.php`:

```php
<?php

return [
    Hyperf\Database\Commands\Ast\ModelUpdateVisitor::class => App\Kernel\Visitor\ModelUpdateVisitor::class,
];
```

Setelah menjalankan ulang `gen:model`, model yang sesuai adalah sebagai berikut:

```php
<?php

declare (strict_types=1);

namespace App\Model;

/**
 * @property int $id 
 * @property int $count 
 * @property string $float_num 
 * @property string $str 
 * @property string $json 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class UserExt extends Model
{
    /**
     * Tabel yang terkait dengan model.
     */
    protected ?string $table = 'user_ext';
    /**
     * Atribut yang bisa diisi secara massal.
     */
    protected array $fillable = ['id', 'count', 'float_num', 'str', 'json', 'created_at', 'updated_at'];
    /**
     * Atribut yang harus di-cast ke tipe native.
     */
    protected array $casts = ['id' => 'integer', 'count' => 'integer', 'float_num' => 'decimal:2', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
```
