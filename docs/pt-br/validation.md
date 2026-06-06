# Validador

## Prefácio

> [hyperf/validation](https://github.com/hyperf/validation) é derivado de [illuminate/validation](https://github.com/illuminate/validation). Fizemos algumas modificações, mas mantivemos as mesmas regras de validação. Obrigado à equipe de desenvolvimento do Laravel por implementar um componente de validação tão poderoso e fácil de usar.

## Instalação

### Instalar pacote do componente

```bash
composer require hyperf/validation
```

### Adicionar middleware

Você precisa adicionar a configuração do middleware global `Hyperf\\Validation\\Middleware\\ValidationMiddleware` no arquivo `config/autoload/middlewares.php` do servidor que usa o componente de validação. A seguir está um exemplo de middleware global para o servidor `http`:

```php
<?php
return [
    // The following http string corresponds to the value corresponding to the name attribute of each server in config/autoload/server.php, which means that the corresponding middleware configuration is only applied to the server
    'http' => [
        // Configure your global middleware in the array, the order is based on the order of the array
        \Hyperf\Validation\Middleware\ValidationMiddleware::class
        // Other middleware goes here
    ],
];
```

> Se o middleware global não estiver configurado corretamente, o uso de `FormRequest` pode não funcionar.

### Adicionar handler de exceção

O handler de exceção trata principalmente as exceções `Hyperf\\Validation\\ValidationException`. Nós fornecemos `Hyperf\\Validation\\ValidationExceptionHandler` para processar. Você precisa configurar manualmente esse handler no seu projeto adicionando-o ao arquivo `config/autoload/exceptions.php`. Claro, você também pode personalizar seu próprio handler.

```php
<?php
return [
    'handler' => [
        // This corresponds to your current server name
        'http' => [
            \Hyperf\Validation\ValidationExceptionHandler::class,
        ],
    ],
];
```

### Publicar arquivos de idioma do validator

Por causa do recurso de múltiplos idiomas, este componente depende do componente [hyperf/translation](https://github.com/hyperf/translation). Se você ainda não adicionou o arquivo de configuração do componente de tradução, você pode executar o comando a seguir para publicar o arquivo de configuração. Se a configuração já existir, basta publicar o arquivo de idioma do componente de validação:

Publicar os arquivos do componente de tradução:

```bash
php bin/hyperf.php vendor:publish hyperf/translation
```

Publicar os arquivos do componente de validação:

```bash
php bin/hyperf.php vendor:publish hyperf/validation
```

Executar os comandos acima publicará o arquivo de idioma do validador `validation.php` no diretório de idiomas correspondente; `en` refere-se ao arquivo de idioma inglês e `zh_CN` ao arquivo de idioma em chinês simplificado. Você pode personalizar o conteúdo do arquivo.

```
/storage
    /languages
        /en
            validation.php
        /zh_CN
            validation.php

```

## Uso

### Validação via FormRequest

Para cenários de validação mais complexos, você pode criar um `FormRequest`. O form request é uma classe de request personalizada que contém a lógica de validação. Você pode criar uma classe de validação chamada FooRequest executando o seguinte comando:

```bash
php bin/hyperf.php gen:request FooRequest
```

A classe de validação será gerada no diretório `app\\Request`. Se o diretório não existir, ele será criado automaticamente ao executar o comando.
Em seguida, adicionamos algumas regras de validação no método `rules` dessa classe:

```php
/**
 * Get the validation rules applied to the request
 */
public function rules(): array
{
    return [
        'foo' => 'required|max:255',
        'bar' => 'required',
    ];
}
```

Então, como a regra de validação entra em vigor? Basta declarar a classe de request como parâmetro no método do controller usando type hints. Assim, o form request recebido será validado antes do método do controller ser chamado — o que significa que você não precisa escrever lógica de validação no controller e consegue desacoplar bem as duas partes do código:

```php
<?php
namespace App\Controller;

use App\Request\FooRequest;

class IndexController
{
    public function index(FooRequest $request)
    {
        // The incoming request is verified...

        // Get the verified data...
        $validated = $request->validated();
    }
}
```

Se a validação falhar, o validador lançará uma exceção `Hyperf\\Validation\\ValidationException`. Você pode tratar a exceção adicionando uma classe personalizada de tratamento de exceções. Ao mesmo tempo, também fornecemos o handler `Hyperf\\Validation\\ValidationExceptionHandler`, e você pode configurar diretamente esse handler para tratar a exceção. No entanto, o handler padrão pode não atender às suas necessidades. Você pode personalizar o comportamento após falha de validação customizando o handler de acordo com a situação.

#### Mensagem de erro personalizada

Você pode personalizar as mensagens de erro usadas pelo form request sobrescrevendo o método `messages`. Esse método deve retornar um array de pares atributo/regra e suas mensagens de erro correspondentes:

```php
/**
 * Get the error message of the defined validation rule
 */
public function messages(): array
{
    return [
        'foo.required' => 'foo is required',
        'bar.required' => 'bar is required',
    ];
}
```

#### Atributos personalizados

Se você quiser substituir a parte `:attribute` da mensagem por um nome de atributo personalizado, você pode sobrescrever o método `attributes` para especificar um nome customizado. Esse método retornará um array de nomes de atributos e seus respectivos pares chave-valor com o nome personalizado:

```php
/**
 * Get custom attributes for validation errors
 */
public function attributes(): array
{
    return [
        'foo' => 'foo of request',
    ];
}
```

### Criar um validador manualmente

Se você não quiser usar a validação automática do `FormRequest`, pode obter a factory do validador injetando a interface `ValidatorFactoryInterface` e então criar manualmente uma instância do validador pelo método `make`:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function foo(RequestInterface $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'foo' => 'required',
                'bar' => 'required',
            ],
            [
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        if ($validator->fails()){
            // Handle exception
            $errorMessage = $validator->errors()->first();
        }
        // Do something
    }
}
```

O primeiro parâmetro passado para o método `make` é o dado a ser validado, e o segundo parâmetro são as regras de validação para esse dado.

#### Mensagem de erro personalizada

Se necessário, você também pode usar mensagens de erro personalizadas em vez das mensagens padrão da validação. Existem várias maneiras de especificar informações customizadas. Primeiro, você pode passar mensagens customizadas como terceiro parâmetro do método `make`:

```php
<?php
$messages = [
    'required' => 'The :attribute field is required.',
];

$validator = $this->validationFactory->make($request->all(), $rules, $messages);
```

Neste exemplo, o placeholder `:attribute` será substituído pelo nome real do campo em validação. Além disso, você também pode usar outros placeholders na mensagem de validação. Por exemplo:

```php
$messages = [
    'same' => 'The :attribute and :other must match.',
    'size' => 'The :attribute must be exactly :size.',
    'between' => 'The :attribute value :input is not between :min-:max.',
    'in' => 'The :attribute must be one of the following types: :values',
];
```

#### Especificar mensagens personalizadas para um atributo

Às vezes, você pode querer personalizar mensagens de erro apenas para campos específicos. Basta adicionar `.` após o nome do campo para especificar regras de validação com mensagens personalizadas:

```php
$messages = [
    'email.required' => 'We need to know your e-mail address!',
];
```

#### Especificar mensagens personalizadas no arquivo PHP

Na maioria dos casos, você pode especificar mensagens personalizadas no arquivo, em vez de passá-las diretamente para o `Validator`. Para isso, coloque suas mensagens no array `custom` do arquivo de idioma `storage/languages/xx/validation.php`.

#### Especificar atributos personalizados em arquivos PHP

Se você quiser substituir a parte `:attribute` da mensagem de validação por um nome de atributo personalizado, você pode especificar o nome customizado no array `attributes` do arquivo de idioma `storage/languages/xx/validation.php`:

```php
'attributes' => [
    'email' => 'email address',
],
```

### Hook pós-validação

O validador também permite adicionar callbacks que serão executados após a validação ter sucesso, para que você possa realizar uma próxima etapa de validação e até adicionar mais mensagens de erro à coleção. Para usar, basta chamar o método `after` na instância do validator:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function foo(RequestInterface $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'foo' => 'required',
                'bar' => 'required',
            ],
            [
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        $validator->after(function ($validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field','Something is wrong with this field!');
            }
        });

        if ($validator->fails()) {
            //
        }
    }
}
```

## Tratando mensagens de erro

Ao chamar o método `errors` na instância de `Validator`, você obtém uma instância de `Hyperf\\Support\\MessageBag`, que possui vários métodos convenientes para lidar com mensagens de erro.

### Ver a primeira mensagem de erro de um campo específico

Para ver a primeira mensagem de erro de um campo específico, você pode usar o método `first`:

```php
$errors = $validator->errors();

echo $errors->first('foo');
```

### Ver todas as mensagens de erro de um campo específico

Se você precisar obter um array com todas as mensagens de erro de um campo especificado, você pode usar o método `get`:

```php
foreach ($errors->get('foo') as $message) {
    //
}
```

Se você quiser validar campos do tipo array no formulário, você pode usar `*` para obter todas as mensagens de erro de cada elemento do array:

```php
foreach ($errors->get('foo.*') as $message) {
    //
}
```

### Ver todas as mensagens de erro de todos os campos

Se você quiser obter todas as mensagens de erro de todos os campos, você pode usar o método `all`:

```php
foreach ($errors->all() as $message) {
    //
}
```

### Verificar se um campo específico contém uma mensagem de erro

O método `has` pode ser usado para determinar se há uma mensagem de erro no campo especificado:

```php
if ($errors->has('foo')) {
    //
}
```

### Cenário

O validator adiciona uma função de cenário, para que possamos modificar facilmente as regras de validação conforme necessário.

> Este recurso requer uma versão deste componente maior ou igual a 2.2.7
Crie um `SceneRequest` da seguinte forma:

```php
<?php
declare(strict_types=1);
namespace App\Request;
use Hyperf\Validation\Request\FormRequest;
class SceneRequest extends FormRequest
{
    protected array $scenes = [
        'foo' => ['username'],
        'bar' => ['username', 'password'],
    ];
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => 'required',
            'gender' => 'required',
        ];
    }
}
```

Quando usamos normalmente, todas as regras de validação são usadas, isto é, `username` e `gender` são obrigatórios.

Podemos definir o cenário para que esse request valide apenas o campo obrigatório `username`.

Se configurarmos `Hyperf\\Validation\\Middleware\\ValidationMiddleware` e injetarmos `SceneRequest` no método,
isso fará com que a entrada seja validada diretamente no middleware;
por isso precisamos obter o `SceneRequest` do container no método para alternar o cenário.

```php
<?php
namespace App\Controller;
use App\Request\DebugRequest;
use App\Request\SceneRequest;
use Hyperf\HttpServer\Annotation\AutoController;
#[AutoController(prefix: 'foo')]
class FooController extends Controller
{
    public function scene()
    {
        $request = $this->container->get(SceneRequest::class);
        $request->scene('foo')->validateResolved();
        return $this->response->success($request->all());
    }
}
```

Mas podemos usar a annotation `Scene` para alternar.

```php
<?php

namespace App\Controller;

use App\Request\DebugRequest;
use App\Request\SceneRequest;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Validation\Annotation\Scene;

#[AutoController(prefix: 'foo')]
class FooController extends Controller
{
    #[Scene(scene:'bar1')]
    public function bar1(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }

    #[Scene(scene:'bar2', argument: 'request')] // bind $request
    public function bar2(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }

    #[Scene(scene:'bar3', argument: 'request')] // bind $request
    #[Scene(scene:'bar3', argument: 'req')] // bind $req
    public function bar3(SceneRequest $request, DebugRequest $req)
    {
        return $this->response->success($request->all());
    }

    #[Scene()] // the default scene is method name, The effect is equivalent to #[Scene(scene: 'bar1')]
    public function bar1(SceneRequest $request)
    {
        return $this->response->success($request->all());
    }
}
```

## Regras de validação

A seguir está uma lista de regras válidas e suas funções:

##### accepted

O valor do campo em validação deve ser `yes`, `on`, `1` ou `true`, o que é útil quando você precisa "concordar com os termos de serviço".

##### active_url

O campo em validação deve ser um URL ativo de acordo com a função `dns_get_record` do `PHP`, com o registro `A` ou `AAAA`.

##### after:date

O campo em validação deve ser uma data posterior à data fornecida, e a data será passada para a função `strtotime` do PHP:

```php
'start_date' => 'required|date|after:tomorrow'
```

Em vez de passar uma string de data para `strtotime`, você pode especificar outro campo para comparar as datas:

```php
'finish_date' => 'required|date|after:start_date'
```

##### after_or_equal:date

O campo em validação deve ser uma data maior ou igual à data fornecida. Para mais informações, consulte a regra `after:date`.

##### alpha

O campo em validação deve conter apenas letras (incluindo caracteres chineses).

##### alpha_dash

O campo em validação pode conter letras (incluindo caracteres chineses) e números, além de hífens e underscores.

##### alpha_num

O campo em validação deve conter apenas letras (incluindo caracteres chineses) ou números.

##### array

O campo em validação deve ser um array PHP.

##### bail

Se a primeira regra de validação falhar, pare de executar as demais regras.

##### before:date

Ao contrário de `after:date`, o campo em validação deve ser uma data anterior à data especificada, e a data será passada para a função `strtotime` do PHP.

##### before_or_equal:date

O campo em validação deve ser uma data menor ou igual à data fornecida. A data será passada para a função `strtotime` do PHP.

##### between:min,max

Verifica se o tamanho do campo está entre os valores mínimo e máximo informados. Strings, números, arrays e arquivos podem usar essa regra da mesma forma que a regra `size`:

'name' =>'required|between:1,20'

##### boolean

O campo em validação deve poder ser convertido para um valor booleano, aceitando entradas como true, false, 1, 0, "1" e "0".

##### confirmed

O campo em validação deve ter um campo correspondente foo_confirmation. Por exemplo, se o campo em validação é password, você deve informar um campo password_confirmation correspondente.

##### date

O campo em validação deve ser uma data válida de acordo com a função `strtotime` do PHP

 ##### date_equals:date

 O campo em validação deve ser igual à data fornecida, e a data será passada para a função `strtotime` do PHP.

 ##### date_format:format

 O campo em validação deve corresponder ao formato especificado. Você pode usar as funções `date` ou `date_format` do PHP para validar o campo.

 ##### different:field

 O campo em validação deve ser um valor diferente do campo especificado.

 ##### digits:value

 O campo em validação deve ser numérico e o comprimento deve ser o valor especificado em value.

 ##### digits_between:min,max

 O comprimento do campo em validação deve estar entre os valores mínimo e máximo.

 ##### dimensions

 O tamanho da imagem validada deve atender às restrições especificadas pelos parâmetros:

 ```php
 'avatar' => 'dimensions:min_width=100,min_height=200'
 ```

 Restrições válidas incluem: `min_width`, `max_width`, `min_height`, `max_height`, `width`, `height`, `ratio`.

 `ratio` restringe a proporção largura/altura, que pode ser expressa pela expressão `3/2` ou pelo número de ponto flutuante `1.5`:

 ```php
 'avatar' => 'dimensions:ratio=3/2'
 ```

 Como essa regra exige múltiplos parâmetros, você pode usar o método `Rule::dimensions` para construir a regra:

 ```php
 use Hyperf\Validation\Rule;

 public function rules(): array
 {
 return [
            'avatar' => [
               'required',
               Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3 / 2),
            ],
        ];
 }
 ```
 ##### distinct

  Ao processar arrays, o campo em validação não pode conter valores duplicados:

 ```php
 'foo.*.id' => 'distinct'
 ```

 ##### email

  O campo em validação deve ser um endereço de e-mail formatado corretamente.

 ##### exists:table,column

  O campo em validação deve existir na tabela de dados especificada.

  Uso básico:

 ```php
 'state' => 'exists:states'
```

Se a opção `column` não for especificada, o nome do campo será usado.

Especificar um nome de coluna personalizado:

```php
'state' => 'exists:states,abbreviation'
```

Às vezes, você pode precisar especificar a conexão de banco de dados a ser usada na consulta `exists`. Isso pode ser feito usando `.` com o prefixo da conexão antes do nome da tabela, ou resolvendo automaticamente ao especificar o nome da classe do model:

```php
// Pre-database connection method
'email' => 'exists:connection.staff,email'

// Automatically resolve model class names
'email' => 'exists:StaffModel::class,email'
```

Se você quiser personalizar a query executada pelas regras de validação, pode usar a classe `Rule` para definir as regras. Neste exemplo, também especificamos as regras de validação em forma de array, em vez de usar o caractere `|` para separá-las:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'email' => [
        'required',
        Rule::exists('staff')->where(function ($query) {
            $query->where('account_id', 1);
        }),
    ],
]);
```

##### file

O campo em validação deve ser um arquivo enviado com sucesso.

##### filled

O campo em validação não pode estar vazio se existir.

##### gt:field

O campo em validação deve ser maior que o campo `field` fornecido, e os dois campos devem ter o mesmo tipo; aplicável a strings, números, arrays e arquivos, de forma semelhante à regra `size`.

##### gte:field

O campo em validação deve ser maior ou igual ao campo `field` fornecido, e os dois campos devem ter o mesmo tipo; aplicável a strings, números, arrays e arquivos, de forma semelhante à regra `size`.

##### image

O arquivo em validação deve ser uma imagem (`jpeg`, `png`, `bmp`, `gif` ou `svg`).

##### in:foo,bar...

O valor do campo em validação deve estar na lista fornecida. Como essa regra geralmente exige que façamos implode do array, podemos usar `Rule::in` para construir a regra:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'zones' => [
        'required',
        Rule::in(['first-zone','second-zone']),
    ],
]);
```

##### in_array:anotherfield

O campo em validação deve existir como um valor de outro campo.

##### integer

O campo em validação deve ser um inteiro.

##### ip

O campo em validação deve ser um endereço IP.

##### ipv4

O campo em validação deve ser um endereço IPv4.

##### ipv6

O campo em validação deve ser um endereço IPv6.

##### json

O campo em validação deve ser uma string JSON válida.

##### lt:field

O campo em validação deve ser menor que o campo `field` fornecido, e os dois campos devem ter o mesmo tipo; aplicável a strings, números, arrays e arquivos, de forma semelhante à regra `size`.

##### lte:field

O campo em validação deve ser menor ou igual ao campo `field` fornecido, e os dois campos devem ter o mesmo tipo; aplicável a strings, números, arrays e arquivos, de forma semelhante à regra `size`.

##### max:value

O campo em validação deve ser menor ou igual ao valor máximo; para campos do tipo string, numérico, array e arquivo, isso é consistente com o uso da regra `size`.

##### mimetypes: text/plain...

O arquivo em validação deve corresponder a um dos tipos de arquivo `MIME` fornecidos:

```php
'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime'
```

Para determinar o tipo `MIME` do arquivo enviado, o componente lerá o conteúdo do arquivo para inferir o tipo `MIME`, o que pode ser diferente do tipo `MIME` informado pelo cliente.

##### mimes:foo,bar,...

O tipo `MIME` do arquivo em validação deve ser um dos tipos de extensão listados na regra.
Uso básico das regras `MIME`:

```php
'photo' => 'mimes:jpeg,bmp,png'
```

Embora você especifique apenas a extensão, esta regra na verdade valida o tipo `MIME` do arquivo obtido pela leitura do conteúdo do arquivo.
A lista completa de tipos `MIME` e suas extensões correspondentes pode ser encontrada aqui: [mime types](http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types)

##### min:value

Em contraste com `max:value`, o campo em validação deve ser maior ou igual ao valor mínimo. Para campos do tipo string, numérico, array e arquivo, isso é consistente com o uso da regra `size`.

##### not_in:foo,bar,...

O valor do campo em validação não pode estar na lista fornecida. Assim como na regra `in`, podemos usar o método `Rule::notIn` para construir a regra:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'toppings' => [
        'required',
        Rule::notIn(['sprinkles','cherries']),
    ],
]);
```

##### not_regex:pattern

O campo em validação não pode corresponder à expressão regular fornecida.

Nota: ao usar o modo `regex/not_regex`, as regras devem ser passadas em um array, em vez de separadas por pipe, especialmente quando a expressão regular contém o caractere `|`.

##### nullable

O campo em validação pode ser `null`, o que é útil ao validar dados primitivos que podem ser `null`, como inteiros ou strings.

##### numeric

O campo em validação deve ser numérico.

##### present

O campo em validação deve estar presente nos dados de entrada, mas pode estar vazio.

##### regex:pattern

O campo em validação deve corresponder à expressão regular fornecida.
No nível mais baixo, esta regra usa a função `preg_match` do `PHP`. Portanto, o padrão especificado precisa seguir o formato exigido por `preg_match` e conter um separador válido. Por exemplo:

```php
 'email' => 'regex:/^.+@.+$/i'
```

Nota: ao usar o modo `regex/not_regex`, as regras devem ser passadas em um array, em vez de separadas por pipe, especialmente quando a expressão regular contém o caractere `|`.

##### required

O valor do campo em validação não pode ser vazio, e o valor é considerado vazio nos seguintes casos:
- O valor é `null`
- O valor é uma string vazia
- O valor é um array vazio ou um objeto `Countable` vazio
- O valor é um arquivo enviado, mas o caminho está vazio

##### required_if:anotherfield,value,â€¦

O campo em validação deve existir quando `anotherfield` for igual ao valor especificado `value` e não pode estar vazio.
Se você quiser construir condições mais complexas para a regra `required_if`, pode usar o método `Rule::requiredIf`, que aceita um booleano ou uma closure. Ao passar uma closure, ela deve retornar `true` ou `false` para indicar se o campo é obrigatório:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($request->all(), [
    'role_id' => Rule::requiredIf($request->user()->is_admin),
]);

$validator = $this->validationFactory->make($request->all(), [
    'role_id' => Rule::requiredIf(function () use ($request) {
        return $request->user()->is_admin;
    }),
]);
```

##### required_unless:anotherfield,value,â€¦

A menos que o campo `anotherfield` seja igual a `value`, o campo em validação não pode estar vazio.

##### required_with:foo,bar,â€¦

O campo em validação só é obrigatório se qualquer outro campo especificado existir.

##### required_with_all:foo,bar,â€¦

O campo em validação só é obrigatório se todos os campos especificados existirem.

##### required_without:foo,bar,â€¦

O campo em validação só é obrigatório se qualquer campo especificado não existir.

##### required_without_all:foo,bar,â€¦

O campo em validação só é obrigatório se todos os campos especificados não existirem.

##### same:field

O campo informado e o campo em validação devem ser iguais.

##### size:value

O campo em validação deve ter um tamanho que corresponda ao valor informado `value`. Para strings, `value` é o número de caracteres; para números, `value` é um inteiro; para arrays, `value` é o tamanho do array; para arquivos, `value` é o tamanho do arquivo em kilobytes (KB).

##### starts_with:foo,bar,...

O campo em validação deve começar com um valor fornecido.

##### string

O campo em validação deve ser uma string. Se o campo puder estar vazio, você precisa atribuir a regra `nullable` ao campo.

##### timezone

O campo em validação deve ser um identificador de fuso horário válido, de acordo com a função `timezone_identifiers_list` do `PHP`.

##### unique:table,column,except,idColumn

O campo em validação deve ser único em uma tabela de dados. Se a opção `column` não for especificada, o nome do campo será usado como `column` padrão.

1. Especificar um nome de coluna personalizado:

```php
'email' => 'unique:users,email_address'
```

2. Conexão de banco de dados personalizada:
Às vezes, você pode precisar personalizar a conexão de banco de dados usada pelo validator. Como você pode ver acima, definir `unique:users` como regra fará com que seja usada a conexão de banco de dados padrão para consultar o banco. Para sobrescrever a conexão padrão, use "." após o nome da tabela para especificar a conexão, ou resolva automaticamente especificando o nome da classe do model:

```php
// Pre-database connection method
'email' => 'unique:connection.users,email_address'

// Automatically resolve model class names
'email' => 'unique:UserModel::class,email_address'
```

3. Forçar uma regra de unique que ignora um `ID` específico:
Às vezes você pode querer ignorar um `ID` durante a verificação de unicidade. Por exemplo, considere uma interface de "atualização" que inclui nome de usuário, e-mail e localização. Você quer validar se o e-mail é único. Alterar o nome de usuário não altera o e-mail. Você não quer lançar um erro de validação só porque o usuário já possui aquele e-mail. Você só quer lançar um erro quando o e-mail fornecido já tiver sido usado por outra pessoa.

Para dizer ao validator para ignorar o ID do usuário, você pode usar a classe Rule para definir essa regra. Também precisamos especificar a regra de validação em um array, em vez de usar `|`:

```php
use Hyperf\Validation\Rule;

$validator = $this->validationFactory->make($data, [
    'email' => [
        'required',
        Rule::unique('users')->ignore($user->id),
    ],
]);
```

Além de passar o valor da chave primária da instância do model para o método `ignore`, você também pode passar a instância inteira do model. O componente irá extrair automaticamente o valor da chave primária da instância:

```php
Rule::unique('users')->ignore($user)
```

Se sua tabela de dados usa uma chave primária diferente de `id`, você pode especificar o nome do campo ao chamar o método `ignore`:

```php
'email' => Rule::unique('users')->ignore($user->id,'user_id')
```

Por padrão, a regra `unique` verifica a unicidade da coluna que corresponde ao nome do atributo a ser validado. No entanto, você pode especificar um nome de coluna diferente como segundo parâmetro do método `unique`:

```php
Rule::unique('users','email_address')->ignore($user->id),
```

4. Adicionar uma cláusula `where` adicional:

Você também pode especificar restrições adicionais na query usando o método `where` para personalizar a consulta. Por exemplo, vamos adicionar uma restrição que valida que `account_id` é 1:

```php
'email' => Rule::unique('users')->where(function ($query) {
    $query->where('account_id', 1);
})
```

##### url

O campo em validação deve ser uma URL válida.

##### uuid

O campo em validação deve ser um identificador único universal (UUID) válido no padrão RFC 4122 (versões 1, 3, 4 ou 5).

##### sometimes

Adicionar regras condicionais
Validar quando existir

Em alguns cenários, você pode querer validar apenas quando um determinado campo existir. Para implementar rapidamente, adicione a regra `sometimes` à lista de regras:

```php
$validator = $this->validationFactory->make($data, [
    'email' => 'sometimes|required|email',
]);
```

No exemplo acima, o campo `email` só será validado se existir no array `$data`.

Nota: se você tentar validar um campo que sempre existe, mas pode estar vazio, consulte as considerações sobre campos opcionais.

Validação condicional complexa

Às vezes, você pode querer adicionar regras de validação com base em uma lógica condicional mais complexa. Por exemplo, talvez você queira exigir um determinado campo apenas quando o valor de outro campo for maior que 100, ou talvez precise exigir que ambos os campos tenham um determinado valor apenas quando o outro campo existir. Adicionar esse tipo de regra não precisa ser um problema. Primeiro, crie uma regra estática (que não mudará) na instância do `Validator`:

```php
$validator = $this->validationFactory->make($data, [
    'email' => 'required|email',
    'games' => 'required|numeric',
]);
```

Vamos supor que nossa aplicação web atende colecionadores de jogos. Se um colecionador se cadastrar e possuir mais de 100 jogos, queremos que ele explique por que tem tantos jogos — por exemplo, talvez ele tenha uma loja de jogos usados, ou apenas goste de colecionar. Para adicionar essa condição, podemos usar o método `sometimes` na instância do `Validator`:

```php
$v->sometimes('reason','required|max:500', function($input) {
    return $input->games >= 100;
});
```

O primeiro parâmetro passado para o método `sometimes` é o nome do campo que queremos validar condicionalmente, e o segundo parâmetro é a regra que queremos adicionar. Se a closure passada como terceiro parâmetro retornar `true`, a regra será adicionada. Esse método facilita a construção de validações condicionais complexas e você pode até adicionar validação condicional para vários campos ao mesmo tempo:

```php
$v->sometimes(['reason','cost'],'required', function($input) {
    return $input->games >= 100;
});
```

Nota: o parâmetro `$input` passado para a closure é uma instância de `Hyperf\\Support\\Fluent` e pode ser usado para acessar inputs e arquivos.

### Validar entrada de array

Validar campos de entrada de um formulário em array deixa de ser um problema. Por exemplo, se o request HTTP recebido contém o campo `photos[profile]`, você pode validar assim:

```php
$validator = $this->validationFactory->make($request->all(), [
    'photos.profile' => 'required|image',
]);
```

Também podemos validar cada elemento do array. Por exemplo, para validar que cada e-mail em um array é único, podemos fazer assim (esse tipo de campo enviado é um array bidimensional, como `person[][email ]` ou `person[test][email]`):

```php
$validator = $this->validationFactory->make($request->all(), [
    'person.*.email' => 'email|unique:users',
    'person.*.first_name' => 'required_with:person.*.last_name',
]);
```

Da mesma forma, no arquivo de idioma você também pode usar o caractere `*` para especificar a mensagem de validação, de modo que você possa usar uma única mensagem para definir regras de validação para campos em array:

```php
'custom' => [
    'person.*.email' => [
        'unique' => 'E-mail address of each person must be unique',
    ]
],
```

### Regras de validação personalizadas

#### Registrar regras de validação personalizadas

O componente `Validation` usa um mecanismo de eventos para implementar regras de validação personalizadas. Definimos o evento `ValidatorFactoryResolved`. Tudo o que você precisa fazer é definir um listener para `ValidatorFactoryResolved` e implementar o registro do validator dentro do listener. O exemplo é o seguinte:

```php
namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Hyperf\Validation\Validator;

#[Listener]
class ValidatorFactoryResolvedListener implements ListenerInterface
{

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event): void
    {
        /** @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;
        // registered foo validator
        $validatorFactory->extend('foo', function (string $attribute, mixed $value, array $parameters, Validator $validator): bool {
            return $value == 'foo';
        });
        // When creating a custom validation rule, you may sometimes need to define a custom placeholder for error messages. Here is an extension of the :foo placeholder
        $validatorFactory->replacer('foo', function (string $message, string $attribute, string $rule, array $parameters): array|string {
            return str_replace(':foo', $attribute, $message);
        });
    }
}
```

#### Mensagem de erro personalizada

Você também precisa definir mensagens de erro para regras personalizadas. Você pode usar arrays de mensagens inline ou adicionar entradas no arquivo de idioma de validação para obter essa funcionalidade. A mensagem deve ser colocada no primeiro nível do array, não no array `custom`, que é usado apenas para armazenar informações de erro específicas por atributo. Pegue como exemplo o validator personalizado `foo` da seção anterior:

Em `storage/languages/en/validation.php`, adicione o seguinte conteúdo ao array do arquivo:

```php
    'foo' => 'The :attribute must be foo',
```

Em `storage/languages/zh_CN/validation.php`, adicione o seguinte conteúdo ao array do arquivo:

```php
    'foo' => ':attribute must be foo',
```

#### Uso do validador personalizado

```php
<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class DemoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // use foo validator
            'name' => 'foo'
        ];
    }
}
```

