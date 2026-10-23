# Elasticsearch

O [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) é uma factory para criação de objetos client para o [elasticsearch-php](https://github.com/elastic/elasticsearch-php); o handler padrão é o client `Guzzle Ring`, e no [hyperf/guzzle](https://github.com/hyperf/guzzle) implementamos o `Handler` da versão em coroutine, então podemos criar um novo `Builder` diretamente através do `Hyperf\Elasticsearch\ClientBuilderFactory`.

## Instalação

```bash
composer require hyperf/elasticsearch
```
## Uso

### Criar um Client

```php
<?php

use Hyperf\Elasticsearch\ClientBuilderFactory;

// If created in coroutine environment will use coroutine handler, if created in non-coroutine environment will not change.
$builder = $this->container->get(ClientBuilderFactory::class)->create();

$client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

$info = $client->info();
```
