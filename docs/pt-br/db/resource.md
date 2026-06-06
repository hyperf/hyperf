# Construtor de recursos de API
> Suporte a extensões de resource que retornam respostas gRPC

## Introdução

Ao construir APIs, muitas vezes você precisa de uma camada de transformação para conectar seu Model ao JSON que será retornado ao usuário. Classes de resource permitem converter models e collections de models para JSON de forma mais intuitiva e simples.

## Instalação
```
composer require hyperf/resource
```

## Gerar resources

Você pode usar o comando `gen:resource` para gerar uma classe de resource. Por padrão, resources gerados são colocados na pasta `app/Resource` da aplicação. Resources herdam de `Hyperf\Resource\Json\JsonResource`:

```bash
php bin/hyperf.php gen:resource User
```

### Resource Collection

Além de gerar resources para transformar um único model, você também pode gerar uma coleção de resources para transformar uma collection de models. Isso permite incluir links e outros metadados relacionados a um determinado resource na resposta.

Você precisa adicionar a flag `--collection` ao gerar resources para gerar uma coleção de resources. Alternativamente, você pode incluir `Collection` diretamente no nome do resource para indicar que uma coleção deve ser gerada. Coleções de resources herdam de `Hyperf\Resource\Json\ResourceCollection`:

```bash
php bin/hyperf.php gen:resource Users --collection

php bin/hyperf.php gen:resource UserCollection
```

## Resources gRPC

> Requer a instalação adicional de `hyperf/resource-grpc`

```
composer require hyperf/resource-grpc
```

```bash
php bin/hyperf.php gen:resource User --grpc
```

Resources gRPC precisam definir a classe `message`. Isso é feito sobrescrevendo o método `expect()` na classe de resource.

Quando o serviço gRPC retorna, é necessário chamar `toMessage()`. Esse método retorna uma instância da classe `message`.

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

A collection de resource gerada por padrão pode suportar retorno gRPC estendendo a interface `Hyperf\ResourceGrpc\GrpcResource`.

## Visão geral de conceitos

> Esta é uma visão geral (alto nível) de resources e collections de resources. É altamente recomendado ler o restante deste documento para um entendimento mais aprofundado de como customizar e usar resources.

Antes de entrar em como escrever resources customizados, vamos ver como resources são usados no framework. Uma classe de resource que representa um único model precisa ser convertida para JSON. Por exemplo, temos um resource simples `User`:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
     /**
      * Transforma o resource em um array.
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

Cada resource define um método `toArray` que retorna um array de atributos que devem ser convertidos para JSON ao enviar a resposta. Note que aqui podemos usar diretamente `$this` para acessar atributos do model. Isso acontece porque a classe de resource faz proxy automaticamente de propriedades e métodos para o model subjacente. Você pode retornar resources definidos no seu controller:

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

### Coleção de resources

Você pode usar o método `collection` em um controller para criar instâncias de resource e retornar coleções com múltiplos resources ou respostas paginadas:

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

Claro, usando o método acima você não conseguirá adicionar metadados adicionais junto com a coleção. Se você precisar de uma resposta de collection de resources customizada, precisa criar um resource dedicado para representar a coleção:

```bash
php bin/hyperf.php gen:resource UserCollection
```

Você pode definir facilmente os metadados que deseja retornar na resposta na classe de collection de resources gerada:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
     /**
      * Transforma a collection de resources em um array.
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

Você pode retornar uma coleção de resources definida no seu controller:

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

### Preservar chaves da coleção

Quando uma collection de resources é retornada por uma rota, as chaves da coleção são redefinidas para uma ordem numérica simples. Entretanto, um atributo `preserveKeys` pode ser adicionado a uma classe de resource para indicar se as chaves devem ser preservadas:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Chave de coleção indicando se o resource deve preservar as chaves.
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * Transforma o resource em um array.
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

Quando a propriedade `preserveKeys` é definida como `true`, as chaves da coleção serão preservadas:

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

### Classe base de resource customizada

Normalmente, a propriedade `$this->collection` de uma collection de resources é preenchida automaticamente, resultando no mapeamento de cada item da coleção para sua classe de resource individual. A classe de resource individual é assumida como o nome da classe da coleção sem o sufixo `Collection`.

Por exemplo, `UserCollection` mapeia uma instância de user para um resource `User`. Para customizar esse comportamento, você pode sobrescrever a propriedade `$collects` da collection de resources:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Propriedade collects define classes de resource.
     *
     * @var string
     */
    public $collects = 'App\Resource\Member';

    /**
     * Transforma a collection de resources em um array.
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

## Escrever resources

> Se você ainda não leu [Visão geral de conceitos](#visao-geral-de-conceitos), é altamente recomendado que o faça antes de continuar.

Essencialmente, o papel de resources é simples: converter um model em um array. Por isso, todo resource contém um método `toArray` para converter atributos do seu model para um array amigável para a API, que pode ser retornado ao usuário:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transforma o resource em um array.
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

Você pode retornar um resource já definido em um controller:

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

### Associações

Se você quiser incluir resources associados na resposta, basta adicioná-los ao array retornado por `toArray`. No exemplo a seguir, usaremos o método `collection` do resource `Post` para adicionar os posts do usuário à resposta do resource:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
     /**
      * Transforma o resource em um array.
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

> Se você quiser adicionar um resource associado apenas quando a associação já estiver carregada, veja a documentação relacionada.

### Coleção de resources

Um resource converte um único model em um array, e uma coleção de resources converte uma collection de múltiplos models em um array. Todos os resources fornecem um método `collection` para gerar uma coleção "temporária" de resources, assim você não precisa criar uma classe de coleção para cada tipo de model:

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

Para customizar os metadados da coleção retornada, você ainda precisa definir uma collection de resources:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
     /**
      * Transforma a collection de resources em um array.
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

Assim como com resources individuais, você pode retornar coleções de resources diretamente no seu controller:

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

### Envelope de dados

Por padrão, quando a resposta do resource é convertida para JSON, o resource de nível superior será envelopado na chave `data`. Então uma resposta típica de collection de resources se parece com isto:

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

Você pode desabilitar o envelope de resources de nível superior usando o método `withoutWrapping` da classe base de resource.

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

> O método withoutWrapping apenas desabilita o envelope do resource de nível superior; ele não remove a chave data que você adicionou manualmente à collection de resources. Ele também só tem efeito no resource/collection atual, sem afetar o comportamento global.

#### Envelopando resources aninhados

Você tem total liberdade para decidir como associações de resources serão envelopadas. Se você quiser que todas as coleções de resources sejam envelopadas em uma chave `data`, independentemente do nível de aninhamento, então você precisa definir uma classe de coleção para cada resource e envolver a coleção retornada em `data`.

É natural se preocupar que o resource de nível superior então ficaria com duas chaves `data`. Fique tranquilo: o componente nunca fará double wrap, então você não precisa se preocupar com múltiplos níveis de `data` em coleções transformadas:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
     /**
      * Transforma a collection de resources em um array.
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

#### Paginação

Ao retornar uma coleção paginada em uma resposta de resource, mesmo que você chame `withoutWrapping`, o componente envolverá seus dados em `data`. Isso acontece porque as chaves `meta` e `links` da paginação sempre contêm informações de status de paginação:

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

Você pode passar uma instância de paginação para o método collection do resource ou para uma collection de resources customizada:

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

Respostas paginadas sempre incluem as chaves `meta` e `links`, contendo informações de status da paginação:

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

### Propriedades condicionais

Às vezes você pode querer adicionar atributos à resposta do resource quando uma determinada condição for atendida. Por exemplo, você pode querer adicionar um valor se o usuário atual for um "admin". Nesse caso, o componente fornece métodos auxiliares para ajudar a resolver o problema. O método `when` pode ser usado para adicionar atributos condicionalmente:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
     /**
      * Transforma o resource em um array.
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

No exemplo acima, a chave `secret` só será retornada na resposta do resource se `isAdmin` retornar `true`. Se retornar `false`, a chave `secret` será removida antes de enviar a resposta ao cliente. O método `when` evita que você concatene arrays com condicionais e permite escrever resources de forma mais elegante.

O método `when` também aceita um closure como segundo argumento. O valor retornado será computado apenas se a condição informada for `true`:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transforma o resource em um array.
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

#### Merge condicional de dados

Às vezes você pode querer adicionar múltiplos atributos à resposta do resource quando uma condição for atendida. Nesse caso, você pode usar `mergeWhen` para adicionar várias propriedades quando a condição for `true`:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transforma o resource em um array.
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

Da mesma forma, se a condição for `false`, esses atributos serão removidos antes de enviar a resposta ao cliente.

> O método `mergeWhen` não deve ser usado em arrays com chaves string e numéricas misturadas. Ele também não deve ser usado em arrays com chaves numéricas fora de ordem.

### Associações condicionais

Além de adicionar propriedades de forma condicional, você também pode incluir associações condicionalmente com base em se o relacionamento do model está carregado. Isso permite decidir no controller quais associações carregar, para que seus resources as adicionem depois que estiverem carregadas.

Isso evita o problema de "N+1" queries nos resources. Você deve usar `whenLoaded` para incluir associações condicionalmente. Para evitar carregar associações desnecessárias, esse método aceita o nome da associação, em vez da própria associação, como parâmetro:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transforma o resource em um array.
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

No exemplo acima, se a associação não estiver carregada, a chave `posts` será removida antes de enviar a resposta ao cliente.

#### Informação condicional da tabela intermediária

Além de incluir associações condicionalmente, você também pode adicionar dados da tabela intermediária em relacionamentos muitos-para-muitos usando `whenPivotLoaded`. O primeiro parâmetro é o nome da tabela intermediária. O segundo é um closure que define o valor a retornar no model quando a informação da tabela intermediária estiver disponível:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transforma o resource em um array.
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

Se a tabela intermediária usa um acessor diferente de `pivot`, você pode usar `whenPivotLoadedAs`:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transforma o resource em um array.
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

### Adicionar metadados

Alguns padrões de JSON API exigem que você adicione metadados a respostas de resources e collections de resources. Isso geralmente inclui `links` do resource ou de resources relacionados, ou metadados sobre o próprio resource. Se você precisar retornar metadados adicionais, basta incluí-los no `toArray`. Por exemplo, você pode precisar adicionar `links` ao converter coleções:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transforma a collection de resources em um array.
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

Ao adicionar metadados extras ao seu resource, você não precisa se preocupar em sobrescrever as chaves `links` ou `meta` que são adicionadas automaticamente em respostas paginadas. Quaisquer outros `links` adicionados serão mesclados com os `links` adicionados pela paginação.

#### Metadados no nível superior

Às vezes você pode querer adicionar certos metadados à resposta do resource quando ele é retornado como resource de nível superior. Isso normalmente inclui informações de meta para toda a resposta. Você pode adicionar um método `with` na classe do resource para definir metadados. Esse método deve retornar um array de metadados que será incluído quando o resource for renderizado como nível superior:

```php
<?php

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transforma a collection de resources em um array.
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

#### Adicionar metadados ao construir resources

Você também pode adicionar dados de nível superior ao construir uma instância de resource no controller. Todos os resources podem usar o método `additional` para receber um array com os dados que devem ser adicionados à resposta:

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

## Resource de resposta

Como você já sabe, resources podem ser retornados diretamente no controller:

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

Se você quiser definir headers da resposta, status code, etc., obtenha o objeto de response chamando `toResponse()` para então configurá-lo.

