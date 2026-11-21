# Construtor de recursos de API
 
> Suporte para extensões de recurso que retornam respostas Grpc

## Introdução

Ao construir APIs, muitas vezes você precisa de uma camada de tradução para conectar seu Model com a resposta JSON real retornada ao usuário. Classes de recurso (Resource) permitem que você converta models e collections de models em JSON de uma forma mais intuitiva e fácil.

## Instalação

```
composer require hyperf/resource
```

## Gerar recursos

Você pode usar o comando `gen:resource` para gerar uma classe de recurso. Por padrão, os recursos gerados são colocados na pasta `app/Resource` da aplicação. Recursos herdam da classe `Hyperf\Resource\Json\JsonResource`:

```bash
php bin/hyperf.php gen:resource User
```

### Coleção de recursos (Resource Collection)

Além de gerar recursos para transformar um único model, você também pode gerar uma coleção de recursos para transformar uma coleção de models. Isso permite que você inclua links e outras informações de metadados relacionadas a um determinado recurso na resposta.

Você precisa adicionar a flag `--collection` ao gerar recursos para gerar uma coleção de recursos. Alternativamente, você pode incluir `Collection` diretamente no nome do recurso para indicar que uma coleção de recursos deve ser gerada. Coleções de recursos herdam da classe `Hyperf\Resource\Json\ResourceCollection`:

```bash
php bin/hyperf.php gen:resource Users --collection

php bin/hyperf.php gen:resource UserCollection
```

## Recursos gRPC

> Requer a instalação adicional de `hyperf/resource-grpc`

```
composer require hyperf/resource-grpc
```

```bash
php bin/hyperf.php gen:resource User --grpc
```

Recursos gRPC precisam definir a classe `message`. Isso é feito sobrescrevendo o método `expect()` da classe de recurso.

Quando o serviço gRPC retorna, `toMessage()` deve ser chamado. Este método retorna uma classe `message` instanciada.

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

A coleção de recursos gerada por padrão pode suportar retorno gRPC estendendo a interface `Hyperf\ResourceGrpc\GrpcResource`.

## Visão geral do conceito

> Esta é uma visão geral de alto nível sobre recursos e coleções de recursos. É altamente recomendado que você leia o restante deste documento para um entendimento profundo de como customizar e usar melhor os recursos.

Antes de entrar em detalhes sobre como escrever seus próprios recursos, vamos ver como os recursos são usados no framework. Uma classe de recurso representando um único model precisa ser convertida para o formato JSON. Por exemplo, agora temos uma classe de recurso `User` simples:

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

Cada classe de recurso define um método `toArray` que retorna um array de propriedades que devem ser convertidas para JSON ao enviar a resposta. Observe que aqui podemos usar diretamente a variável `$this` para acessar propriedades do model. Isso porque a classe de recurso vai automaticamente fazer proxy de propriedades e métodos para o model subjacente para facilitar o acesso. Você pode retornar recursos definidos no seu controller:

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

### Coleção de recursos

Você pode usar o método `collection` em um controller para criar instâncias de recurso para retornar coleções de múltiplos recursos ou respostas paginadas:

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

Claro, usando o método acima você não conseguirá adicionar nenhum metadado adicional para retornar com a coleção. Se você precisar de uma resposta de coleção de recursos customizada, você precisa criar um recurso dedicado para representar a coleção:

```bash
php bin/hyperf.php gen:resource UserCollection
```

Você pode facilmente definir qualquer metadado que deseja retornar na resposta na classe de coleção de recursos gerada:

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

Você pode retornar uma coleção de recursos definida no seu controller:

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

### Chaves de coleção protegidas

Quando uma coleção de recursos é retornada de uma rota, as chaves da coleção são resetadas para ficarem em ordem numérica simples. Porém, um atributo `preserveKeys` pode ser adicionado a uma classe de recurso para indicar se as chaves da coleção devem ser preservadas:

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

Quando a propriedade `preserveKeys` está definida como `true`, as chaves da coleção serão protegidas:

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

### Classe de recurso base customizada

Normalmente, a propriedade `$this->collection` de uma coleção de recursos é preenchida automaticamente, resultando em um mapeamento de cada item da coleção para sua classe de recurso individual. Assume-se que a classe de recurso individual é o nome de classe da coleção sem a string `Collection` no final.

Por exemplo, `UserCollection` mapeia uma dada instância de user para um recurso `User`. Para customizar esse comportamento, você pode sobrescrever a propriedade `$collects` da coleção de recursos:

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

## Escrevendo recursos

> Se você não leu a [Visão geral do conceito](#Concept Overview), é altamente recomendado que você o faça antes de continuar com este documento.

Essencialmente, o papel dos recursos é simples. Eles só precisam converter um determinado model em um array. Então todo recurso contém um método `toArray` para converter as propriedades do seu model em um array amigável para API que pode ser retornado ao usuário:

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

Você pode retornar um recurso já definido em um controller:

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

### Associação

Se você quiser incluir recursos associados na resposta, você só precisa adicioná-los ao array retornado pelo método `toArray`. No exemplo a seguir, vamos usar o método `collection` do recurso `Post` para adicionar os posts do usuário à resposta do recurso:

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

> Se você só quiser adicionar um recurso associado quando a associação já estiver carregada, veja a documentação relacionada.

### Coleção de recursos

Um recurso converte um único model em um array, e uma coleção de recursos converte uma coleção de múltiplos models em um array. Todos os recursos fornecem um método `collection` para gerar uma coleção "temporária" de recursos, então você não precisa escrever uma classe de coleção de recursos para cada tipo de model:

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

Para customizar os metadados da coleção retornada, você ainda precisa definir uma coleção de recursos:

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

Assim como nos recursos individuais, você pode retornar coleções de recursos diretamente no seu controller:

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

### Empacotamento de dados

Por padrão, quando a resposta do recurso é convertida para JSON, o recurso de nível superior será envolvido na chave `data`. Então uma resposta típica de coleção de recursos se parece assim:

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

Você pode desabilitar o envolvimento de recursos de nível superior usando o método `withoutWrapping` da classe base de recurso.

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

> O método withoutWrapping vai desabilitar apenas o envolvimento do recurso de nível superior, ele não vai remover a chave data que você adicionou manualmente na coleção de recursos. E ele só terá efeito no recurso ou coleção de recursos atual, sem afetar o global.

#### Envolvendo recursos aninhados

Você é completamente livre para decidir como as associações de recursos são envolvidas. Se você quiser que todas as coleções de recursos sejam envolvidas em uma chave `data`, independentemente de quão aninhadas estejam, então você precisa definir uma classe de coleção de recursos para cada recurso e envolver a coleção retornada em uma chave `data`.

Claro, você pode se preocupar que o recurso de nível superior seria então envolvido em duas chaves `data`. Fique tranquilo, os componentes nunca vão fazer com que seus recursos sejam envolvidos duplamente, então você não precisa se preocupar com múltiplos aninhamentos de coleções de recursos transformadas:

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

#### Paginação

Ao retornar uma coleção paginada em uma resposta de recurso, mesmo que você chame o método `withoutWrapping`, o componente vai envolver os dados do seu recurso na chave `data`. Isso porque as chaves `meta` e `links` na resposta de paginação sempre contêm informações de status de paginação:

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

Você pode passar uma instância de paginação para o método collection do recurso ou uma coleção de recursos customizada:

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

Sempre há chaves `meta` e `links` nas respostas de paginação que contêm informações de status de paginação:

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

Às vezes você pode querer adicionar atributos à resposta do recurso quando uma determinada condição for atendida. Por exemplo, você pode querer adicionar um valor à resposta do recurso se o usuário atual for um "admin". Nesse caso o componente fornece alguns métodos auxiliares para ajudá-lo a resolver o problema. O método `when` pode ser usado para adicionar condicionalmente atributos às respostas de recurso:

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

No exemplo acima, a chave `secret` será eventualmente retornada na resposta do recurso apenas se o método `isAdmin` retornar `true`. Se este método retornar `false`, a chave `secret` será excluída antes que a resposta do recurso seja enviada ao cliente. O método `when` permite que você evite concatenar arrays com declarações condicionais e, em vez disso, escreva seus recursos de uma forma mais elegante.

O método `when` também aceita um closure como segundo argumento, do qual o valor retornado é computado apenas se a condição dada for `true`:

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

#### Mesclagem condicional de dados

Às vezes, você pode querer adicionar múltiplos atributos à resposta do recurso quando uma determinada condição for atendida. Nesse caso, você pode usar o método `mergeWhen` para adicionar múltiplas propriedades à resposta quando uma determinada condição for `true`:

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

Da mesma forma, se a condição dada for `false`, esses atributos serão removidos antes que a resposta do recurso seja enviada ao cliente.

> O método `mergeWhen` não deve ser usado em arrays com chaves de string e numéricas misturadas. Além disso, não deve ser usado em arrays de chaves numéricas fora de ordem.

### Associação condicional

Além de adicionar propriedades condicionalmente, você também pode incluir condicionalmente associações nas respostas do seu recurso com base em se a associação do model está carregada. Isso permite que você decida no controller quais associações do model carregar, para que seus recursos possam adicioná-las depois que as associações do model forem carregadas.

Fazer isso vai evitar o problema de query "N+1" nos seus recursos. Você deve usar o método `whenLoaded` para carregar associações condicionalmente. Para evitar carregar associações desnecessárias, este método aceita o nome da associação em vez da própria associação como seu parâmetro:

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

No exemplo acima, se a associação não estiver carregada, a chave `posts` será excluída antes que a resposta do recurso seja enviada ao cliente.

#### Informações condicionais da tabela intermediária

Além de incluir condicionalmente associações nas respostas do seu recurso, você também pode adicionar condicionalmente dados de tabelas intermediárias em associações muitos-para-muitos usando o método `whenPivotLoaded`. O primeiro parâmetro aceito pelo método `whenPivotLoaded` é o nome da tabela intermediária. O segundo parâmetro é um closure que define o valor a retornar no model se as informações da tabela intermediária estiverem disponíveis:

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

Se sua tabela intermediária usa acessadores diferentes de `pivot`, você pode usar o método `whenPivotLoadedAs`:

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

### Adicionar metadados

Alguns padrões de API JSON exigem que você adicione metadados às respostas de recurso e coleção de recursos. Isso geralmente inclui `links` para o recurso ou recursos relacionados, ou alguns metadados sobre o próprio recurso. Se você precisar retornar metadados adicionais sobre o recurso, basta incluí-los no método `toArray`. Por exemplo, você pode precisar adicionar informações de `links` ao converter coleções de recursos:

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

Ao adicionar metadados extras ao seu recurso, você não precisa se preocupar em sobrescrever as chaves `links` ou `meta` que são adicionadas automaticamente ao retornar respostas paginadas. Quaisquer outros `links` que você adicionar serão mesclados com os `links` adicionados pela resposta de paginação.

#### Metadados de nível superior

Às vezes você pode querer adicionar certos metadados à resposta do recurso quando o recurso é retornado como um recurso de nível superior. Isso geralmente inclui informações de meta para toda a resposta. Você pode adicionar um método `with` à sua classe de recurso para definir metadados. Este método deve retornar um array de metadados que será incluído na resposta do recurso quando o recurso for renderizado como um recurso de nível superior:

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

#### Adicionar metadados ao construir recursos

Você também pode adicionar dados de nível superior ao construir uma instância de recurso em um controller. Todos os recursos podem usar o método `additional` para aceitar um array de dados que devem ser adicionados à resposta do recurso:

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

## Recurso de resposta

Como você sabe, recursos podem ser retornados diretamente no controller:

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

Se você quiser definir as informações de header da resposta, código de status, etc., obtenha o objeto de resposta chamando o método `toResponse()` para definí-los.
