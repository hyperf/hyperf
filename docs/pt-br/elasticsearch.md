# Elasticsearch

[hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) é uma fábrica para criação de objetos de client para o [elasticsearch-php](https://github.com/elastic/elasticsearch-php). O handler padrão é o client `Guzzle Ring`. Em [hyperf/guzzle](https://github.com/hyperf/guzzle) implementamos o `Handler` na versão para corrotinas; assim, podemos criar diretamente um novo `Builder` via `Hyperf\Elasticsearch\ClientBuilderFactory`.

## Instalação

```bash
composer require hyperf/elasticsearch
```

## Uso

### Criar um Client

```php
<?php

use Hyperf\Elasticsearch\ClientBuilderFactory;

// Se criado em um ambiente de corrotina, usará o handler de corrotina; se criado em um ambiente sem corrotina, não haverá alteração.
$builder = $this->container->get(ClientBuilderFactory::class)->create();

$client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

$info = $client->info();
```
