# Paginação de query

Ao consultar dados usando [hyperf/database](https://github.com/hyperf/database), é muito conveniente usar o componente [hyperf/paginator](https://github.com/hyperf/paginator) para paginar facilmente os resultados de query.

# Instruções

Ao consultar dados através do [Query Builder](pt-br/db/querybuilder.md) ou [Model](pt-br/db/model.md), a paginação pode ser tratada através do método `paginate`, que usa automaticamente a página que está sendo visualizada para definir o limit e o offset. Por padrão, o número da página atual é detectado pelo valor do parâmetro `page` presente na requisição HTTP atual:

> Como o Hyperf atualmente não suporta views, o componente de paginação ainda não suporta a renderização de views, e os resultados de paginação retornados diretamente serão exibidos no formato application/json por padrão.

## Paginação com query builder

```php
<?php
// Show all users in the app, 10 pieces of data per page
return Db::table('users')->paginate(10);
```

## Paginação com Model

Você pode fazer a paginação chamando o método `paginate` diretamente a partir de um método estático:

```php
<?php
// Show all users in the app, 10 pieces of data per page
return User::paginate(10);
```

Você também pode definir as condições de query ou outras configurações de query:

```php
<?php 
// Show all users in the app, 10 pieces of data per page
return User::where('gender', 1)->paginate(10);
```

## Métodos de instância do Paginator

Aqui é descrito apenas o uso do paginator em queries de banco de dados. Para mais detalhes sobre o paginator, leia o capítulo [Paginação](pt-br/paginator.md).
