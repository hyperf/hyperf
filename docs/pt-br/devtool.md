# Dev Tool

## Instalação

```
composer require hyperf/devtool
```

# Comandos suportados

```bash
php bin/hyperf.php
```

Todos os comandos suportados pelo Command podem ser listados executando o comando acima. A série de comandos sob `gen` e `vendor:publish` fornece principalmente suporte ao componente `devtool`.

```
 gen
  gen:amqp-consumer  Create a new amqp consumer class
  gen:amqp-producer  Create a new amqp producer class
  gen:aspect         Create a new aspect class
  gen:command        Create a new command class
  gen:controller     Create a new controller class
  gen:job            Create a new job class
  gen:listener       Create a new listener class
  gen:middleware     Create a new middleware class
  gen:process        Create a new process class
 vendor
  vendor:publish     Publish any publishable configs from vendor packages.
```

## Quick Open

Foi adicionada uma função bem simples para abrir rapidamente os arquivos criados com o comando `gen`, com suporte a `sublime`, `textmate`, `cursor`, `emacs`, `macvim`, `phpstorm`, `idea`, `vscode`, `vscode-insiders`, `vscode-remote`, `vscode-insiders-remote`, `atom`, `nova`, `netbeans`, `xdebug`.

Você também precisa adicionar este bloco de configuração em `config/autoload/devtool.php`:

```php
return [
    /**
     * IDEs suportadas: "sublime", "textmate", "cursor", "emacs", "macvim", "phpstorm", "idea",
     *        "vscode", "vscode-insiders", "vscode-remote", "vscode-insiders-remote",
     *        "atom", "nova", "netbeans", "xdebug"
     */
    'ide' => env('DEVTOOL_IDE', ''),
    //...
];
```
