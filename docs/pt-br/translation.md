# I18n

O suporte à internacionalização do Hyperf é bem amigável, permitindo que seu projeto suporte vários idiomas.

# Instalação

```bash
composer require hyperf/translation
```

> This component is an independent component with no framework-related dependencies, and can be independently reused for other projects or frameworks.

# Arquivo de idioma

Por padrão, os arquivos de idioma do Hyperf ficam em `storage/languages`. Você também pode alterar a pasta de arquivos de idioma em `config/autoload/translation.php`. Cada idioma corresponde a uma subpasta; por exemplo, `en ` se refere ao arquivo de idioma em inglês, e `zh_CN` se refere ao arquivo de idioma em chinês simplificado. Você pode criar uma nova pasta de idioma e o arquivo de idioma dentro dela conforme sua necessidade. Um exemplo é:

```
/storage
    /languages
        /en
            messages.php
        /zh_CN
            messages.php
```

Todos os arquivos de idioma retornam um array cujas chaves são strings:

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome to our application',
];
```

## Configurar locale

### Configurar o locale padrão

As configurações relevantes do componente de internacionalização ficam no arquivo de configuração `config/autoload/translation.php`. Você pode modificá-las conforme sua necessidade.

```php
<?php
// config/autoload/translation.php

return [
    // default language
    'locale' => 'zh_CN',
    // Fallback language, when the language text of the default language is not provided, the corresponding language text of the fallback language will be used
    'fallback_locale' => 'en',
    // Folder where language files are stored
    'path' => BASE_PATH . '/storage/languages',
];
```

### Configurar um locale temporário

```php
<?php

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\TranslatorInterface;

class FooController
{
    #[Inject]
    private TranslatorInterface $translator;
    
    public function index()
    {
        // Only valid for the current request or coroutine lifetime
        $this->translator->setLocale('zh_CN');
    }
}
```

# Traduzir string

## Traduzir via TranslatorInterface

A tradução de strings pode ser feita diretamente injetando `Hyperf\Contact\TranslatorInterface` e chamando o método `trans` da instância:

```php
<?php

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\TranslatorInterface;

class FooController
{
    #[Inject]
    private TranslatorInterface $translator;
    
    public function index()
    {
        return $this->translator->trans('messages.welcome', [], 'zh_CN');
    }
}
```

## Traduzir via função global

Você também pode traduzir strings por meio das funções globais `__()` ou `trans()`.
O primeiro parâmetro da função pode estar no formato `key` (referenciando a chave que usa a string de tradução como valor) ou `file.key`.

```php
echo __('messages.welcome');
echo trans('messages.welcome');
```

# Definir placeholders nas strings de tradução

Você também pode definir placeholders nas strings de idioma; todos os placeholders são prefixados com `:`. Por exemplo, usando o nome de usuário como placeholder:

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome :name',
];
```

Substitua o placeholder usando o segundo parâmetro da função:

```php
echo __('messages.welcome', ['name' => 'Hyperf']);
```

Se o placeholder estiver em letras maiúsculas, ou com a primeira letra maiúscula, a string traduzida também ficará na forma correspondente:

```php
'welcome' => 'Welcome, :NAME', // Welcome, HYPERF
'goodbye' => 'Goodbye, :Name', // Goodbye, Hyperf
```

# Lidar com números complexos

As regras de plural são diferentes entre idiomas, o que pode não ser uma grande preocupação em chinês, mas ao traduzir outros idiomas precisamos lidar com formas plurais das palavras. Podemos usar o caractere `"pipe"` para distinguir formas singular e plural das strings:

```php
'apples' => 'There is one apple|There are many apples',
```

Você também pode especificar um intervalo de números para criar regras de plural mais complexas:

```php
'apples' => '{0} There are none|[1,19] There are some|[20,*] There are many',
```

Usando o caractere `"pipe"`, depois que as regras de plural forem definidas, a função global `trans_choice` pode ser usada para obter uma string literal para um determinado `"amount"`. No exemplo a seguir, como o número é maior que `1`, a forma plural da string de tradução é retornada:

```php
echo trans_choice('messages.apples', 10);
```

Claro, além da função global `trans_choice()`, você também pode usar o método `transChoice` de `Hyperf\Contract\TranslatorInterface`.
