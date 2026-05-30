# Skrip Pembuatan Model

Hyperf menyediakan perintah untuk membuat model, dan Anda dapat dengan mudah
membuat model yang sesuai berdasarkan tabel data. Perintah ini menghasilkan
model melalui `AST`, sehingga ketika Anda menambahkan metode tertentu, Anda
juga dapat dengan mudah mereset model dengan skrip tersebut.

```bash
php bin/hyperf.php gen:model table_name
```

## Membuat model

Parameter opsional adalah sebagai berikut:

|     Parameter      |  Tipe  |             Default               |                                            Keterangan                                          |
|:------------------:|:------:|:---------------------------------:|:----------------------------------------------------------------------------------------------:|
|       --pool       | string |             `default`             | Connection pool, skrip akan dibuat berdasarkan konfigurasi connection pool saat ini            |
|       --path       | string |            `app/Model`            | path model                                                                                     |
|   --force-casts    |  bool  |              `false`              | Apakah akan memaksa reset parameter `casts`                                                    |
|      --prefix      | string |           string kosong           | prefix tabel                                                                                   |
|   --inheritance    | string |              `Model`              | Parent class                                                                                   |
|       --uses       | string | `Hyperf\DbConnection\Model\Model` | Gunakan bersama dengan `inheritance`                                                           |
| --refresh-fillable |  bool  |              `false`              | Apakah akan me-refresh parameter `fillable`                                                    |
|  --table-mapping   | array  |               `[]`                | Tambahkan hubungan mapping untuk nama tabel -> model seperti ['users:Account']                 |
|  --ignore-tables   | array  |               `[]`                | Tidak perlu menghasilkan model untuk nama tabel tertentu, misal ['users']                      |
|  --with-comments   |  bool  |              `false`              | Apakah akan menambahkan komentar field                                                         |
|  --property-case   |  int   |                `0`                | Tipe Field: 0 Snakecase, 1 CamelCase                                                           |

Ketika menggunakan `--property-case` untuk mengubah tipe field menjadi camel
case, Anda juga perlu menambahkan `Hyperf\Database\Model\Concerns\CamelCase` ke
model secara manual.
Konfigurasi yang sesuai juga dapat diatur dalam
`databases.{pool}.commands.gen:model`, sebagai berikut:

> Semua tanda hubung harus diubah menjadi underscore

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Commands\ModelOption;

return [
    'default' => [
        // Ignore other configurations
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
     * The table associated with the model.
     */
    protected ?string $table = 'user';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'gender' => 'integer'];
}
```

## Visitor

Framework ini menyediakan beberapa `Visitor` bagi pengguna untuk memperluas
kemampuan skrip. Penggunaannya sangat sederhana, cukup tambahkan `Visitor` yang
sesuai dalam konfigurasi `visitors`.

```php
<?php

declare(strict_types=1);

return [
    'default' => [
        // Ignore other configurations
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

### Visitor Opsional

- Hyperf\Database\Commands\Ast\ModelRewriteKeyInfoVisitor

`Visitor` ini dapat menghasilkan `$incrementing`, `$primaryKey`, dan `$keyType`
yang sesuai berdasarkan primary key di database.

- Hyperf\Database\Commands\Ast\ModelRewriteSoftDeletesVisitor

`Visitor` ini dapat menilai apakah model berisi field soft delete berdasarkan
konstanta `DELETED_AT`, dan jika ya, menambahkan Trait `SoftDeletes` yang
sesuai.

- Hyperf\Database\Commands\Ast\ModelRewriteTimestampsVisitor

`Visitor` ini dapat secara otomatis menentukan, berdasarkan `created_at` dan
`updated_at`, apakah akan mengaktifkan pencatatan default untuk
`waktu pembuatan dan modifikasi` (created and modified times).

- Hyperf\Database\Commands\Ast\ModelRewriteGetterSetterVisitor

`Visitor` ini dapat menghasilkan `getter` dan `setter` yang sesuai berdasarkan
field database.

## Override Visitor

Dalam framework Hyperf, saat `gen:model` digunakan, secara default hanya tipe
`tinyint, smallint, mediumint, int, bigint` yang dideklarasikan sebagai tipe
int, `bool, boolean` dideklarasikan sebagai tipe boolean, sedangkan tipe data
lainnya secara default menjadi `string`. Anda dapat melakukan override untuk
menyesuaikannya.

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
     * The table associated with the model.
     */
    protected ?string $table = 'user_ext';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'count', 'float_num', 'str', 'json', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'count' => 'integer', 'float_num' => 'string', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

```

Pada titik ini, kita dapat memodifikasi fitur ini dengan meng-override
`ModelUpdateVisitor`.

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
                // Set to decimal, and set the corresponding precision
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
            // If cast is decimal, @property is changed to string
            return 'string';
        }

        return $cast;
    }
}
```

Konfigurasikan hubungan pemetaan (mapping) pada `dependencies.php`

```php
<?php

return [
    Hyperf\Database\Commands\Ast\ModelUpdateVisitor::class => App\Kernel\Visitor\ModelUpdateVisitor::class,
];

```

Setelah menjalankan kembali `gen:model`, model yang sesuai adalah sebagai
berikut:

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
     * The table associated with the model.
     */
    protected ?string $table = 'user_ext';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'count', 'float_num', 'str', 'json', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'count' => 'integer', 'float_num' => 'decimal:2', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
```
