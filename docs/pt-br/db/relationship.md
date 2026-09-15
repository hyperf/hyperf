# Associação de Model

## Definindo associação

Associações são apresentadas como métodos na classe de model do `Hyperf`. Assim como o próprio model do `Hyperf`, associações também podem ser usadas como `query builder` poderoso, fornecendo capacidades poderosas de encadeamento e query. Por exemplo, podemos adicionar uma restrição às chamadas encadeadas associadas a role:

```php
$user->role()->where('level', 1)->get();
```

### Um para um

Um-para-um é o relacionamento mais básico. Por exemplo, um model `User` pode estar associado a um model `Role`. Para definir essa associação, precisamos escrever um método `role` no model `User`. Chame o método `hasOne` dentro do método `role` e retorne seu resultado:

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

O primeiro parâmetro do método `hasOne` é o nome da classe do model associado. Uma vez que as associações do model são definidas, podemos usar as propriedades dinâmicas do `Hyperf` para obter os registros relacionados. Propriedades dinâmicas permitem que você acesse métodos de relacionamento como se fossem propriedades definidas no model:

```php
$role = User::query()->find(1)->role;
```

### Um para muitos

Uma associação "um para muitos" é usada para definir um único model com qualquer número de outros models associados. Por exemplo, um autor pode ter escrito múltiplos livros. Como com todos os outros relacionamentos do `Hyperf`, a definição de um relacionamento um-para-muitos é escrever um método no model `Hyperf`:

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

Lembre-se que o `Hyperf` vai determinar automaticamente as propriedades de chave estrangeira do model `Book`. Por convenção, o `Hyperf` vai usar a forma "snake case" do nome do model proprietário, mais o sufixo `_id` como o campo de chave estrangeira. Portanto, no exemplo acima, o `Hyperf` vai assumir que a chave estrangeira correspondente a `User` no model `Book` é `user_id`.

Uma vez que o relacionamento é definido, a coleção de comentários pode ser obtida acessando a propriedade `books` do model `User`. Lembre-se, já que o Hyperf fornece "propriedades dinâmicas", podemos acessar os métodos associados como se fossem propriedades do model:

```php
$books = User::query()->find(1)->books;

foreach ($books as $book) {
    //
}
```

Claro, já que todas as associações também podem ser usadas como query builders, você pode usar chamadas encadeadas para adicionar restrições adicionais ao método books:

```php
$book = User::query()->find(1)->books()->where('title', 'Mastering the Hyperf framework in one month')->first();
```

### Um para muitos (inverso)

Agora que podemos obter todas as obras de um autor, vamos definir uma associação para obter seu autor através do livro. Essa associação é o inverso da associação `hasMany` e precisa ser definida no model filho usando o método `belongsTo`:

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

Após essa relação ser definida, podemos obter o model `User` associado acessando a "propriedade dinâmica" do author do model `Book`:

```php
$book = Book::find(1);

echo $book->author->name;
```

### Muitos para muitos

Associações muitos-para-muitos são um pouco mais complicadas do que associações `hasOne` e `hasMany`. Por exemplo, um usuário pode ter muitas roles, e essas roles também são compartilhadas por outros usuários. Por exemplo, muitos usuários podem ter a role de "Administrador". Para definir essa associação, três tabelas de banco de dados são necessárias: `users`, `roles` e `role_user`. A tabela `role_user` é nomeada alfabeticamente com base nos dois models associados, e contém os campos `user_id` e `role_id`.

Associações muitos-para-muitos são definidas chamando o resultado retornado pelo método interno `belongsToMany`. Por exemplo, definimos o método `roles` no model `User`:

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

Uma vez que a relação é definida, você pode obter as roles do usuário através da propriedade dinâmica `roles`:

```php
$user = User::query()->find(1);

foreach ($user->roles as $role) {
    //
}
```

Claro, como todos os outros models relacionais, você pode usar o método `roles` para adicionar restrições às queries usando chamadas encadeadas:

```php
$roles = User::find(1)->roles()->orderBy('name')->get();
```

Como mencionado anteriormente, para determinar o nome da tabela de junção relacional, o `Hyperf` vai concatenar os nomes dos dois models relacionais em ordem alfabética. Claro, você também pode ignorar essa convenção e passar o segundo parâmetro para o método belongsToMany:

```php
return $this->belongsToMany(Role::class, 'role_user');
```

Além de customizar o nome da tabela de junção, você também pode definir o nome da chave do campo na tabela passando parâmetros adicionais ao método `belongsToMany`. O terceiro parâmetro é o nome da chave estrangeira do model que define essa associação na tabela de junção, e o quarto parâmetro é o nome da chave estrangeira do outro model na tabela de junção:

```php
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

#### Obtendo campos da tabela intermediária

Como você acabou de aprender, relacionamentos muitos-para-muitos requerem uma tabela intermediária para suporte, e o `Hyperf` fornece alguns métodos úteis para interagir com essa tabela. Por exemplo, digamos que nosso objeto `User` tem múltiplos objetos `Role` associados a ele. Após obter esses objetos associados, os dados na tabela intermediária podem ser acessados usando o atributo `pivot` do model:

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

Deve-se notar que cada objeto de model `Role` que obtemos recebe automaticamente um atributo `pivot`, que representa um objeto de model da tabela intermediária e pode ser usado como qualquer outro model do `Hyperf`.

Por padrão, o objeto `pivot` contém apenas as chaves primárias dos dois models relacionais. Se você tiver campos adicionais na tabela intermediária, você deve especificá-los ao definir a relação:

```php
return $this->belongsToMany(Role::class)->withPivot('column1', 'column2');
```

Se você quiser que a tabela intermediária mantenha automaticamente os timestamps `created_at` e `updated_at`, então adicione o método `withTimestamps` ao definir a associação:

```php
return $this->belongsToMany(Role::class)->withTimestamps();
```

#### Nome de atributo `pivot` customizado

Como mencionado anteriormente, propriedades de tabelas intermediárias podem ser acessadas usando o atributo `pivot`. Porém, você é livre para customizar o nome dessa propriedade para melhor refletir seu uso na sua aplicação.

Por exemplo, se sua aplicação inclui usuários que podem se inscrever, pode haver um relacionamento muitos-para-muitos entre usuários e blogs. Se este for o caso, você pode querer nomear o acessador da tabela intermediária `subscription` em vez de `pivot`. Isso pode ser feito usando o método `as` ao definir o relacionamento:

```php
return $this->belongsToMany(Podcast::class)->as('subscription')->withTimestamps();
```

Uma vez definido, você pode acessar os dados da tabela intermediária com um nome customizado:

```php
$users = User::with('podcasts')->get();

foreach ($users->flatMap->podcasts as $podcast) {
    echo $podcast->subscription->created_at;
}
```

#### Filtrando relações pela tabela intermediária

Ao definir um relacionamento, você também pode usar os métodos `wherePivot` e `wherePivotIn` para filtrar os resultados retornados por `belongsToMany`:

```php
return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);
```


## Pré-carregamento

Ao acessar um relacionamento do `Hyperf` como um atributo, os dados associados são "lazy loaded" (carregados de forma tardia). Isso significa que os dados associados não são de fato carregados até que a propriedade seja acessada pela primeira vez. Porém, o `Hyperf` pode "pré-carregar" associações filhas ao consultar o model pai. O eager loading pode aliviar o problema de query N+1. Para ilustrar o problema de query N + 1, considere um model `User` associado a uma `Role`:

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

Agora, vamos obter todos os usuários e suas roles correspondentes

```php
$users = User::query()->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

Esse loop vai executar uma query para obter todos os usuários, e então executar uma query para obter roles para cada usuário. Se tivermos 10 pessoas, esse loop vai executar 11 queries: 1 para usuários e 10 queries adicionais para roles.

Felizmente, conseguimos reduzir a operação para apenas 2 queries usando eager loading. No momento da query, você pode usar o método with para especificar quais associações você quer pré-carregar:

```php
$users = User::query()->with('role')->get();

foreach ($users as $user){
    echo $user->role->name;
}
```

Neste exemplo, apenas duas queries são executadas

```
SELECT * FROM `user`;

SELECT * FROM `role` WHERE id in (1, 2, 3, ...);
```

## Associação polimórfica

Associação polimórfica permite que o model de destino associe múltiplos models com a ajuda de relacionamentos de associação.

### Um para um (polimórfico)

#### Estrutura de tabela

Uma associação polimórfica um-para-um é similar a uma associação um-para-um simples, porém o model de destino pode pertencer a múltiplos models em uma única associação.
Por exemplo, Book e User podem compartilhar um relacionamento com o model Image. Usar uma associação polimórfica um-para-um permite usar uma lista de imagens única tanto para Book quanto para User. Vamos ver primeiro a estrutura da tabela:

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

O campo imageable_id na tabela image terá significados diferentes dependendo do imageable_type. Por padrão, imageable_type é diretamente o nome da classe do model relevante.

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

#### Obtendo a associação

Depois de definir o model como acima, podemos obter o model correspondente através do relacionamento do model.

Por exemplo, obtemos uma imagem de um usuário.

```php
use App\Model\User;

$user = User::find(1);

$image = $user->image;
```

Ou obtemos uma imagem correspondente a um usuário ou livro. `imageable` vai obter o `User` ou `Book` correspondente de acordo com `imageable_type`.

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

#### Obtendo a associação

Obtenha todas as imagens do usuário

```php
use App\Model\User;

$user = User::query()->find(1);
foreach ($user->images as $image) {
    // ...
}
```

### Mapeamento polimórfico customizado

Por padrão, o framework exige que `type` armazene o nome da classe do model correspondente. Por exemplo, o `imageable_type` acima deve ser o `User::class` e `Book::class` correspondentes, mas obviamente na aplicação real, isso é muito inconsistente. Conveniente. Então podemos customizar a relação de mapeamento para desacoplar o banco de dados e a estrutura interna da aplicação.

```php
use App\Model;
use Hyperf\Database\Model\Relations\Relation;
Relation::morphMap([
    'user' => Model\User::class,
    'book' => Model\Book::class,
]);
```

Porque `Relation::morphMap` vai permanecer residente em memória após a modificação, podemos criar o mapeamento de relacionamento correspondente quando o projeto inicia. Podemos criar o seguinte listener:

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
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

### Pré-carregamento aninhado de associação `morphTo`

Se você quiser carregar um relacionamento `morphTo`, juntamente com relacionamentos aninhados de várias entidades que o relacionamento pode retornar, você pode usar o método `with` em conjunto com o método `morphWith` do relacionamento `morphTo`.

Por exemplo, planejamos pré-carregar o relacionamento book.user de image.

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

A query SQL correspondente é a seguinte:

```sql
// Search all pictures
select * from `images`;
// Query the user list corresponding to the image
select * from `user` where `user`.`id` in (1, 2);
// Query the list of books corresponding to the image
select * from `book` where `book`.`id` in (1, 2, 3);
// Query the user list corresponding to the book list
select * from `user` where `user`.`id` in (1, 2);
```

### Query relacional polimórfica

Para consultar a existência de uma associação `MorphTo`, você pode usar o método `whereHasMorph` e seu método correspondente:

O exemplo a seguir vai consultar a lista de imagens com book ou user de ID 1.

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
