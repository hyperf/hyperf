# Overview

To give you a full understanding of how Hyperf works, this document gives you technical details, but it's not an introductory tutorial or a reference document (we've also prepared these for you, of course).

## Write Routes

Hyperf uses `nikic/fast-route` to provide routing capabilities, and you can easily write your route in `config/routes.php`. In addition, the framework also provides routing annotation [routing]().

~~~php
// config/routes.php

use Hyperf\HttpServer\Router\Router;

Router::get('/', 'App\Controllers\IndexController@index');
~~~

## Handle Request

~~~php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;

class IndexController
{
    public function index(RequestInterface $request)
    {
        $id = $request->input('id', 1);

        return (string)$id;
    }
}
~~~

## Auto Inject

When you use make and get, the framework automatically injects container's singleton objects. For example, we implement a UserService.
The framework provides constructor injection and annotation injection.

~~~php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Dao\BookDao;
use App\Service\Dao\UserDao;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;

class UserService
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var UserDao
     */
    protected $dao;

    /**
     * @Inject
     * @var BookDao
     */
    protected $book;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dao = $container->get(UserDao::class);
    }

    public function get(int $id)
    {
        return $this->dao->first($id);
    }

    public function book(int $id)
    {
        return $this->book->first($id);
    }
}
~~~

Let's rewrite the IndexController above to execute the UserService method.

~~~php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Service\UserService;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;

class IndexController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function index(RequestInterface $request)
    {
        $id = $request->input('id', 1);

        $service = $this->container->get(UserService::class);

        return $service->get($id)->toArray();
    }
}
~~~


## 创建对象

Hyperf implements AOP aspect programming based on PHPParser. To support annotations, origin classes are rewritten, so create objects in the following way instead of simply new. 
Of course, this is only a suggestion, not a requirement.

~~~php
<?php

$class = $this->container->get(UserService::class);

$user = $class->get(1);

var_dump($user->toArray);

$form = make(UserForm::class,['data' => $data]);
$form->save();
~~~

