# View

A renderização de View é implementada pelo componente [hyperf/view](https://github.com/hyperf/view). O componente suporta cinco engines de templates diferentes: `Blade`, `Smarty`, `Twig`, `Plates` e `ThinkTemplate`.

## Instalação

```bash
composer require hyperf/view
```

## Configuração

O arquivo de configuração do componente de view está localizado em `config/autoload/view.php`; se o arquivo de configuração não existir, o seguinte comando pode ser executado para gerá-lo:

```bash
php bin/hyperf.php vendor:publish hyperf/view
```

As seguintes opções de configuração estão disponíveis:

| Configuração        | Tipo     | Valor padrão                            | Observações                      |
| :-----------------: | :------: | :--------------------------------------: | :--------------------------: |
| engine              | string   | Hyperf\View\Engine\BladeEngine::class    | Engine de renderização de View        |
| mode                | string   | Mode::TASK                               | Modo de renderização de View          |
| config.view_path    | string   | Nenhum                                     | Endereço padrão do arquivo de view |
| config.cache_path   | string   | Nenhum                                     | Endereço de cache do arquivo de view      |

Exemplo de formato do arquivo de configuração:

```php
<?php
declare(strict_types=1);

use Hyperf\View\Mode;
use Hyperf\View\Engine\BladeEngine;

return [
    // The rendering engine used
    'engine' => BladeEngine::class,
    // If you don't fill it in, the default is Task mode, it is recommended to use Task mode
    'mode' => Mode::TASK,
    'config' => [
        // If the following folder does not exist, please create it yourself
        'view_path' => BASE_PATH.'/storage/view/',
        'cache_path' => BASE_PATH.'/runtime/view/',
    ],
];
```

### Modo Task

Ao usar o modo `Task`, o componente [hyperf/task](https://github.com/hyperf/task) deve estar instalado e `task_enable_coroutine` deve ser configurado como `false`, caso contrário haverá um problema de consistência de dados entre Coroutines. Consulte a documentação do componente [task](pt-br/task.md).

Além disso, no modo `Task`, o trabalho de renderização da view é feito por um processo `Task Worker`, enquanto o processamento da requisição no controller é concluído por um processo `Worker`. Isso significa que não é possível acessar diretamente na view objetos de dados dependentes de contexto, como `Request` e `Session`. Se você precisar usar dados dependentes de contexto nas suas views, certifique-se de passar os dados do controller através do método `render`.


### Modo Sync

Se você usar o modo `Sync` para renderizar a view, certifique-se de que a engine relevante seja segura para Coroutine, caso contrário haverá problemas de consistência de dados. Recomenda-se usar o modo `Task`, que é mais seguro para dados.

### Configurar recursos estáticos

Se você quiser que o `Swoole` gerencie recursos estáticos, adicione a seguinte configuração em `config/autoload/server.php`.

```
return [
    'settings' => [
        ...
        // static resources
        'document_root' => BASE_PATH.'/public',
        'enable_static_handler' => true,
    ],
];

```

## Engine de renderização de View

As engines de renderização atualmente suportadas oficialmente são `Blade`, `Smarty`, `Twig`, `Plates` e `ThinkTemplate`. A engine de templates não será instalada automaticamente quando o [hyperf/view](https://github.com/hyperf/view) for instalado. Você precisa instalar a engine de templates correspondente além do pacote view.

### Instalar a engine Blade

```bash
composer require hyperf/view-engine
```

Para mais detalhes, consulte a [documentação da engine de view](pt-br/view-engine.md).

Ou use

> duncan3dc/blade usa a biblioteca Support do Laravel, então algumas funções serão incompatíveis, portanto não é recomendado por enquanto

```bash
composer require duncan3dc/blade
```

### Instalar a engine Smarty

```bash
composer require smarty/smarty
```

### Instalar a engine Twig

```bash
composer require twig/twig
```

### Instalar a engine Plates

```bash
composer require league/plates
```

### Instalar a engine ThinkTemplate

```bash
composer require sy-records/think-template
```

### Acessar outras engines de template

Suponha que queiramos conectar uma engine de template virtual chamada `TemplateEngine`; nesse caso, precisamos criar a classe `TemplateEngine` correspondente em qualquer lugar e implementar a interface `Hyperf\View\Engine\EngineInterface`.

```php
<?php

declare(strict_types=1);

namespace App\Engine;

use Hyperf\View\Engine\EngineInterface;

class TemplateEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        // instantiate an instance of the corresponding template engine
        $engine = new TemplateInstance();
        // and call the corresponding rendering method
        return $engine->render($template, $data);
    }
}

```

Depois modifique a configuração do componente de view:

```php
<?php

use App\Engine\TemplateEngine;

return [
    // Change the engine parameter to your custom template engine class
    'engine' => TemplateEngine::class,
    'mode' => Mode::TASK,
    'config' => [
        'view_path' => BASE_PATH.'/storage/view/',
        'cache_path' => BASE_PATH.'/runtime/view/',
    ],
];
```

## Uso

A seguir usamos `BladeEngine` como exemplo. Primeiro, crie o arquivo de view `index.blade.php` no diretório correspondente.

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hyperf</title>
</head>
<body>
Hello, {{ $name }}. You are using blade template now.
</body>
</html>
```

Obtenha a instância de `Hyperf\View\Render` no controller, depois chame o método `render` e passe o endereço do arquivo de view `index` e os `dados de renderização`. O endereço do arquivo ignora o sufixo do arquivo de view.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\View\RenderInterface;

#[AutoController]
class ViewController
{
    public function index(RenderInterface $render)
    {
        return $render->render('index', ['name' =>'Hyperf']);
    }
}

```

Acesse a URL correspondente para obter a página de view, conforme mostrado abaixo:

```
Hello, Hyperf. You are using blade template now.
```
