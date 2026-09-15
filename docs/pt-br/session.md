# Gerenciamento de Session

HTTP é um protocolo sem estado, o que significa que o servidor não retém nenhum estado durante as transações com os clientes. No entanto, ao desenvolver aplicações web, muitas vezes é necessário compartilhar informações entre múltiplas requisições, o que geralmente é feito através do armazenamento de session. Você pode implementar a funcionalidade de session com o [hyperf/session](https://github.com/hyperf/session). O componente de session atualmente implementa apenas dois drivers de armazenamento, `file` e `Redis`. O padrão é o driver `file`. Em um ambiente de produção, recomendamos fortemente que você use `Redis`, pois ele tem um desempenho muito melhor comparado à alternativa `file` e também é mais adequado para arquiteturas de cluster.

# Instalação

```bash
composer require hyperf/session
```

# Configuração

A configuração do componente de session é armazenada no arquivo `config/autoload/session.php`. Se o arquivo não existir, você pode usar o comando `php bin/hyperf.php vendor:publish hyperf/session` para publicar o arquivo de configuração do componente de session.

## Configurar o middleware de session

Antes de usar a session, você precisa configurar o middleware `Hyperf\Session\Middleware\SessionMiddleware` como middleware global do HTTP Server, para que o componente possa interceptar a requisição para processamento. Você pode definir middlewares no arquivo de configuração `config/autoload/middlewares.php`. Exemplo de configuração:

```php
<?php

return [
    // Here http corresponds to the default server name. If you need to use session on other servers, you need to configure the corresponding global middleware
    'http' => [
        \Hyperf\Session\Middleware\SessionMiddleware::class,
    ],
];
```

## Configurar o driver de armazenamento

Modifique diferentes drivers de armazenamento de session alterando a configuração `handler` no arquivo de configuração, e os itens de configuração específicos do handler correspondente são determinados pelos diferentes itens de configuração em `options`.

### Usar o driver de armazenamento em arquivo

> O driver de armazenamento em arquivo é o driver de armazenamento padrão, mas é recomendado usar o driver Redis em um ambiente de produção

Quando o valor de `handler` é `Hyperf\Session\Handler\FileHandler`, isso indica que o driver de armazenamento `file` está sendo usado, e todos os arquivos de dados de session serão gerados e armazenados na pasta correspondente ao valor de configuração `options.path`. A pasta de configuração padrão é a pasta `runtime/session` no diretório raiz.

### Usar o driver Redis

Antes de usar o driver de armazenamento `Redis`, você precisa instalar o componente [hyperf/redis](https://github.com/hyperf/redis). Para usar esse driver de armazenamento, defina o valor de `handler` como `Hyperf\Session\Handler\RedisHandler`. Você pode ajustar a conexão `Redis` usada pelo driver configurando o valor de configuração `options.connection`. As conexões são definidas em `config/autoload/redis.php` do componente [hyperf/redis](https://github.com/hyperf/redis).

# Uso

## Obter o objeto session

O objeto session pode ser acessado injetando `Hyperf\Contract\SessionInterface`:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\SessionInterface;

class IndexController
{
    #[Inject]
    private SessionInterface $session;

    public function index()
    {
        // Use directly via $this->session
    }
}
```

## Armazenar dados

Quando você quiser armazenar dados na session, pode fazer isso chamando o método `set(string $name, $value): void`:

```php
<?php

$this->session->set('foo','bar');
```

## Recuperar dados

Quando você quiser obter dados da session, pode fazer isso chamando o método `get(string $name, $default = null)`:

```php
<?php

$this->session->get('foo', $default = null);
```

### Obter todos os dados

Você pode obter todos os dados armazenados na session de uma vez chamando o método `all(): array`:

```php
<?php

$data = $this->session->all();
```

## Determinar se existe um valor na session

Para determinar se um valor existe na session, você pode usar o método `has(string $name): bool`. Se o valor existir e não for nulo, o método `has` retornará `true`:

```php
<?php

if ($this->session->has('foo')) {
    //
}
```

## Obter e excluir um dado

Chamando o método `remove(string $name)`, você pode obter e excluir um dado da session usando apenas um método:

```php
<?php

$data = $this->session->remove('foo');
```

## Excluir um ou mais dados

Chamando o método `forget(string|array $name): void`, um ou mais dados podem ser excluídos da session usando apenas um método. Quando uma string é passada, significa que apenas um dado é excluído. Quando um array de chaves é passado, significa que múltiplos dados serão excluídos:

```php
<?php

$this->session->forget('foo');
$this->session->forget(['foo','bar']);
```

## Limpar os dados da session atual

Você pode limpar todos os dados da session atual chamando o método `clear(): void`:

```php
<?php

$this->session->clear();
```

## Obter o ID da session atual

Quando você quiser obter o ID da session atual para tratar alguma lógica por conta própria, você pode obter o ID da session atual chamando o método `getId(): string`:

```php
<?php

$sessionId = $this->session->getId();
```
