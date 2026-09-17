# Modificadores

> Este documento se apoia bastante em [LearnKu](https://learnku.com). Muito obrigado ao LearnKu por contribuir com a comunidade PHP.

Acessores e modificadores permitem formatar valores de atributos de um model quando você obtém ou define certos valores em uma instância.

## Acessores e modificadores

### Definir um acessor

Para definir um acessor, você precisa criar um método `getFooAttribute` no model, e o campo `Foo` a ser acessado precisa estar nomeado em "camel case". Neste exemplo, definiremos um acessor para o atributo `first_name`. Esse acessor é chamado automaticamente quando o model tenta obter o atributo `first_name`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Obtém o nome do usuário.
     *
     * @param  string  $value
     * @return string
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }
}
```

Como você pode ver, o valor bruto do campo é passado para o acessor, permitindo processá-lo e retornar o resultado. Para obter o valor modificado, você pode acessar o atributo `first_name` na instância do model:

```php
$user = App\User::find(1);

$firstName = $user->first_name;
```

Claro, você também pode usar um acessor para retornar um novo valor calculado com base em atributos existentes:

```php
namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Obtém o nome do usuário.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

### Definir um modificador

Para definir um modificador, defina o método `setFooAttribute` no model. O campo `Foo` deve ser nomeado usando "camel case". Vamos definir novamente um modificador para o atributo `first_name`. Esse modificador será chamado automaticamente quando tentarmos definir o valor do atributo `first_name` no schema:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Define o nome do usuário.
     *
     * @param  string  $value
     * @return void
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strtolower($value);
    }
}
```

Modificadores recebem o valor de um atributo que já foi definido, permitindo que você o modifique e atribua o resultado à propriedade `$attributes` dentro do model. Por exemplo, se tentarmos definir o valor de `first_name` como `Sally`:

```php
$user = App\User::find(1);

$user->first_name = 'Sally';
```

Neste exemplo, o método `setFirstNameAttribute` é chamado com o valor `Sally` como parâmetro. O modificador então aplica `strtolower` e define o resultado do processamento no array interno `$attributes`.

## Conversor de datas

Por padrão, o model converte os campos `created_at` e `updated_at` para instâncias de `Carbon`, que herdam a classe nativa `DateTime` do `PHP` e fornecem vários métodos úteis. Você pode adicionar outras propriedades de data definindo a propriedade `$dates` do model:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Propriedades que devem ser convertidas para formato de data.
     *
     * @var array
     */
    protected $dates = [
        'seen_at',
    ];
}

```

> Dica: você pode desabilitar os timestamps padrão created_at e updated_at definindo a propriedade pública $timestamps do model como false.

Quando um campo está em formato de data, você pode definir o valor como um timestamp `UNIX`, uma string datetime `(Y-m-d)` ou uma instância `DateTime` / `Carbon`. O valor de data será formatado corretamente e salvo no banco.

Como mencionado, quando o atributo buscado está contido na propriedade `$dates`, ele é convertido automaticamente para uma instância `Carbon`, permitindo que você use qualquer método do Carbon nesse atributo:

```php
$user = App\User::find(1);

return $user->deleted_at->getTimestamp();
```

### Formato de tempo

Timestamps serão formatados como `Y-m-d H:i:s`. Se você precisar de um formato customizado, defina a propriedade `$dateFormat` no model. Ela determina como o atributo de data será armazenado no banco e o formato quando o model for serializado para array ou `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class Flight extends Model
{
    /**
     * Esta propriedade deve ser convertida para o tipo nativo.
     *
     * @var string
     */
    protected $dateFormat = 'U';
}
```

## Conversão de tipo de atributos

A propriedade `$casts` no model fornece uma forma conveniente de converter atributos para tipos de dados comuns. `$casts` deve ser um array em que as chaves são os nomes dos atributos a serem convertidos, e os valores são os tipos de dados desejados.
Os tipos suportados são: `integer`, `real`, `float`, `double`, `decimal:<digits>`, `string`, `boolean`, `object`, `array`, `collection`, `date`, `datetime` e `timestamp`. Ao converter para o tipo `decimal`, você precisa definir o número de casas decimais, por exemplo: `decimal:2`.

Como exemplo, vamos converter o atributo `is_admin` armazenado no banco como inteiro (`0` ou `1`) para boolean:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'is_admin' => 'boolean',
    ];
}
```

Agora, ao acessar `is_admin`, embora o valor armazenado no banco seja inteiro, o retorno será sempre convertido para boolean:

```php
$user = App\User::find(1);

if ($user->is_admin) {
    //
}
```

### Conversão de tipo customizada

Models trazem várias conversões comuns embutidas. Entretanto, às vezes é necessário converter dados para tipos customizados. Isso pode ser feito definindo uma classe que implemente a interface `CastsAttributes`.

Classes que implementam essa interface devem definir previamente os métodos `get` e `set`. O método `get` é responsável por converter os dados brutos obtidos do banco para o tipo correspondente, enquanto `set` converte os dados para o tipo de banco correspondente para armazenamento. Por exemplo, vamos reimplementar a conversão interna `json` como uma conversão customizada:

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * Converte os dados extraídos.
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * Converte para o valor a ser armazenado.
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }
}
```

Depois de definir um cast customizado, ele pode ser anexado a um atributo do model usando o nome da classe:

```php
<?php

namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Propriedades que devem ser convertidas.
     *
     * @var array
     */
    protected $casts = [
        'options' => Json::class,
    ];
}
```

#### Conversão para value object

Você não só pode converter dados para tipos nativos, como também pode converter para objetos. Essas duas conversões customizadas são definidas de forma muito parecida. Porém, o método `set` da classe de conversão que converte para um objeto precisa retornar um array de pares chave/valor, usado para definir os valores armazenáveis originais no model correspondente.

Como exemplo, defina uma classe de conversão customizada para converter múltiplos atributos do model em um único value object `Address`, assumindo que `Address` tenha duas propriedades públicas `lineOne` e `lineTwo`:

```php
<?php

namespace App\Casts;

use App\Address;
use Hyperf\Contract\CastsAttributes;

class AddressCaster implements CastsAttributes
{
    /**
     * Converte os dados extraídos.
     */
    public function get($model, $key, $value, $attributes): Address
    {
        return new Address(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * Converte para o valor a ser armazenado.
     */
    public function set($model, $key, $value, $attributes)
    {
        return [
            'address_line_one' => $value->lineOne,
            'address_line_two' => $value->lineTwo,
        ];
    }
}
```

Após a conversão para value object, quaisquer alterações no value object serão sincronizadas automaticamente de volta para o model antes de ele ser salvo:

```php
<?php
$user = App\User::find(1);

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#10000';

$user->save();

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#10000'
//];
```

**A implementação aqui é diferente do Laravel. Se ocorrer o uso a seguir, preste atenção**

```php
$user = App\User::find(1);

var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Valor do Endereço',
//    'address_line_two' => '#10000'
//];

$user->address->lineOne = 'Updated Address Value';
$user->address->lineTwo = '#20000';

// Após modificar diretamente o campo de address, isso não entra em vigor imediatamente em attributes, mas você pode obter os dados modificados diretamente via $user->address.
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Valor do Endereço',
//    'address_line_two' => '#10000'
//];

// Quando salvamos os dados ou removemos os dados, attributes mudará para os dados modificados.
$user->save();
var_dump($user->getAttributes());
//[
//    'address_line_one' => 'Updated Address Value',
//    'address_line_two' => '#20000'
//];
```

Se, após modificar `address`, você não quiser salvar ou quiser obter `address_line_one` via `address->lineOne`, você também pode usar o método a seguir:

```php
$user = App\User::find(1);
$user->address->lineOne = 'Updated Address Value';
$user->syncAttributes();
var_dump($user->getAttributes());
```

Claro, se você ainda precisar que `attributes` seja sincronizado após modificar o `value`, pode tentar o seguinte. Primeiro, implementamos um `UserInfo` e herdamos de `CastsValue`.

```php
namespace App\Caster;

use Hyperf\Database\Model\CastsValue;

/**
 * @property string $name
 * @property int $gender
 */
class UserInfo extends CastsValue
{
}
```

Depois implementamos o `UserInfoCaster` correspondente:

```php
<?php

declare(strict_types=1);

namespace App\Caster;

use Hyperf\Contract\CastsAttributes;
use Hyperf\Collection\Arr;

class UserInfoCaster implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): UserInfo
    {
        return new UserInfo($model, Arr::only($attributes, ['name', 'gender']));
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return [
            'name' => $value->name,
            'gender' => $value->gender,
        ];
    }
}

```

Quando modificamos UserInfo da forma abaixo, conseguimos sincronizar os dados modificados para attributes:

```php
/** @var User $user */
$user = User::query()->find(100);
$user->userInfo->name = 'John1';
var_dump($user->getAttributes()); // ['name' => 'John1']
```

#### Conversão somente de entrada

Às vezes você pode precisar apenas converter valores escritos no model, sem processar os valores lidos do model. Um exemplo típico de conversão somente de entrada é "hashing". Classes de conversão somente de entrada precisam implementar a interface `CastsInboundAttributes` e só precisam implementar o método `set`.

```php
<?php

namespace App\Casts;

use Hyperf\Contract\CastsInboundAttributes;

class Hash implements CastsInboundAttributes
{
    /**
     * Algoritmo de hash.
     *
     * @var string
     */
    protected $algorithm;

    /**
     * Cria uma nova instância da classe de conversão.
     */
    public function __construct($algorithm = 'md5')
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Converte para o valor a ser armazenado.
     */
    public function set($model, $key, $value, $attributes)
    {
        return hash($this->algorithm, $value);
    }
}
```

#### Parâmetros de conversão

Ao anexar um cast customizado a um model, você pode especificar o parâmetro do cast. Para passar parâmetros, use `:` para separar os parâmetros do nome da classe e use vírgulas para separar múltiplos parâmetros. Esses parâmetros serão passados para o construtor da classe de conversão:

```php
<?php
namespace App;

use App\Casts\Json;
use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Propriedades que devem ser convertidas.
     *
     * @var array
     */
    protected $casts = [
        'secret' => Hash::class.':sha256',
    ];
}
```

### Conversão de Array e `JSON`

Conversões do tipo `array` são muito úteis quando você armazena dados `JSON` serializados no banco. Por exemplo: se o seu banco tiver um campo do tipo `JSON` ou `TEXT` serializado como `JSON`, e você adicionar uma conversão `array` no model, o valor será automaticamente convertido para um array `PHP` quando você acessar o atributo:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Propriedades que devem ser convertidas.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
}
```

一Depois que a conversão é definida, ao acessar o atributo `options` ele será automaticamente desserializado do tipo `JSON` para um array `PHP`. Quando você define o valor de `options`, o array informado também é automaticamente serializado e armazenado como `JSON`:

```php
$user = App\User::find(1);

$options = $user->options;

$options['key'] = 'value';

$user->options = $options;

$user->save();
```

### Conversão de tipo date

Ao usar atributos `date` ou `datetime`, você pode especificar o formato da data. Esse formato será usado quando models forem serializados como arrays ou `JSON`:

```php
<?php

namespace App;

use Hyperf\DbConnection\Model\Model;

class User extends Model
{
    /**
     * Propriedades que devem ser convertidas.
     *
     * @var array
     */
    protected $casts = [
         'created_at' => 'datetime:Y-m-d',
    ];
}
```

### Conversão durante a query

Há situações em que você precisa converter atributos específicos durante a execução da query, como quando você busca dados de uma tabela. Como exemplo, considere a query a seguir:

```php
use App\Post;
use App\User;

$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->get();
```

No result set obtido por essa query, o atributo `last_posted_at` será uma string. Seria mais conveniente se fizéssemos uma conversão do tipo `date` durante a query. Você pode fazer isso usando o método `withCasts`:

```php
$users = User::select([
    'users.*',
    'last_posted_at' => Post::selectRaw('MAX(created_at)')
            ->whereColumn('user_id', 'users.id')
])->withCasts([
    'last_posted_at' => 'date'
])->get();
```

`

