# View

A renderização de views é implementada pelo componente [hyperf/view](https://github.com/hyperf/view). O componente suporta cinco motores de template diferentes: `Blade`, `Smarty`, `Twig`, `Plates` e `ThinkTemplate`.

## Instalação

```bash
composer require hyperf/view
```

## Configuração

O arquivo de configuração do componente view fica em `config/autoload/view.php`. Se o arquivo de configuração não existir, você pode executar o comando a seguir para gerar o arquivo:

```bash
php bin/hyperf.php vendor:publish hyperf/view
```

As seguintes opções de configuração estão disponíveis:

| Configuração         | Tipo     | Valor padrão                              | Observações                       |
| :-----------------: | :------: | :--------------------------------------: | :------------------------------: |
| engine              | string   | Hyperf\View\Engine\BladeEngine::class    | Motor de renderização de view     |
| mode                | string   | Mode::TASK                               | Modo de renderização de view      |
| config.view_path    | string   | None                                     | Endereço padrão do arquivo de view |
| config.cache_path   | string   | None                                     | Endereço de cache do arquivo de view |

Formato de exemplo do arquivo de configuração:

```php
<?php
declare(strict_types=1);

use Hyperf\View\Mode;
use Hyperf\View\Engine\BladeEngine;

return [
    // O motor de renderização usado
    'engine' => BladeEngine::class,
    // Se não preencher, o padrão é o modo Task; recomenda-se usar o modo Task
    'mode' => Mode::TASK,
    'config' => [
        // Se a pasta abaixo não existir, crie-a manualmente
        'view_path' => BASE_PATH.'/storage/view/',
        'cache_path' => BASE_PATH.'/runtime/view/',
    ],
];
```

### Modo Task

Ao usar o modo `Task`, o componente [hyperf/task](https://github.com/hyperf/task) precisa estar instalado e o `task_enable_coroutine` deve estar configurado como `false`; caso contrário, haverá um problema de consistência de dados da coroutine. Consulte a documentação do componente [task](pt-br/task.md).

Além disso, no modo `Task`, o trabalho de renderização de view é feito por um processo `Task Worker`, enquanto o processamento de requisições no controller é concluído por um processo `Worker`. Isso significa que não é possível acessar diretamente objetos de dados dependentes de contexto, como `Request` e `Session`, a partir da view. Se você precisar usar dados dependentes de contexto nas views, certifique-se de passar os dados a partir do controller via método `render`.

### Modo Sync

Se você usar o modo `Sync` para renderizar a view, certifique-se de que o motor relevante é seguro para coroutine; caso contrário, haverá problemas de consistência de dados. Recomenda-se usar o modo `Task`, que é mais seguro para dados.

### Configurar recursos estáticos

Se você quiser que o `Swoole` gerencie recursos estáticos, adicione a configuração a seguir no arquivo `config/autoload/server.php`.

```
return [
    'settings' => [
        ...
        // recursos estáticos
        'document_root' => BASE_PATH.'/public',
        'enable_static_handler' => true,
    ],
];

```

## Motor de renderização de view

Os motores de renderização oficialmente suportados atualmente são `Blade`, `Smarty`, `Twig`, `Plates` e `ThinkTemplate`. O motor de template não será instalado automaticamente quando o [hyperf/view](https://github.com/hyperf/view) for instalado. Você precisa instalar o motor de template correspondente além do pacote view.

### Instalar Blade Engine

```bash
composer require hyperf/view-engine
```

Para mais detalhes, consulte a [documentação do view engine](pt-br/view-engine.md).

Ou use:

> duncan3dc/blade usa a biblioteca Support do Laravel, então algumas funções serão incompatíveis; por isso, não é recomendado por enquanto

```bash
composer require duncan3dc/blade
```

### Instalar Smarty Engine

```bash
composer require smarty/smarty
```

### Instalar Twig Engine

```bash
composer require twig/twig
```

### Instalar Plates Engine

```bash
composer require league/plates
```

### Instalar ThinkTemplate Engine

```bash
composer require sy-records/think-template
```

### Acessar outros templates

Suponha que queremos conectar um motor de templates virtual chamado `TemplateEngine`. Então precisamos criar a classe `TemplateEngine` em qualquer lugar e implementar a interface `Hyperf\View\Engine\EngineInterface`.

```php
<?php

declare(strict_types=1);

namespace App\Engine;

use Hyperf\View\Engine\EngineInterface;

class TemplateEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        // instancia uma instância do motor de template correspondente
        $engine = new TemplateInstance();
        // e chama o método de renderização correspondente
        return $engine->render($template, $data);
    }
}

```

Em seguida, modifique a configuração do componente view:

```php
<?php

use App\Engine\TemplateEngine;

return [
    // Altere o parâmetro engine para sua classe de motor de template customizado
    'engine' => TemplateEngine::class,
    'mode' => Mode::TASK,
    'config' => [
        'view_path' => BASE_PATH.'/storage/view/',
        'cache_path' => BASE_PATH.'/runtime/view/',
    ],
];
```

## Uso

O exemplo a seguir usa `BladeEngine`. Primeiro, crie o arquivo de view `index.blade.php` no diretório correspondente.

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

Obtenha a instância de `Hyperf\View\Render` no controller, então chame o método `render` e passe o endereço do arquivo de view `index` e os `rendering data`. O endereço do arquivo ignora o sufixo do arquivo de view.

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

Visite a URL correspondente para obter a página de view como mostrado abaixo:

```
Hello, Hyperf. You are using blade template now.
```

