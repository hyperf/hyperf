# Construtor de consultas

## Introdução

O construtor de consultas de banco de dados do Hyperf fornece uma interface conveniente para criar e executar consultas. Ele pode ser usado para realizar a maioria das operações de banco em uma aplicação e funciona em todos os sistemas de banco de dados suportados.

O construtor de consultas do Hyperf usa binding de parâmetros do PDO para proteger sua aplicação contra ataques de SQL injection. Portanto, não é necessário sanitizar strings passadas como bindings.

Aqui são apresentados apenas alguns tutoriais comumente usados. Tutoriais mais completos podem ser vistos no site oficial do Laravel.
[Laravel Query Builder](https://laravel.com/docs/5.8/queries)

## Obter resultados

```php
use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM user;');
$users = Db::table('user')->get();
$users = Db::table('user')->select('name', 'gender as user_gender')->get();
```

O método `Db::select()` retorna um array, e o método `get` retorna `Hyperf\Collection\Collection`. Os elementos são `stdClass`, então os dados de cada elemento podem ser acessados com o código a seguir:

```php
<?php

foreach ($users as $user) {
    echo $user->name;
}
```

### Converter o resultado para formato array

Em alguns cenários, você pode querer usar `Array` em vez da estrutura de objeto `stdClass` no resultado, e o `Eloquent` remove o `FetchMode` padrão configurado via configuração. Nesse caso, você pode alterar a configuração escutando o evento `Hyperf\Database\Events\StatementPrepared` através de um listener:

```php
<?php
declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Events\StatementPrepared;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use PDO;

#[Listener]
class FetchModeListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            StatementPrepared::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof StatementPrepared) {
            $event->statement->setFetchMode(PDO::FETCH_ASSOC);
        }
    }
}
```

### Obter o valor de uma coluna

Se você quiser obter uma collection contendo os valores de uma única coluna, pode usar o método `pluck`. No exemplo a seguir, vamos obter uma collection de títulos na tabela roles:

```php
<?php
use Hyperf\DbConnection\Db;

$names = Db::table('user')->pluck('name');

foreach ($names as $name) {
    echo $names;
}

```

Você também pode especificar chaves customizadas para os campos na collection retornada:

```php
<?php
use Hyperf\DbConnection\Db;

$roles = Db::table('roles')->pluck('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}

```

### Resultados em chunks

Se você precisa processar milhares de registros do banco de dados, pode considerar usar o método `chunk`. Esse método busca o conjunto de resultados em pequenos blocos e passa cada bloco para uma função `closure` para processamento. Ele é muito útil quando um `Command` está processando milhares de dados. Por exemplo, podemos dividir os dados da tabela user em blocos que processam 100 registros por vez:

```php
<?php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        //
    }
});
```

Você pode interromper a busca de chunks retornando `false` no closure:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {

    return false;
});
```

Se você estiver atualizando registros do banco enquanto faz chunk nos resultados, os chunks podem não ser como o esperado. Portanto, ao atualizar registros em chunks, é melhor usar o método chunkById. Esse método automaticamente pagina os resultados com base na chave primária do registro:

```php
use Hyperf\DbConnection\Db;

Db::table('user')->where('gender', 1)->chunkById(100, function ($users) {
    foreach ($users as $user) {
        Db::table('user')
            ->where('id', $user->id)
            ->update(['update_time' => time()]);
    }
});
```

> Qualquer alteração nas chaves primárias ou estrangeiras pode afetar a consulta em blocos ao atualizar ou remover registros dentro do callback do chunk. Isso pode fazer com que registros não sejam incluídos no resultado em chunks.

### Consulta agregada

O framework também fornece métodos de agregação como `count`, `max`, `min`, `avg`, `sum`.

```php
use Hyperf\DbConnection\Db;

$count = Db::table('user')->count();
```

#### Determinar se o registro existe

Além de usar o método `count` para determinar se existe resultado para uma condição, você também pode usar os métodos `exists` e `doesntExist`:

```php
return Db::table('orders')->where('finalized', 1)->exists();

return Db::table('orders')->where('finalized', 1)->doesntExist();
```

## Consultas

### Especificar uma instrução Select

Claro que você nem sempre vai querer obter todas as colunas da tabela. Usando o método select, você pode customizar uma query select para consultar os campos desejados:

```php
$users = Db::table('user')->select('name', 'email as user_email')->get();
```

O método `distinct` força a query a retornar resultados únicos:

```php
$users = Db::table('user')->distinct()->get();
```

Se você já tem uma instância de query builder e quer adicionar um campo a uma query existente, pode usar o método addSelect:

```php
$query = Db::table('users')->select('name');

$users = $query->addSelect('age')->get();
```

## Expressões raw

Às vezes você precisa usar expressões raw em uma query, por exemplo para implementar `COUNT(0) AS count`, o que exige o uso do método `raw`.

```php
use Hyperf\DbConnection\Db;

$res = Db::table('user')->select('gender', Db::raw('COUNT(0) AS `count`'))->groupBy('gender')->get();
```

### Métodos nativos

Os métodos a seguir podem ser usados no lugar de `Db::raw` para inserir expressões raw em várias partes da query.

O método `selectRaw` pode ser usado no lugar de `select(Db::raw(...))`. O segundo parâmetro é opcional e o valor é um array de parâmetros bindados:

```php
$orders = Db::table('order')
    ->selectRaw('price * ? as price_with_tax', [1.0825])
    ->get();
```

Os métodos `whereRaw` e `orWhereRaw` injetam `where` nativo na query. O segundo parâmetro ainda é opcional, e o valor continua sendo um array de parâmetros bindados:

```php
$orders = Db::table('order')
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->get();
```

Os métodos `havingRaw` e `orHavingRaw` podem ser usados para definir uma string raw como o valor de uma instrução `having`:

```php
$orders = Db::table('order')
    ->select('department', Db::raw('SUM(price) as total_sales'))
    ->groupBy('department')
    ->havingRaw('SUM(price) > ?', [2500])
    ->get();
```

O método `orderByRaw` pode ser usado para definir uma string raw como o valor da cláusula `order by`:

```php
$orders = Db::table('order')
    ->orderByRaw('updated_at - created_at DESC')
    ->get();
```

## Join de tabelas

### Cláusula inner join

O query builder também suporta métodos `join`. Para executar um `"inner join"` básico, você pode usar o método `join` em uma instância do query builder. O primeiro argumento passado para `join` é o nome da tabela que você deseja juntar (join), enquanto os outros argumentos especificam as restrições de campos do join. Você também pode fazer join de múltiplas tabelas em uma única query:

```php
$users = Db::table('users')
    ->join('contacts', 'users.id', '=', 'contacts.user_id')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.*', 'contacts.phone', 'orders.price')
    ->get();
```

### Left join

Se você quiser usar `"left join"` ou `"right join"` em vez de `"inner join"`, use os métodos `leftJoin` ou `rightJoin`. Esses métodos são usados da mesma forma que `join`:

```php
$users = Db::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
$users = Db::table('users')
    ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

### Instrução cross join

Use o método `crossJoin` para fazer um `"cross join"` com o nome da tabela que você deseja juntar. Um cross join produz um produto cartesiano entre a primeira tabela e as tabelas unidas:

```php
$users = Db::table('sizes')
    ->crossJoin('colours')
    ->get();
```

### Instrução de join avançada

Você pode especificar instruções `join` mais avançadas. Por exemplo, passando um `closure` como segundo parâmetro do método `join`. Esse `closure` aceita um objeto `JoinClause`, permitindo definir as restrições do join:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')->orOn(...);
    })
    ->get();
```

Se você quiser usar instruções no estilo `"where"` no join, pode usar os métodos `where` e `orWhere` no join. Esses métodos comparam colunas com valores, em vez de colunas com colunas:

```php
Db::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
                ->where('contacts.user_id', '>', 5);
    })
    ->get();
```

### Subconsulta em join

Você pode usar os métodos `joinSub`, `leftJoinSub` e `rightJoinSub` para fazer join com uma subconsulta. Cada um desses métodos recebe três parâmetros: uma subquery, um alias de tabela e um closure que define os campos relacionados:

```php
$latestPosts = Db::table('posts')
    ->select('user_id', Db::raw('MAX(created_at) as last_post_created_at'))
    ->where('is_published', true)
    ->groupBy('user_id');

$users = Db::table('users')
    ->joinSub($latestPosts, 'latest_posts', function($join) {
        $join->on('users.id', '=', 'latest_posts.user_id');
    })->get();
```

## Consulta combinada

O query builder também fornece um atalho para "unir" duas queries. Por exemplo, você pode criar uma query primeiro e, em seguida, usar o método `union` para fazer union com a segunda query:

```php
$first = Db::table('users')->whereNull('first_name');

$users = Db::table('users')
    ->whereNull('last_name')
    ->union($first)
    ->get();
```

## Cláusulas where

### Cláusula where simples

Ao construir uma query `where`, você pode usar o método `where`. A forma mais básica de chamar `where` é passando três parâmetros: o primeiro é o nome da coluna, o segundo é qualquer operador suportado pelo banco, e o terceiro é o valor a ser comparado com a coluna.

Por exemplo, aqui está uma query para verificar se o valor do campo gender é igual a 1:

```php
$users = Db::table('user')->where('gender', '=', 1)->get();
```

Por conveniência, se você estiver apenas comparando o valor da coluna com um valor fornecido, pode passar o valor diretamente como segundo parâmetro de `where`:

```php
$users = Db::table('user')->where('gender', 1)->get();
```

Claro, você também pode usar outros operadores para escrever cláusulas where:

```php
$users = Db::table('users')->where('gender', '>=', 0)->get();

$users = Db::table('users')->where('gender', '<>', 1)->get();

$users = Db::table('users')->where('name', 'like', 'T%')->get();
```

Você também pode passar um array de condições para o método where:

```php
$users = Db::table('user')->where([
    ['status', '=', '1'],
    ['gender', '=', '1'],
])->get();
```

### Cláusula or

Você pode encadear restrições `where` ou adicionar cláusulas `or` na query. O método `orWhere` aceita os mesmos parâmetros do método `where`:

```php
$users = Db::table('user')
    ->where('gender', 1)
    ->orWhere('name', 'John')
    ->get();
```

### Outras cláusulas where

#### whereBetween

O método `whereBetween` verifica se o valor de um campo está entre dois valores fornecidos:

```php
$users = Db::table('users')->whereBetween('votes', [1, 100])->get();
```

#### whereNotBetween

O método `whereNotBetween` verifica se o valor de um campo está fora dos dois valores fornecidos:

```php
$users = Db::table('users')->whereNotBetween('votes', [1, 100])->get();
```

#### whereIn / whereNotIn

O método `whereIn` valida que o valor de um campo deve existir no array especificado:

```php
$users = Db::table('users')->whereIn('id', [1, 2, 3])->get();
```

O método `whereNotIn` verifica que o valor de um campo não deve existir no array especificado:

```php
$users = Db::table('users')->whereNotIn('id', [1, 2, 3])->get();
```

### Agrupamento de parâmetros

Às vezes você precisa criar cláusulas `where` mais avançadas, como `"where exists"` ou agrupamentos aninhados. O query builder também lida com isso. A seguir, veja um exemplo de agrupamento de restrições entre parênteses:

```php
Db::table('users')->where('name', '=', 'John')
    ->where(function ($query) {
        $query->where('votes', '>', 100)
                ->orWhere('title', '=', 'Admin');
    })
    ->get();
```

Como você pode ver, é passado um `Closure` para o método `where` para construir um agrupamento de restrições. O `Closure` recebe uma instância de query que você pode usar para definir as restrições que devem ser incluídas. O exemplo acima gerará o seguinte SQL:

```sql
select * from users where name = 'John' and (votes > 100 or title = 'Admin')
```

> Você deve chamar esse agrupamento com orWhere para evitar a aplicação acidental de efeitos globais.

#### Cláusula where exists

O método `whereExists` permite usar a instrução SQL `where exists`. O método `whereExists` aceita um parâmetro `Closure` e o closure recebe uma instância do query builder, permitindo definir a query colocada na cláusula `exists`:

```php
Db::table('users')->whereExists(function ($query) {
    $query->select(Db::raw(1))
            ->from('orders')
            ->whereRaw('orders.user_id = users.id');
})
->get();
```

A query acima produzirá a seguinte instrução SQL:

```sql
select * from users
where exists (
    select 1 from orders where orders.user_id = users.id
)
```

#### Cláusula where com JSON

O `Hyperf` também suporta consultas em campos do tipo `JSON` (apenas em bancos que suportam o tipo `JSON`).

```php
$users = Db::table('users')
    ->where('options->language', 'en')
    ->get();

$users = Db::table('users')
    ->where('preferences->dining->meal', 'salad')
    ->get();
```

Você também pode usar `whereJsonContains` para consultar arrays `JSON`:

```php
$users = Db::table('users')
    ->whereJsonContains('options->languages', 'en')
    ->get();
```

Você pode usar `whereJsonLength` para consultar o tamanho de um array `JSON`:

```php
$users = Db::table('users')
    ->whereJsonLength('options->languages', 0)
    ->get();

$users = Db::table('users')
    ->whereJsonLength('options->languages', '>', 1)
    ->get();
```

## Ordenação, agrupamento, limit e offset

### orderBy

O método `orderBy` permite ordenar o conjunto de resultados por uma coluna. O primeiro parâmetro de `orderBy` deve ser a coluna pela qual você quer ordenar, e o segundo controla a direção, que pode ser `asc` ou `desc`.

```php
$users = Db::table('users')
    ->orderBy('name', 'desc')
    ->get();
```

### latest / oldest

Os métodos `latest` e `oldest` permitem ordenar facilmente por data. Por padrão, ele usa a coluna `created_at` como referência. Claro, você também pode passar o nome de uma coluna customizada:

```php
$user = Db::table('users')->latest()->first();
```

### inRandomOrder

O método `inRandomOrder` é usado para ordenar resultados aleatoriamente. Por exemplo, você pode usar esse método para encontrar um usuário aleatório.

```php
$randomUser = Db::table('users')->inRandomOrder()->first();
```

### groupBy / having

Os métodos `groupBy` e `having` podem agrupar resultados. O uso de `having` é muito parecido com o método `where`:

```php
$users = Db::table('users')
    ->groupBy('account_id')
    ->having('account_id', '>', 100)
    ->get();
```

Você pode passar múltiplos argumentos para `groupBy`:

```php
$users = Db::table('users')
    ->groupBy('first_name', 'status')
    ->having('account_id', '>', 100)
    ->get();
```

> Para uma sintaxe mais avançada de having, veja o método havingRaw.

### skip / take

Para limitar o número de resultados retornados, ou pular um número especificado de resultados, você pode usar os métodos `skip` e `take`:

```php
$users = Db::table('users')->skip(10)->take(5)->get();
```

Ou você também pode usar os métodos limit e offset:

```php
$users = Db::table('users')->offset(10)->limit(5)->get();
```

## Instruções condicionais

Às vezes você pode querer executar uma query apenas se uma determinada condição for verdadeira. Por exemplo, você pode querer aplicar um `where` apenas se um determinado valor existir na request. Você pode fazer isso usando o método `when`:

```php
$role = $request->input('role');

$users = Db::table('users')
    ->when($role, function ($query, $role) {
        return $query->where('role_id', $role);
    })
    ->get();
```

O método `when` executa o closure fornecido apenas se o primeiro argumento for `true`. Se o primeiro argumento for `false`, então o closure não será executado.

Você pode passar outro closure como terceiro parâmetro do método `when`. Esse closure será executado se o primeiro argumento for `false`. Para ilustrar, vamos configurar a ordenação padrão de uma query:

```php
$sortBy = null;

$users = Db::table('users')
    ->when($sortBy, function ($query, $sortBy) {
        return $query->orderBy($sortBy);
    }, function ($query) {
        return $query->orderBy('name');
    })
    ->get();
```

## Insert

O query builder também fornece o método `insert` para inserir registros no banco. O método `insert` aceita um array com nomes de colunas e valores:

```php
Db::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

Você também pode passar um array para `insert` para inserir múltiplos registros na tabela:

```php
Db::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);
```

### ID auto incremental

Se a tabela tiver um `ID` auto incremental, use o método `insertGetId` para inserir o registro e retornar o valor do `ID`.

```php
$id = Db::table('users')->insertGetId(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

## Update

Além de inserir registros, o query builder também pode atualizar registros existentes via o método `update`. Assim como `insert`, o método `update` aceita um array contendo os campos e valores a serem atualizados. Você pode restringir a query de `update` com uma cláusula `where`:

```php
Db::table('users')->where('id', 1)->update(['votes' => 1]);
```

### Update ou insert

Às vezes você pode querer atualizar um registro existente no banco ou criar um registro correspondente caso ele não exista. Nesse caso, pode ser usado o método `updateOrInsert`. Ele aceita dois parâmetros: um array de condições para encontrar o registro e um array de pares chave/valor contendo os dados a serem atualizados.

O método `updateOrInsert` primeiro tenta encontrar um registro correspondente usando os pares chave/valor do primeiro argumento. Se o registro existir, usa os valores do segundo parâmetro para atualizar. Se não for encontrado, um novo registro é inserido.

```php
Db::table('users')->updateOrInsert(
    ['email' => 'john@example.com', 'name' => 'John'],
    ['votes' => '2']
);
```

### Atualizar campos JSON

Ao atualizar um campo JSON, você pode usar a sintaxe `->` para acessar o valor correspondente no objeto JSON, o que só é suportado no MySQL 5.7+:

```php
Db::table('users')->where('id', 1)->update(['options->enabled' => true]);
```

### Incremento e decremento automáticos

O query builder também fornece métodos convenientes para incrementar ou decrementar uma coluna. Eles oferecem uma interface mais expressiva e concisa do que escrever manualmente instruções `update`.

Ambos os métodos recebem ao menos um parâmetro: a coluna que precisa ser modificada. O segundo parâmetro é opcional e controla a quantidade pela qual a coluna será incrementada ou decrementada:

```php
Db::table('users')->increment('votes');

Db::table('users')->increment('votes', 5);

Db::table('users')->decrement('votes');

Db::table('users')->decrement('votes', 5);
```

Você também pode especificar campos para atualizar durante a operação:

```php
Db::table('users')->increment('votes', 1, ['name' => 'John']);
```

## Delete

O query builder também pode remover registros de uma tabela usando o método `delete`. Antes de usar `delete`, você pode adicionar uma cláusula `where` para restringir a remoção:

```php
Db::table('users')->delete();

Db::table('users')->where('votes', '>', 100)->delete();
```

Se você precisar esvaziar a tabela, pode usar o método `truncate`, que removerá todas as linhas e resetará o `ID` auto incremental para zero:

```php
Db::table('users')->truncate();
```

## Lock pessimista

O query builder também contém algumas funções que podem ajudar a implementar `pessimistic locking` na sintaxe `select`. Para implementar um `"shared lock"` em uma query, você pode usar o método `sharedLock`. Shared locks impedem que colunas de dados selecionadas sejam modificadas até que a transação seja commitada.

```php
Db::table('users')->where('votes', '>', 100)->sharedLock()->get();
```

Alternativamente, você pode usar o método `lockForUpdate`. O lock `"update"` impede que linhas sejam modificadas ou selecionadas por outros shared locks:

```php
Db::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
```
