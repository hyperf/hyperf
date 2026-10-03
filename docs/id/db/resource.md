# API Resource Constructor

> Mendukung resource extensions yang mengembalikan response Grpc.

## Introduction

Saat membangun API, Anda sering butuh layer transformasi untuk menghubungkan Model dengan response JSON yang dikembalikan ke pengguna. Resource classes memungkinkan Anda mentransformasi model dan koleksi model menjadi JSON secara intuitif dan langsung.

## Installation

```bash
composer require hyperf/resource
```

## Generating Resources

Anda dapat menggunakan perintah `gen:resource` untuk menghasilkan resource class. Secara default, resource yang dihasilkan ditempatkan di folder `app/Resource` aplikasi. Resource mewarisi dari class `Hyperf\Resource\Json\JsonResource`:

```bash
php bin/hyperf.php gen:resource User
```

### Resource Collections

Selain menghasilkan resource untuk mentransformasi satu model, Anda juga dapat menghasilkan resource collection untuk mentransformasi kumpulan model. Ini memungkinkan Anda untuk menyertakan link dan meta-informasi lain yang terkait dengan resource tertentu dalam response.

Anda perlu menambahkan flag `--collection` saat menghasilkan resource untuk membuat resource collection. Atau, Anda dapat menyertakan `Collection` langsung di nama resource untuk menunjukkan bahwa resource collection harus dibuat. Resource collection mewarisi dari class `Hyperf\Resource\Json\ResourceCollection`:

```bash
php bin/hyperf.php gen:resource Users --collection

php bin/hyperf.php gen:resource UserCollection
```

## gRPC Resources

> Membutuhkan `hyperf/resource-grpc` untuk diinstal.

```bash
composer require hyperf/resource-grpc
```

```bash
php bin/hyperf.php gen:resource User --grpc
```

gRPC resources perlu mengatur class `message`, yang diimplementasikan dengan menimpa method `expect()` dari resource class.

Ketika service gRPC mengembalikan response, Anda harus memanggil `toMessage()`. Method ini akan mengembalikan class `message` yang telah diinstansiasi.

```php
<?php
namespace HyperfTest\ResourceGrpc\Stubs\Resources;

use Hyperf\ResourceGrpc\GrpcResource;
use HyperfTest\ResourceGrpc\Stubs\Grpc\HiReply;

class HiReplyResource extends GrpcResource
{
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'user' => HiUserResource::make($this->user),
        ];
    }

    public function expect(): string
    {
        return HiReply::class;
    }
}
```

Resource collection yang dihasilkan secara default dapat dibuat mendukung pengembalian gRPC dengan mewarisi interface `Hyperf\ResourceGrpc\GrpcResource`.

## Conceptual Overview

> Ini adalah gambaran tingkat tinggi tentang resources dan resource collections. Sangat disarankan agar Anda membaca bagian lain dari dokumentasi ini untuk mendapatkan pemahaman yang lebih dalam tentang cara menyesuaikan dan menggunakan resources dengan lebih baik.

Sebelum mendalami cara menyesuaikan dan menulis resources Anda, mari kita lihat terlebih dahulu cara menggunakan resources di framework. Sebuah resource class mewakili satu model yang perlu ditransformasi ke format JSON. Misalnya, kita memiliki resource class `User` yang sederhana:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transformasi resource menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

Setiap resource class mendefinisikan method `toArray`, yang mengembalikan array atribut yang harus ditransformasi menjadi JSON saat mengirim response. Perhatikan bahwa kita dapat langsung menggunakan variabel `$this` untuk mengakses atribut model di sini. Ini karena resource class secara otomatis memproksikan atribut dan method ke model yang mendasarinya untuk memudahkan akses. Anda dapat mengembalikan resource yang telah didefinisikan di controller:

```php
<?php

namespace App\Controller;

use App\Resource\User as UserResource;
use App\Model\User;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserResource(User::first()))->toResponse();
    }
}
```

### Resource Collections

Anda dapat menggunakan method `collection` di controller untuk membuat instance resource guna mengembalikan kumpulan beberapa resource atau response paginated:

```php
namespace App\Controller;

use App\Resource\User as UserResource;
use App\Model\User;

class IndexController extends AbstractController
{
    public function index()
    {
        return UserResource::collection(User::all())->toResponse();
    }
}
```

Tentu saja, menggunakan method di atas, Anda tidak akan dapat menambahkan metadata tambahan apa pun untuk dikembalikan bersama koleksi. Jika Anda perlu menyesuaikan response resource collection, Anda perlu membuat resource khusus untuk mewakili koleksi tersebut:

```bash
php bin/hyperf.php gen:resource UserCollection
```

Anda dapat dengan mudah mendefinisikan metadata apa pun yang ingin Anda kembalikan dalam response di class resource collection yang dihasilkan:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transformasi resource collection menjadi array.
     *
     * @return array
     */
    public function toArray() :array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
```

Anda dapat mengembalikan resource collection yang telah didefinisikan di controller:

```php
<?php
namespace App\Controller;

use App\Model\User;
use App\Resource\UserCollection;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserCollection(User::all()))->toResponse();
    }
}
```

### Preserving Collection Keys

Ketika mengembalikan resource collection dari route, key dari koleksi akan di-reset sehingga berada dalam urutan numerik sederhana. Namun, Anda dapat menambahkan properti `preserveKeys` ke resource class untuk menunjukkan apakah key koleksi harus dipertahankan:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Menunjukkan apakah key koleksi harus dipertahankan.
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * Transformasi resource menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

Ketika properti `preserveKeys` diatur ke `true`, key koleksi akan dipertahankan:

```php
<?php

namespace App\Controller;

use App\Model\User;
use App\Resource\User as UserResource;

class IndexController extends AbstractController
{
    public function index()
    {
        return UserResource::collection(User::all()->keyBy->id)->toResponse();
    }
}
```

### Customizing the Base Resource Class

Biasanya, properti `$this->collection` dari resource collection secara otomatis diisi, dan hasilnya adalah setiap item dalam koleksi dipetakan ke resource class individualnya. Diasumsikan bahwa resource class individual adalah nama class dari koleksi, tetapi tanpa string `Collection` di akhir.

Misalnya, `UserCollection` memetakan instance user yang diberikan ke resource `User`. Untuk menyesuaikan perilaku ini, Anda dapat menimpa properti `$collects` dari resource collection:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Properti collects mendefinisikan resource class.
     *
     * @var string
     */
    public $collects = 'App\Resource\Member';

    /**
     * Transformasi resource collection menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
```

## Writing Resources

> Jika Anda belum membaca [Conceptual Overview](#conceptual-overview), sangat disarankan untuk membacanya sebelum melanjutkan dengan dokumentasi ini.

Pada intinya, peran resource sederhana. Mereka hanya perlu mentransformasi model yang diberikan menjadi array. Oleh karena itu, setiap resource berisi method `toArray` untuk mentransformasi atribut model Anda menjadi array yang ramah API yang dapat dikembalikan ke pengguna:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transformasi resource menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

Anda dapat mengembalikan resource yang telah didefinisikan di controller:

```php
<?php

namespace App\Controller;

use App\Model\User;
use App\Resource\User as UserResource;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserResource(User::find(1)))->toResponse();
    }
}
```

### Relationships

Jika Anda ingin menyertakan resource terkait dalam response, Anda hanya perlu menambahkannya ke array yang dikembalikan oleh method `toArray`. Dalam contoh di bawah, kita akan menggunakan method `collection` dari resource `Post` untuk menambahkan postingan pengguna ke response resource:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transformasi resource menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts' => PostResource::collection($this->posts),
        ];
    }
}
```

> Jika Anda hanya ingin menambahkan resource terkait ketika relationship sudah dimuat, silakan periksa dokumentasi terkait.

### Resource Collections

Sebuah resource mentransformasi satu model menjadi array, sedangkan resource collection mentransformasi kumpulan beberapa model menjadi array. Semua resource menyediakan method `collection` untuk menghasilkan resource collection "sementara", sehingga Anda tidak perlu menulis class resource collection untuk setiap tipe model:

```php
<?php
namespace App\Controller;

use App\Resource\User as UserResource;
use App\Model\User;

class IndexController extends AbstractController
{
    public function index()
    {
        return UserResource::collection(User::all())->toResponse();
    }
}
```

Untuk menyesuaikan metadata yang dikembalikan oleh koleksi, Anda tetap perlu mendefinisikan resource collection:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transformasi resource collection menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
```

Seperti resource tunggal, Anda dapat langsung mengembalikan resource collection di controller:

```php
<?php
namespace App\Controller;

use App\Model\User;
use App\Resource\UserCollection;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserCollection(User::all()))->toResponse();
    }
}
```

### Data Wrapping

Secara default, ketika response resource ditransformasi menjadi JSON, resource tingkat atas akan dibungkus dalam key `data`. Oleh karena itu, response resource collection yang tipikal terlihat seperti ini:

```json
{
    "data": [
        {
            "id": 1,
            "name": "Eladio Schroeder Sr.",
            "email": "therese28@example.com"
        },
        {
            "id": 2,
            "name": "Liliana Mayert",
            "email": "evandervort@example.com"
        }
    ]
}
```

Anda dapat menggunakan method `withoutWrapping` dari resource class dasar untuk menonaktifkan pembungkusan resource tingkat atas.

```php
<?php
namespace App\Controller;

use App\Model\User;
use App\Resource\UserCollection;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserCollection(User::all()))->withoutWrapping()->toResponse();
    }
}
```

> Method `withoutWrapping` hanya menonaktifkan pembungkusan resource tingkat atas dan tidak akan menghapus key `data` yang Anda tambahkan secara manual ke resource collection. Selain itu, method ini hanya berlaku di resource atau resource collection saat ini dan tidak memengaruhi state global.

#### Wrapping Nested Resources

Anda dapat sepenuhnya memutuskan bagaimana resource relationships dibungkus. Jika Anda ingin membungkus semua resource collections dalam key `data` terlepas dari bagaimana mereka ditumpuk, Anda perlu mendefinisikan class resource collection untuk setiap resource dan membungkus koleksi yang dikembalikan dalam key `data`.

Tentu saja, Anda mungkin khawatir bahwa resource tingkat atas akan dibungkus dalam dua key `data`. Tenang saja, komponen ini tidak akan pernah membiarkan resource Anda dibungkus ganda, jadi Anda tidak perlu khawatir tentang resource collection yang ditransformasi menjadi multi-tingkat:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transformasi resource collection menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
```

#### Pagination

Ketika mengembalikan koleksi paginated dalam response resource, bahkan jika Anda memanggil method `withoutWrapping`, komponen akan membungkus data resource Anda dalam key `data`. Ini karena response pagination selalu memiliki key `meta` dan `links` yang berisi informasi status pagination:

```json
{
    "data": [
        {
            "id": 1,
            "name": "Eladio Schroeder Sr.",
            "email": "therese28@example.com"
        },
        {
            "id": 2,
            "name": "Liliana Mayert",
            "email": "evandervort@example.com"
        }
    ],
    "links":{
        "first": "/pagination?page=1",
        "last": "/pagination?page=1",
        "prev": null,
        "next": null
    },
    "meta":{
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "/pagination",
        "per_page": 15,
        "to": 10,
        "total": 10
    }
}
```

Anda dapat meneruskan instance pagination ke method `collection` dari resource atau resource collection kustom:

```php
<?php

namespace App\Controller;

use App\Model\User;
use App\Resource\UserCollection;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserCollection(User::paginate()))->toResponse();
    }
}
```

Selalu ada key `meta` dan `links` dalam response pagination yang berisi
informasi status pagination:

```json

    {
        "data": [
            {
                "id": 1,
                "name": "Eladio Schroeder Sr.",
                "email": "therese28@example.com"
            },
            {
                "id": 2,
                "name": "Liliana Mayert",
                "email": "evandervort@example.com"
            }
        ],
        "links":{
            "first": "/pagination?page=1",
            "last": "/pagination?page=1",
            "prev": null,
            "next": null
        },
        "meta":{
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "/pagination",
            "per_page": 15,
            "to": 10,
            "total": 10
        }
    }
```

### Properti Bersyarat (Conditional Properties)

Terkadang, Anda mungkin ingin menambahkan atribut ke response resource hanya ketika kondisi tertentu terpenuhi. Misalnya, Anda mungkin ingin menambahkan nilai ke response resource hanya ketika pengguna saat ini adalah "administrator". Dalam kasus ini, komponen menyediakan beberapa method helper untuk membantu Anda memecahkan masalah. Method `when` dapat digunakan untuk menambahkan atribut secara kondisional ke response resource:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transformasi resource menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'secret' => $this->when(Auth::user()->isAdmin(), 'secret-value'),
        ];
    }
}
```

Dalam contoh di atas, key `secret` hanya akan dikembalikan dalam response resource jika method `isAdmin` mengembalikan `true`. Jika method mengembalikan `false`, key `secret` akan dihapus sebelum response resource dikirim ke klien. Method `when` memungkinkan Anda untuk menghindari penggunaan statement kondisional untuk menggabungkan array, menggunakan cara yang lebih elegan untuk menulis resources Anda.

Method `when` juga menerima closure sebagai argumen kedua, hanya menghitung nilai yang dikembalikan dari closure jika kondisi yang diberikan adalah `true`:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transformasi resource menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'secret' => $this->when(Auth::user()->isAdmin(), function () {
                return 'secret-value';
            }),
        ];
    }
}
```

#### Conditionally Merging Data

Terkadang, Anda mungkin ingin menambahkan beberapa atribut ke response resource hanya ketika kondisi tertentu terpenuhi. Dalam kasus ini, Anda dapat menggunakan method `mergeWhen` untuk menambahkan beberapa atribut ke response ketika kondisi yang diberikan adalah `true`:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transformasi resource menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            $this->mergeWhen(Auth::user()->isAdmin(), [
                'first-secret' => 'value',
                'second-secret' => 'value',
            ]),
        ];
    }
}
```

Demikian pula, jika kondisi yang diberikan adalah `false`, atribut-atribut ini akan dihapus sebelum response resource dikirim ke klien.

> Method `mergeWhen` tidak boleh digunakan dalam array yang mencampur key string dan numerik. Selain itu, tidak boleh digunakan dalam array dengan key numerik yang tidak berurutan.

### Conditional Relationships

Selain menambahkan atribut secara kondisional, Anda juga dapat menyertakan relationships secara kondisional dalam response resource berdasarkan apakah model relationship sudah dimuat. Ini memungkinkan Anda untuk memutuskan di controller model relationships mana yang akan dimuat, sehingga resources Anda dapat menambahkannya hanya setelah model relationships dimuat.

Melakukan hal ini menghindari masalah query "N+1" di resources Anda. Anda harus menggunakan method `whenLoaded` untuk memuat relationships secara kondisional. Untuk menghindari pemuatan relationships yang tidak perlu, method ini menerima nama relationship daripada relationship itu sendiri sebagai argumen:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transformasi resource menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts' => PostResource::collection($this->whenLoaded('posts')),
        ];
    }
}
```

Dalam contoh di atas, jika relationship belum dimuat, key `posts` akan dihapus sebelum response resource dikirim ke klien.

#### Conditional Pivot Information

Selain menyertakan relationships secara kondisional dalam response resource, Anda juga dapat menggunakan method `whenPivotLoaded` untuk menambahkan data dari tabel perantara relationship many-to-many secara kondisional. Argumen pertama yang diterima oleh method `whenPivotLoaded` adalah nama tabel perantara. Argumen kedua adalah closure yang mendefinisikan nilai yang akan dikembalikan jika informasi tabel perantara tersedia pada model:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transformasi resource menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'expires_at' => $this->whenPivotLoaded('role_user', function () {
                return $this->pivot->expires_at;
            }),
        ];
    }
}
```

Jika tabel perantara Anda menggunakan accessor selain `pivot`, Anda dapat menggunakan method `whenPivotLoadedAs`:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transformasi resource menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'expires_at' => $this->whenPivotLoadedAs('subscription', 'role_user', function () {
                return $this->subscription->expires_at;
            }),
        ];
    }
}
```

### Adding Metadata

Beberapa standar JSON API mengharuskan Anda untuk menambahkan metadata ke response resource dan resource collection. Ini biasanya mencakup `links` ke resource atau resource terkait, atau beberapa metadata tentang resource itu sendiri. Jika Anda perlu mengembalikan metadata lain tentang resource, cukup sertakan dalam method `toArray`. Misalnya, Anda mungkin perlu menambahkan informasi `links` saat mentransformasi resource collection:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transformasi resource collection menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
```

Saat menambahkan metadata tambahan ke resources Anda, Anda tidak perlu khawatir tentang menimpa key `links` atau `meta` yang secara otomatis ditambahkan saat mengembalikan response pagination. `Links` lain yang Anda tambahkan akan digabungkan dengan `links` yang ditambahkan oleh response pagination.

#### Top-Level Metadata

Terkadang Anda mungkin ingin menambahkan metadata tertentu ke response resource ketika resource dikembalikan sebagai resource tingkat atas. Ini biasanya mencakup meta-informasi untuk seluruh response. Anda dapat menambahkan method `with` di resource class untuk mendefinisikan metadata. Method ini harus mengembalikan array metadata, yang akan disertakan dalam response resource ketika resource dirender sebagai resource tingkat atas:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transformasi resource collection menjadi array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }

    public function with() : array
    {
        return [
            'meta' => [
                'key' => 'value',
            ],
        ];
    }
}
```

#### Adding Metadata Saat Membangun Resources

Anda juga dapat menambahkan data tingkat atas saat membangun instance resource di controller. Semua resources dapat menggunakan method `additional` untuk menerima array data yang harus ditambahkan ke response resource:

```php
<?php

namespace App\Controller;

use App\Model\User;
use App\Resource\UserCollection;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserCollection(User::all()->load('roles')))
            ->additional(['meta' => [
                'key' => 'value',
            ]])->toResponse();    
    }
}
```

## Responding to Resources

Seperti yang Anda ketahui, resources dapat langsung dikembalikan di controller:

```php
<?php

namespace App\Controller;

use App\Model\User;
use App\Resource\User as UserResource;

class IndexController extends AbstractController
{
    public function index()
    {
        return (new UserResource(User::find(1)))->toResponse();
    }

    public function info()
    {
        return new UserResource(User::find(1));
    }
}
```

Jika Anda ingin mengatur response headers, status codes, dll., Anda dapat memanggil method `toResponse()` untuk mendapatkan objek response dan mengaturnya.
