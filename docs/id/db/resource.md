# API Resource Constructor

> Mendukung ekstensi resource yang mengembalikan response gRPC

## Pendahuluan

Saat membangun API, Anda sering kali memerlukan translation layer untuk
menghubungkan Model Anda dengan response JSON aktual yang dikembalikan ke
pengguna. Class resource memungkinkan Anda mengonversi model dan collection dari
model ke JSON dengan cara yang lebih intuitif dan mudah.

## Instalasi

```
composer require hyperf/resource
```

## Membuat Resource

Anda dapat menggunakan command `gen:resource` untuk membuat class resource.
Secara default, resource yang dibuat akan ditempatkan di dalam folder
`app/Resource` aplikasi. Resource mewarisi class
`Hyperf\Resource\Json\JsonResource`:

```bash
php bin/hyperf.php gen:resource User
```

### Resource Collection

Selain membuat resource untuk mentransformasi satu model, Anda juga dapat
membuat collection dari resource untuk mentransformasi collection dari model.
Ini memungkinkan Anda untuk menyertakan link dan informasi meta lainnya yang
terkait dengan resource yang diberikan dalam response.

Anda perlu menambahkan flag `--collection` saat membuat resource untuk
menghasilkan resource collection. Sebagai alternatif, Anda dapat menyertakan
kata `Collection` secara langsung pada nama resource untuk menunjukkan bahwa
resource collection harus dibuat. Resource collection mewarisi class
`Hyperf\Resource\Json\ResourceCollection`:

```bash
php bin/hyperf.php gen:resource Users --collection

php bin/hyperf.php gen:resource UserCollection
```

## gRPC Resource

> Memerlukan instalasi tambahan `hyperf/resource-grpc`

```
composer require hyperf/resource-grpc
```

```bash
php bin/hyperf.php gen:resource User --grpc
```

gRPC resource perlu menyetel class `message`. Hal ini dicapai dengan me-override
method `expect()` dari class resource.

Ketika gRPC service mengembalikan nilai, `toMessage()` harus dipanggil. Method
ini mengembalikan instance dari class `message` yang telah diinisiasi.

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

Resource collection bawaan yang dihasilkan dapat mendukung pengembalian gRPC
dengan meng-extend interface `Hyperf\ResourceGrpc\GrpcResource`.

## Ikhtisar Konsep

> Ini adalah gambaran umum tingkat tinggi (high-level) tentang resource dan
> resource collection. Sangat disarankan bagi Anda untuk membaca bagian lain
> dari dokumen ini guna mendapatkan pemahaman mendalam tentang cara menyesuaikan
> (customizing) dan menggunakan resource dengan lebih baik.

Sebelum mempelajari cara menulis resource kustom Anda, mari kita lihat bagaimana
resource digunakan di dalam framework. Sebuah class resource yang mewakili satu
model perlu dikonversi ke format JSON. Sebagai contoh, sekarang kita memiliki
class resource `User` yang sederhana:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
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

Setiap class resource mendefinisikan method `toArray` yang mengembalikan array
properti yang harus dikonversi ke JSON saat mengirimkan response. Perhatikan
bahwa di sini kita dapat langsung menggunakan variabel `$this` untuk mengakses
properti model. Ini karena class resource akan secara otomatis memproksi
(proxy) properti dan method ke model di bawahnya untuk kemudahan akses. Anda
dapat mengembalikan resource yang telah didefinisikan di dalam controller Anda:

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

### Resource Collection

Anda dapat menggunakan method `collection` di dalam controller untuk membuat
instance resource guna mengembalikan collection dari beberapa resource atau
response yang dipaginasi (paginated):

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

Tentu saja, menggunakan method di atas membuat Anda tidak dapat menambahkan
metadata tambahan apa pun untuk dikembalikan bersama collection tersebut. Jika
Anda memerlukan response resource collection kustom, Anda perlu membuat resource
khusus untuk mewakili collection tersebut:

```bash
php bin/hyperf.php gen:resource UserCollection
```

Anda dapat dengan mudah mendefinisikan metadata apa pun yang ingin Anda
kembalikan dalam response di dalam class resource collection yang dihasilkan:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
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

Anda dapat mengembalikan resource collection yang telah didefinisikan di dalam
controller Anda:

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

### Melindungi Key Collection

Ketika sebuah resource collection dikembalikan dari sebuah route, key dari
collection tersebut akan di-reset sehingga berada dalam urutan numerik yang
sederhana. Namun, atribut `preserveKeys` dapat ditambahkan ke class resource
untuk menunjukkan apakah key collection harus dipertahankan:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * A collection key indicating whether the resource should be preserved.
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * Transform the resource into an array.
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

Ketika properti `preserveKeys` disetel ke `true`, key dari collection akan
dilindungi (dipertahankan):

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

### Menyesuaikan Class Resource Dasar

Biasanya, properti `$this->collection` dari resource collection diisi secara
otomatis, menghasilkan pemetaan dari setiap item collection ke class resource
individunya masing-masing. Class resource tunggal diasumsikan sebagai nama class
dari collection tersebut tanpa string `Collection` di bagian akhir.

Sebagai contoh, `UserCollection` memetakan instance user yang diberikan ke dalam
resource `User`. Untuk menyesuaikan perilaku ini, Anda dapat me-override
properti `$collects` dari resource collection tersebut:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * collects properties define resource classes.
     *
     * @var string
     */
    public $collects = 'App\Resource\Member';

    /**
     * Transform the resource collection into an array.
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

## Menulis Resource

> Jika Anda belum membaca [Ikhtisar Konsep](#ikhtisar-konsep), sangat disarankan
> bagi Anda untuk melakukannya sebelum melanjutkan dokumen ini.

Pada dasarnya, peran resource sangatlah sederhana. Mereka hanya perlu
mengonversi model yang diberikan menjadi array. Jadi setiap resource berisi
method `toArray` untuk mengonversi properti model Anda menjadi array yang ramah
API yang dapat dikembalikan ke pengguna:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
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

Anda dapat mengembalikan resource yang sudah didefinisikan di dalam controller:

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

### Association (Asosiasi)

Jika Anda ingin menyertakan resource terkait (associated resources) dalam
response, Anda hanya perlu menambahkannya ke array yang dikembalikan oleh
method `toArray`. Dalam contoh berikut, kita akan menggunakan method
`collection` dari resource `Post` untuk menambahkan post milik user ke dalam
response resource:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
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

> Jika Anda hanya ingin menambahkan resource terkait ketika association tersebut
> sudah di-load, lihat dokumentasi terkait.

### Resource Collection

Sebuah resource mengonversi satu model menjadi array, dan sebuah resource
collection mengonversi collection dari beberapa model menjadi array. Semua
resource menyediakan method `collection` untuk menghasilkan collection resource
"sementara", sehingga Anda tidak perlu menulis class resource collection untuk
setiap tipe model:

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

Untuk menyesuaikan metadata dari collection yang dikembalikan, Anda masih perlu
mendefinisikan resource collection:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
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

Sama halnya dengan resource individu, Anda dapat mengembalikan resource
collection secara langsung di dalam controller Anda:

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

### Pembungkusan Data (Data Wrapping)

Secara default, saat response resource dikonversi ke JSON, resource tingkat
teratas (top-level) akan dibungkus dalam key `data`. Jadi response resource
collection yang khas akan terlihat seperti ini:

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

Anda dapat menonaktifkan pembungkusan resource top-level menggunakan method
`withoutWrapping` dari class dasar resource.

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

> Method `withoutWrapping` hanya akan menonaktifkan pembungkusan resource
> top-level, ini tidak akan menghapus key `data` yang Anda tambahkan secara
> manual ke resource collection. Dan ini hanya akan berlaku pada resource atau
> resource collection saat ini, tanpa memengaruhi global.

#### Membungkus Nested Resource

Anda sepenuhnya bebas menentukan bagaimana association resource dibungkus.
Jika Anda ingin semua resource collection dibungkus dalam key `data`, tidak
peduli seberapa nested (bersarang) mereka, maka Anda perlu mendefinisikan class
resource collection untuk setiap resource dan membungkus collection yang
dikembalikan dalam key `data`.

Tentu saja, Anda mungkin khawatir bahwa resource top-level kemudian akan
dibungkus dalam dua key `data`. Tenang saja, komponen tidak akan pernah
membungkus resource Anda dua kali, sehingga Anda tidak perlu khawatir tentang
nested berganda dari resource collection yang ditransformasikan:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
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

Saat mengembalikan collection yang dipaginasi (paginated collection) dalam
response resource, meskipun Anda memanggil method `withoutWrapping`, komponen
akan tetap membungkus data resource Anda dalam key `data`. Ini karena key `meta`
dan `links` dalam response pagination selalu berisi informasi status pagination:

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

Anda dapat meneruskan instance pagination ke method collection milik resource
atau ke resource collection kustom:

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

Terkadang Anda mungkin ingin menambahkan atribut ke response resource saat kondisi
tertentu terpenuhi. Sebagai contoh, Anda mungkin ingin menambahkan nilai ke
response resource jika user saat ini adalah seorang "admin". Dalam hal ini,
komponen menyediakan beberapa helper method untuk membantu Anda menyelesaikan
masalah tersebut. Method `when` dapat digunakan untuk menambahkan atribut secara
bersyarat ke response resource:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
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

Dalam contoh di atas, key `secret` pada akhirnya hanya akan dikembalikan dalam
response resource jika method `isAdmin` mengembalikan nilai `true`. Jika method
ini mengembalikan `false`, key `secret` akan dihapus sebelum response resource
dikirim ke klien. Method `when` memungkinkan Anda menghindari penggabungan array
(array concatenation) dengan pernyataan kondisional dan menulis resource Anda
dengan cara yang lebih elegan.

Method `when` juga menerima closure sebagai argumen keduanya, di mana nilai yang
dikembalikan hanya dihitung jika kondisi yang diberikan bernilai `true`:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
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

#### Penggabungan Data Bersyarat (Conditional Merge Data)

Terkadang, Anda mungkin ingin menambahkan beberapa atribut ke response resource
saat kondisi tertentu terpenuhi. Dalam hal ini, Anda dapat menggunakan method
`mergeWhen` untuk menambahkan beberapa properti ke response saat kondisi yang
diberikan bernilai `true`:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
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

Demikian pula, jika kondisi yang diberikan bernilai `false`, atribut-atribut ini
akan dihapus sebelum response resource dikirim ke klien.

> Method `mergeWhen` sebaiknya tidak digunakan pada array dengan campuran key
> bertipe string dan numerik. Selain itu, method ini juga sebaiknya tidak
> digunakan pada array dengan key numerik yang tidak berurutan.

### Association Bersyarat (Conditional Association)

Selain menambahkan properti secara bersyarat, Anda juga dapat menyertakan
association secara bersyarat dalam response resource Anda berdasarkan apakah
association model tersebut sudah di-load atau belum. Ini memungkinkan Anda
untuk memutuskan di dalam controller association model mana yang akan di-load,
sehingga resource Anda dapat menambahkannya setelah association model tersebut
di-load.

Melakukan hal ini akan menghindari masalah query "N+1" di dalam resource Anda.
Anda harus menggunakan method `whenLoaded` untuk me-load association secara
bersyarat. Untuk menghindari pemuatan association yang tidak perlu, method ini
menerima nama dari association tersebut, bukan objek association itu sendiri,
sebagai parameternya:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
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

Dalam contoh di atas, jika association tidak di-load, key `posts` akan dihapus
sebelum response resource dikirim ke klien.

#### Informasi Tabel Perantara Bersyarat (Conditional Intermediate Table Information)

Selain menyertakan association secara bersyarat ke dalam response resource,
Anda juga dapat menambahkan data secara bersyarat dari tabel perantara
(intermediate table) dalam relasi many-to-many menggunakan method
`whenPivotLoaded`. Parameter pertama yang diterima oleh method `whenPivotLoaded`
adalah nama dari tabel perantara. Parameter kedua adalah closure yang
mendefinisikan nilai yang akan dikembalikan pada model jika informasi tabel
perantara tersedia:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
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

Jika tabel perantara Anda menggunakan accessor selain `pivot`, Anda dapat
menggunakan method `whenPivotLoadedAs`:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
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

### Menambahkan Metadata

Beberapa standar JSON API mengharuskan Anda menambahkan metadata ke response
resource and resource collection. Ini biasanya mencakup `links` untuk resource
atau resource terkait, atau beberapa metadata tentang resource itu sendiri. Jika
Anda perlu mengembalikan metadata tambahan tentang resource, cukup sertakan hal
tersebut di dalam method `toArray`. Sebagai contoh, Anda mungkin perlu
menambahkan informasi `links` saat mengonversi resource collection:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
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

Saat menambahkan metadata ekstra ke resource Anda, Anda tidak perlu khawatir
akan menimpa key `links` atau `meta` yang ditambahkan secara otomatis saat
mengembalikan response yang dipaginasi. `links` lain yang Anda tambahkan akan
digabungkan dengan `links` yang ditambahkan oleh response pagination.

#### Metadata Tingkat Teratas (Top-level Metadata)

Terkadang Anda mungkin ingin menambahkan metadata tertentu ke response resource
saat resource tersebut dikembalikan sebagai resource tingkat teratas (top-level).
Ini biasanya mencakup informasi meta untuk seluruh response. Anda dapat
menambahkan method `with` pada class resource Anda untuk mendefinisikan
metadata. Method ini harus mengembalikan array metadata yang akan disertakan
dalam response resource saat resource dirender sebagai resource top-level:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
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

#### Menambahkan Metadata Saat Mengonstruksi Resource

Anda juga dapat menambahkan data top-level saat membuat instance resource di
dalam controller. Semua resource dapat menggunakan method `additional` untuk
menerima array data yang harus ditambahkan ke response resource:

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

## Merespons Resource (Response Resource)

Seperti yang Anda ketahui, resource dapat dikembalikan secara langsung di dalam
controller:

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

Jika Anda ingin mengatur informasi response header, status code, dll., Anda
dapat mendapatkan objek response dengan memanggil method `toResponse()` untuk
menyetelnya.
