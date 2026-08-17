# Paginação de consultas

Ao usar [hyperf/database](https://github.com/hyperf/database) para consultar dados, é muito conveniente usar o componente [hyperf/paginator](https://github.com/hyperf/paginator) para paginar facilmente resultados de consultas.

# Instruções

Ao consultar dados via [Query Builder](pt-br/db/querybuilder.md) ou [Model](pt-br/db/model.md), a paginação pode ser feita pelo método `paginate`, que usa automaticamente a página sendo visualizada para definir limit e offset. Por padrão, o número da página atual é detectado pelo valor do parâmetro `page` carregado pela requisição HTTP atual:

> Como o Hyperf não suporta views atualmente, o componente de paginação ainda não suporta renderização de views, e o resultado da paginação retornado diretamente será emitido em formato application/json por padrão.

## Paginação no query builder

```php
<?php
// Exibe todos os usuários na aplicação, com 10 itens por página
return Db::table('users')->paginate(10);
```

## Paginação no Model

Você pode paginar chamando o método `paginate` diretamente como um método estático:

```php
<?php
// Exibe todos os usuários na aplicação, com 10 itens por página
return User::paginate(10);
```

Você também pode definir condições de consulta ou outras configurações:

```php
<?php 
// Exibe todos os usuários na aplicação, com 10 itens por página
return User::where('gender', 1)->paginate(10);
```

## Métodos da instância Paginator

Aqui é descrito apenas o uso do paginator em consultas ao database. Para mais detalhes sobre o paginator, leia o capítulo [Pagination](pt-br/paginator.md).

