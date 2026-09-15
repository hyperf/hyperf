# Phar packager

## Instalação

```bash
composer require hyperf/phar
```

## Uso

- Empacotar com as configurações padrão

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

O comando a seguir permite que o pacote `phar` leia o arquivo `.env` no mesmo diretório, de forma que o `phar` possa ser distribuído para diversos ambientes

```shell
php bin/hyperf.php phar:build -M .env
```

## Executar

```shell
php your_project.phar start
```

## Precauções

Após o empacotamento, ele é executado no formato de pacote `phar`, o que é diferente de executar em modo código-fonte. O diretório `runtime` dentro do pacote `phar` não é gravável.
Então precisamos sobrescrever alguns locais de diretório graváveis.

> Modifique conforme apropriado de acordo com a situação real

- pid_file

Modifique a configuração de `server.php`.

```php
<?php

return [
     'settings' => [
         'pid_file' => '/tmp/runtime/hyperf.pid',
     ],
];
```

- logger

Modifique a configuração de `logger.php`

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

O Phar packager definirá automaticamente `scan_cacheable` como `true` na configuração `config.php`.

É claro, também é possível modificar ativamente essa configuração para `true`.
