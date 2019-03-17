# 概要

为了让您充分理解 Hyperf 的工作原理，这份文档为您详细描述了相关的技术细节，不过这并不是一份入门教程或者是参考文档（我们当然也为您准备了这些）。

## 编写路由

Hyperf使用`nikic/fast-route`提供路由功能，您可以很方便的在`config/routes.php`中编写您的路由。不仅如此，框架还提供了注解路由功能，详情见[路由]()

~~~php
// config/routes.php

use Hyperf\HttpServer\Router\Router;

Router::get('/', 'App\Controllers\IndexController@index');
~~~

## 处理请求

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

## 自动注入

当您在使用`make`和`get`的时候，框架会自动帮你注入container的单例对象。比如，我们实现一个UserService。
框架提供构造函数注入和注解注入两种方式。

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

让我们改写上面的IndexController，来执行一下UserService的方法。

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

Hyperf基于PHPParser实现了AOP切面编程，为了支持注解，会对原型类进行重写，所以请使用以下方式创建对象，而不是简单的new。
当然这只是建议，并不是要求。

~~~php
<?php

$class = $this->container->get(UserService::class);

$user = $class->get(1);

var_dump($user->toArray);

$form = make(UserForm::class,['data' => $data]);
$form->save();
~~~

