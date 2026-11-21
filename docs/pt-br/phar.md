# Empacotador Phar

## Instalação

```bash
composer require hyperf/phar
```

## Uso

- Empacotado por padrão

```shell
php bin/hyperf.php phar:build
```

- Definir o nome do pacote

```shell
php bin/hyperf.php phar:build --name=your_project.phar
```

- Definir a versão do pacote

```shell
php bin/hyperf.php phar:build --phar-version=1.0.1
```

- Definir o arquivo de inicialização

```shell
php bin/hyperf.php phar:build --bin=bin/hyperf.php
```

- Definir o diretório de empacotamento

```shell
php bin/hyperf.php phar:build --path=BASE_PATH
```

- Mapear arquivos externos

> Requer hyperf/phar versão >= v2.1.7

O comando abaixo permite que o pacote `phar` leia o arquivo `.env` no mesmo diretório, para que o `phar` possa ser distribuído para diferentes ambientes.

```shell
php bin/hyperf.php phar:build -M .env
```

## Executar

```shell
php your_project.phar start
```

## Precauções

Após empacotar, ele roda na forma de um pacote `phar`, o que é diferente de rodar no modo de código-fonte. O diretório `runtime` dentro do pacote `phar` não é gravável.
Por isso, precisamos sobrescrever alguns locais de diretórios graváveis.

> Modifique conforme apropriado de acordo com a situação real

- pid_file

Modifique a configuração `server.php`.

```php
<?php

return [
     'settings' => [
         'pid_file' => '/tmp/runtime/hyperf.pid',
     ],
];
```

- logger

Modifique a configuração `logger.php`

```php
<?php
return [
     'default' => [
         'handler' => [
             'class' => Monolog\Handler\StreamHandler::class,
             'constructor' => [
                 'stream' => '/tmp/runtime/logs/hyperf.log',
                 'level' => Monolog\Logger::INFO,
             ],
         ],
     ],
];
```

- scan_cacheable

O empacotador Phar definirá automaticamente `scan_cacheable` como `true` na configuração `config.php`.

Claro, também é possível modificar essa configuração ativamente para `true`.
