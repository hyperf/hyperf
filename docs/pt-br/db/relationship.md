# Associação de models

## Definir associações

Associações são apresentadas como métodos na classe de model do `Hyperf`. Assim como o próprio model do `Hyperf`, associações também podem ser usadas como um poderoso `query builder`, oferecendo recursos fortes de encadeamento e consulta. Por exemplo, podemos anexar uma restrição a chamadas encadeadas da associação role:

```php
$user->role()->where('level', 1)->get();
```

### Um para um

Um-para-um é o relacionamento mais básico. Por exemplo, um model `User` pode estar associado a um model `Role`. Para definir essa associação, precisamos criar um método `role` no model `User`. Chame o método `hasOne` dentro do método `role` e retorne o resultado:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function role()
    {
        return $this->hasOne(Role::class, 'user_id', 'id');
    }
}
```

O primeiro parâmetro de `hasOne` é o nome da classe do model associado. Depois de definir as associações, podemos usar as propriedades dinâmicas do `Hyperf` para obter os registros relacionados. Propriedades dinâmicas permitem acessar métodos de relacionamento como se fossem propriedades definidas no model:

```php
$role = User::query()->find(1)->role;
```

### Um para muitos

Uma associação "um-para-muitos" é usada para definir um único model que tem qualquer número de outros models associados. Por exemplo, um autor pode ter escrito vários livros. Assim como em todos os outros relacionamentos do `Hyperf`, a definição de um relacionamento um-para-muitos é criar um método no model:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function books()
    {
        return $this->hasMany(Book::class, 'user_id', 'id');
    }
}
```

Lembre-se que o `Hyperf` determinará automaticamente as propriedades de chave estrangeira do model `Book`. Por convenção, o `Hyperf` usa a forma "snake case" do nome do model "dono", adicionando o sufixo `_id` como campo da chave estrangeira. Portanto, no exemplo acima, o `Hyperf` assumirá que a chave estrangeira correspondente a `User` no model `Book` é `user_id`.

Após definir o relacionamento, a collection de livros pode ser obtida acessando a propriedade `books` do model `User`. Lembre-se: como o Hyperf fornece "propriedades dinâmicas", podemos acessar métodos associados como propriedades do model:

```php
$books = User::query()->find(1)->books;

foreach ($books as $book) {
    //
}
```

Claro, como todas as associações também podem ser usadas como construtores de consulta, você pode usar chamadas encadeadas para adicionar restrições adicionais ao método books:

```php
$book = User::query()->find(1)->books()->where('title', 'Mastering the Hyperf framework in one month')->first();
```

### Um para muitos (reverso)

Agora que conseguimos obter todas as obras de um autor, vamos definir uma associação para obter o autor a partir do livro. Essa associação é o inverso de `hasMany` e precisa ser definida no model filho usando o método `belongsTo`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class Book extends Model
{
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
```

Depois de definir esse relacionamento, podemos obter o model `User` associado acessando a "propriedade dinâmica" author do model `Book`:

```php
$book = Book::find(1);

echo $book->author->name;
```

### Muitos para muitos

Associações muitos-para-muitos são um pouco mais complexas do que `hasOne` e `hasMany`. Por exemplo, um usuário pode ter vários papéis (roles), e esses papéis também podem ser compartilhados por outros usuários. Por exemplo, muitos usuários podem ter o papel de "Administrator". Para definir essa associação, são necessárias três tabelas: `users`, `roles` e `role_user`. A tabela `role_user` é nomeada em ordem alfabética pelos dois models associados e contém os campos `user_id` e `role_id`.

Associações muitos-para-muitos são definidas chamando o método `belongsToMany`. Por exemplo, definimos o método `roles` no model `User`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
```

Depois de definir o relacionamento, você pode obter os roles do usuário via a propriedade dinâmica `roles`:

```php
$user = User::query()->find(1);

foreach ($user->roles as $role) {
    //
}
```

Claro, assim como nos outros relacionamentos, você pode usar o método `roles` para adicionar restrições usando chamadas encadeadas:

```php
$roles = User::find(1)->roles()->orderBy('name')->get();
```

Como mencionado anteriormente, para determinar o nome da tabela de junção do relacionamento, o `Hyperf` concatenará os nomes dos dois models em ordem alfabética. Claro, você também pode ignorar essa convenção e passar o segundo parâmetro para `belongsToMany`:

```php
return $this->belongsToMany(Role::class, 'role_user');
```

Além de customizar o nome da tabela de junção, você também pode definir os nomes das chaves passando parâmetros adicionais para `belongsToMany`. O terceiro parâmetro é o nome da chave estrangeira do model que define essa associação na tabela de junção, e o quarto é o nome da chave estrangeira do outro model na tabela de junção:

```php
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

#### Obter campos da tabela intermediária

Como você acabou de ver, relacionamentos muitos-para-muitos exigem uma tabela intermediária, e o `Hyperf` fornece alguns métodos úteis para interagir com ela. Por exemplo, digamos que nosso objeto `User` tenha múltiplos objetos `Role` associados. Após obter esses objetos, os dados da tabela intermediária podem ser acessados usando o atributo `pivot` do model:

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

É importante notar que cada objeto `Role` retornado recebe automaticamente um atributo `pivot`, que representa um objeto de model da tabela intermediária e pode ser usado como outros models do `Hyperf`.

Por padrão, o objeto `pivot` contém apenas as chaves primárias dos dois models do relacionamento. Se você tiver campos adicionais na tabela intermediária, deve especificá-los ao definir o relacionamento:

```php
return $this->belongsToMany(Role::class)->withPivot('column1', 'column2');
```

Se você quiser que a tabela intermediária mantenha automaticamente os timestamps `created_at` e `updated_at`, adicione o método `withTimestamps` ao definir a associação:

```php
return $this->belongsToMany(Role::class)->withTimestamps();
```

#### Nome customizado para o atributo `pivot`

Como mencionado, propriedades da tabela intermediária podem ser acessadas usando o atributo `pivot`. Entretanto, você pode customizar o nome dessa propriedade para refletir melhor seu uso na aplicação.

Por exemplo, se seu app inclui usuários que podem se inscrever, pode haver um relacionamento muitos-para-muitos entre usuários e blogs. Nesse caso, talvez você prefira nomear o acesso da tabela intermediária como `subscription` em vez de `pivot`. Isso pode ser feito usando o método `as` ao definir o relacionamento:

```php
return $this->belongsToMany(Podcast::class)->as('subscription')->withTimestamps();
```

Depois disso, você pode acessar os dados da tabela intermediária com o nome customizado:

```php
$users = User::with('podcasts')->get();

foreach ($users->flatMap->podcasts as $podcast) {
    echo $podcast->subscription->created_at;
}
```

#### Filtrar relações pela tabela intermediária

Ao definir um relacionamento, você também pode usar os métodos `wherePivot` e `wherePivotIn` para filtrar os resultados retornados por `belongsToMany`:

```php
return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);
```

## Carregamento antecipado

Ao acessar um relacionamento do `Hyperf` como atributo, os dados associados são carregados de forma "lazy". Isso significa que eles não são carregados até que a propriedade seja acessada pela primeira vez. Entretanto, o `Hyperf` pode "precarregar" associações filhas ao consultar o model pai. O eager loading pode aliviar o problema de N+1 queries. Para ilustrar o problema N + 1, considere um model `User` associado a um `Role`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    public function role()
    {
        return $this->hasOne(Role::class, 'user_id', 'id');
    }
}
```

Agora, vamos obter todos os usuários e seus roles correspondentes:

```php
$users = User::query()->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

Esse loop executará uma query para obter todos os usuários e depois executará uma query para obter o role de cada usuário. Se tivermos 10 pessoas, esse loop rodará 11 queries: 1 para users e 10 queries adicionais para roles.

Felizmente, conseguimos reduzir isso para apenas 2 queries usando eager loading. No momento da consulta, você pode usar o método with para especificar quais associações deseja precarregar:

```php
$users = User::query()->with('role')->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

Neste exemplo, apenas duas queries são executadas:

```
SELECT * FROM `user`;

SELECT * FROM `role` WHERE id in (1, 2, 3, ...);
```

## Associação polimórfica

Associações polimórficas permitem que um model alvo se associe a múltiplos models com a ajuda de relacionamentos.

### Um para um (polimórfico)

#### Estrutura de tabelas

Uma associação um-para-um polimórfica é parecida com um relacionamento um-para-um simples. Porém, o model alvo pode pertencer a múltiplos models em um único relacionamento.
Por exemplo, Book e User podem compartilhar um relacionamento com o model Image. Usar um-para-um polimórfico permite usar uma lista única de imagens tanto para Book quanto para User. Vamos olhar primeiro a estrutura das tabelas:

```
book
  id - integer
  title - string

user 
  id - integer
  name - string

image
  id - integer
  url - string
  imageable_id - integer
  imageable_type - string
```

O campo imageable_id na tabela image terá significados diferentes dependendo de imageable_type. Por padrão, imageable_type é diretamente o nome da classe do model correspondente.

#### Exemplo de model

```php
<?php
namespace App\Model;

class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }
}

class Book extends Model
{
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}

class User extends Model
{
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
```

#### Obter associação

Após definir o model como acima, podemos obter o model correspondente através do relacionamento.

Por exemplo, podemos obter uma imagem de um usuário:

```php
use App\Model\User;

$user = User::find(1);

$image = $user->image;
```

Ou podemos obter o usuário ou livro correspondente a uma imagem. `imageable` retornará o `User` ou `Book` correspondente de acordo com `imageable_type`.

```php
use App\Model\Image;

$image = Image::find(1);

$imageable = $image->imageable;
```

### Um para muitos (polimórfico)

#### Exemplo de model

```php
<?php
namespace App\Model;

class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }
}

class Book extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}

class User extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
```

#### Obter associação

Obtenha todas as imagens de um usuário:

```php
use App\Model\User;

$user = User::query()->find(1);
foreach ($user->images as $image) {
    // ...
}
```

### Mapeamento polimórfico customizado

Por padrão, o framework exige que o campo `type` armazene o nome da classe do model correspondente. Por exemplo, o `imageable_type` acima deve ser `User::class` ou `Book::class`, mas em aplicações reais isso pode ser bem inconveniente. Assim, podemos customizar o relacionamento de mapeamento para desacoplar o banco e a estrutura interna da aplicação.

```php
use App\Model;
use Hyperf\Database\Model\Relations\Relation;
Relation::morphMap([
    'user' => Model\User::class,
    'book' => Model\Book::class,
]);
```

Como `Relation::morphMap` ficará residente na memória após a alteração, podemos criar o mapeamento correspondente quando o projeto iniciar. Podemos criar o listener a seguir:

```php
<?php

declare(strict_types=1);
/**
 * Este arquivo faz parte do Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Listener;

use App\Model;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

#[Listener]
class MorphMapRelationListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        Relation::morphMap([
            'user' => Model\User::class,
            'book' => Model\Book::class,
        ]);
    }
}

```

### Carregamento aninhado do relacionamento `morphTo`

Se você quiser carregar um relacionamento `morphTo` junto com relacionamentos aninhados de várias entidades que ele pode retornar, você pode usar o método `with` em conjunto com o método `morphWith` do relacionamento `morphTo`.

Por exemplo, queremos precarregar o relacionamento book.user de image:

```php

use App\Model\Book;
use App\Model\Image;
use Hyperf\Database\Model\Relations\MorphTo;

$images = Image::query()->with([
    'imageable' => function (MorphTo $morphTo) {
        $morphTo->morphWith([
            Book::class => ['user'],
        ]);
    },
])->get();
```

A query SQL correspondente é:

```sql
// Busca todas as imagens
select * from `images`;
// Consulta a lista de users correspondente às imagens
select * from `user` where `user`.`id` in (1, 2);
// Consulta a lista de books correspondente às imagens
select * from `book` where `book`.`id` in (1, 2, 3);
// Consulta a lista de users correspondente à lista de books
select * from `user` where `user`.`id` in (1, 2);
```

### Consulta relacional polimórfica

Para consultar a existência de um relacionamento `MorphTo`, você pode usar o método `whereHasMorph` e seus métodos correspondentes:

O exemplo abaixo consultará a lista de imagens cujo book ou user tem ID 1.

```php
use App\Model\Book;
use App\Model\Image;
use App\Model\User;
use Hyperf\Database\Model\Builder;

$images = Image::query()->whereHasMorph(
    'imageable',
    [
        User::class,
        Book::class,
    ],
    function (Builder $query) {
        $query->where('imageable_id', 1);
    }
)->get();
```

