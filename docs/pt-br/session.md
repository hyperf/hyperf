# Gerenciamento de sessão

HTTP é um protocolo stateless, o que significa que o servidor não retém nenhum estado durante transações com clientes. Porém, ao desenvolver aplicações web, frequentemente é necessário compartilhar informações entre múltiplas requisições, o que normalmente é feito via armazenamento de sessão.

Você pode implementar funcionalidade de sessão com [hyperf/session](https://github.com/hyperf/session). O componente de sessão atualmente implementa apenas dois drivers de armazenamento: `file` e `Redis`. O padrão é o driver `file`. Em ambiente de produção, recomendamos fortemente usar `Redis`, pois tem desempenho muito melhor do que `file` e também é mais adequado para arquiteturas em cluster.

# Instalação

```bash
composer require hyperf/session
```

# Configuração

A configuração do componente de sessão fica no arquivo `config/autoload/session.php`. Se o arquivo não existir, você pode usar o comando `php bin/hyperf.php vendor:publish hyperf/session` para publicar o arquivo de configuração do componente.

## Configurar o middleware de sessão

Antes de usar sessão, você precisa configurar o middleware `Hyperf\Session\Middleware\SessionMiddleware` como middleware global do HTTP Server para que o componente possa interceptar e processar as requisições. Você pode definir middlewares no arquivo de configuração `config/autoload/middlewares.php`. Exemplo:

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

Você pode alterar o driver de armazenamento mudando a configuração `handler` no arquivo de configuração. Os itens específicos do handler são definidos por `options`.

### Usar driver de armazenamento em arquivo

> O driver de arquivo é o padrão, mas é recomendado usar o driver Redis em produção.

Quando `handler` é `Hyperf\Session\Handler\FileHandler`, indica que o driver `file` está sendo usado e que os arquivos de dados de sessão serão gerados e armazenados na pasta definida por `options.path`. A pasta padrão fica em `runtime/session` no diretório raiz.

### Usar driver Redis

Antes de usar o driver de armazenamento `Redis`, você precisa instalar o componente [hyperf/redis](https://github.com/hyperf/redis). Para usar este driver, defina `handler` como `Hyperf\Session\Handler\RedisHandler`. Você pode ajustar qual conexão Redis usar configurando `options.connection`. As conexões são definidas em `config/autoload/redis.php` do componente [hyperf/redis](https://github.com/hyperf/redis).

# Uso

## Obter o objeto de sessão

O objeto de sessão pode ser acessado injetando `Hyperf\Contract\SessionInterface`:

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

Quando você quiser armazenar dados na sessão, chame o método `set(string $name, $value): void`:

```php
<?php

$this->session->set('foo','bar');
```

## Recuperar dados

Quando você quiser obter dados da sessão, chame o método `get(string $name, $default = null)`:

```php
<?php

$this->session->get('foo', $default = null);
```

### Obter todos os dados

Você pode obter todos os dados armazenados na sessão de uma vez chamando o método `all(): array`:

```php
<?php

$data = $this->session->all();
```

## Verificar se existe um valor na sessão

Para determinar se um valor existe na sessão, você pode usar o método `has(string $name): bool`. Se o valor existir e não for null, `has` retornará `true`:

```php
<?php

if ($this->session->has('foo')) {
    //
}
```

## Obter e deletar um dado

Ao chamar o método `remove(string $name)`, você pode obter e deletar um dado da sessão em uma única chamada:

```php
<?php

$data = $this->session->remove('foo');
```

## Deletar um ou mais dados

Ao chamar o método `forget(string|array $name): void`, um ou mais dados podem ser removidos da sessão em uma única chamada. Ao passar uma string, apenas um dado é removido. Ao passar um array de strings, vários dados são removidos:

```php
<?php

$this->session->forget('foo');
$this->session->forget(['foo','bar']);
```

## Limpar os dados da sessão atual

Você pode limpar todos os dados da sessão atual chamando o método `clear(): void`:

```php
<?php

$this->session->clear();
```

## Obter o ID da sessão atual

Quando você quiser obter o ID da sessão atual para tratar alguma lógica por conta própria, você pode obtê-lo chamando o método `getId(): string`:

```php
<?php

$sessionId = $this->session->getId();
```
