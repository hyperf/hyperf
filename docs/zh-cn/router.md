# 路由

默认情况下路由由 [nikic/fast-route](https://github.com/nikic/FastRoute) 提供支持，并由 [hyperf/http-server](https://github.com/hyperf/http-server) 组件负责接入到 `Hyperf` 中，`RPC` 路由由对应的 [hyperf/rpc-server](https://github.com/hyperf/rpc-server) 组件负责。

## HTTP 路由

### 通过配置文件定义路由

在 [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 骨架下，默认在 `config/routes.php` 文件内完成所有的路由定义，当然如果您路由众多，您也可以对该文件进行扩展，以适应您的需求，但 `Hyperf` 还支持 `注解路由`，我们更推荐使用 `注解路由`，特别是在路由众多的情况下。   

#### 通过闭包定义路由

构建一个最基本的路由只需一个 URI 和一个 `闭包(Closure)`，我们直接通过代码来演示一下：

```php
<?php
use Hyperf\HttpServer\Router\Router;

Router::get('/hello-hyperf', function () {
    return 'Hello Hyperf.';
});
```

您可以通过 浏览器 或  `cURL` 命令行来请求 `http://host:port/hello-hyperf` 来访问该路由。

#### 定义标准路由

所谓标准路由指的是由 `控制器(Controller)` 和 `操作(Action)` 来处理的路由，如果您使用 `请求处理器(Request Handler)` 模式也是类似的，我们通过代码来演示一下：

```php
<?php
use Hyperf\HttpServer\Router\Router;

// 下面三种方式的任意一种都可以达到同样的效果
Router::get('/hello-hyperf', 'App\Controller\IndexController::hello');
Router::get('/hello-hyperf', 'App\Controller\IndexController@hello');
Router::get('/hello-hyperf', [App\Controller\IndexController::class, 'hello']);
```

该路由定义为将 `/hello-hyperf` 路径绑定到 `App\Controller\IndexController` 下的 `hello` 方法。

#### 可用的路由方法

路由器提供了多种方法帮助您注册任何的 HTTP 请求的路由：

```php
use Hyperf\HttpServer\Router\Router;

// 注册与方法名一致的 HTTP METHOD 的路由
Router::get($uri, $callback);
Router::post($uri, $callback);
Router::put($uri, $callback);
Router::patch($uri, $callback);
Router::delete($uri, $callback);
Router::head($uri, $callback);

// 注册任意 HTTP METHOD 的路由
Router::addRoute($httpMethod, $uri, $callback);
```

有时候您可能需要注册一个可以同时响应多种 HTTP METHOD 请求的路由，可以通过 `addRoute` 方法实现定义：

```php
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST','PUT','DELETE'], $uri, $callback);
```

#### 路由组的定义方式

实际路由为 `group/route`, 即 `/user/index`, `/user/store`, `/user/update`, `/user/delete` 

```php
Router::addGroup('/user/',function (){
    Router::get('index','App\Controller\UserController@index');
    Router::post('store','App\Controller\UserController@store');
    Router::get('update','App\Controller\UserController@update');
    Router::post('delete','App\Controller\UserController@delete');
});

```

### 通过注解定义路由

`Hyperf` 提供了非常便利的 [注解](zh-cn/annotation.md) 路由功能，您可以直接在任意类上通过定义 `#[Controller]` 或 `#[AutoController]` 注解来完成一个路由的定义。

!> 以下出现的注解类都为 `use Hyperf\HttpServer\Annotation\` 命名空间下的类，如 `Hyperf\HttpServer\Annotation\AutoController`

#### 注解参数

`#[Controller]` 和 `#[AutoController]` 都提供了 `prefix` 和 `server` 两个参数。

`prefix` 表示该控制器下的所有方法路由的前缀，默认为控制器类命名空间中 `\Controller\` 之后的部分会以蛇形命名法(SnakeCase)被用作路由的前缀，

如 `App\Controller\Demo\UserController` 则 prefix 默认为 `demo/user`，如类内某一方法的 path 为 `index`，则最终路由为 `/demo/user/index`。

!> 需要注意的是 `prefix` 并非一直有效，当类内的方法的 path 以 `/` 开头时，则表明路径从 `URI` 头部开始定义，也就意味着会忽略 prefix 的值

`server` 表示该路由是定义在哪个 `HTTP Server` 之上的，由于 Hyperf 支持同时启动多个 `HTTP Server`，则在定义路由是可以通过该参数来进行区分这个路由是为了哪个 `Server` 定义的，默认为 `http`。

|                 控制器                  |               注解                |        访问路由        |
|:------------------------------------:|:-------------------------------:|:------------------:|
|   App\Controller\MyDataController    |        @AutoController()        |   /my_data/index   |
|   App\Controller\MydataController    |        @AutoController()        |   /mydata/index    |
|   App\Controller\MyDataController    | @AutoController(prefix="/data") |    /data/index     |
| App\Controller\Demo\MydataController |        @AutoController()        | /demo/mydata/index |
| App\Controller\Demo\MyDataController | @AutoController(prefix="/data") |    /data/index     |



|                 控制器                  |                                        注解                                         |        访问路由         |
|:------------------------------------:|:---------------------------------------------------------------------------------:|:-------------------:|
|   App\Controller\MyDataController    |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        |   /my_data/index    |
| App\Controller\Demo\MyDataController |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        | /demo/my_data/index |
| App\Controller\Demo\MyDataController | @Controller(prefix="/data") + @RequestMapping(path: "index", methods: "get,post") |     /data/index     |
|   App\Controller\MyDataController    |       @Controller() + @RequestMapping(path: "/index", methods: "get,post")        |       /index        |

#### `#[AutoController]` 注解

`#[AutoController]` 为绝大多数简单的访问场景提供路由绑定支持，使用 `#[AutoController]` 时则 `Hyperf` 会自动解析所在类的所有 `public` 方法并提供 `GET` 和 `POST` 两种请求方式。

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class UserController
{
    // Hyperf 会自动为此方法生成一个 /user/index 的路由，允许通过 GET 或 POST 方式请求
    public function index(RequestInterface $request)
    {
        // 从请求中获得 id 参数
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

#### `#[Controller]` 注解

`#[Controller]` 为满足更细致的路由定义需求而存在，使用 `#[Controller]` 注解用于表明当前类为一个 `Controller` 类，同时需配合 `#[RequestMapping]` 注解来对请求方法和请求路径进行更详细的定义。   
我们也提供了多种快速便捷的 `Mapping` 注解，如 `#[GetMapping]`、`#[PostMapping]`、`#[PutMapping]`、`#[PatchMapping]`、`#[DeleteMapping]` 5 种便捷的注解用于表明允许不同的请求方法。

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class UserController
{
    // Hyperf 会自动为此方法生成一个 /user/index 的路由，允许通过 GET 或 POST 方式请求
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // 从请求中获得 id 参数
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### 路由参数

> 本框架定义的路由参数必须和控制器参数键名、类型保持一致，否则控制器无法接收到相关参数

```php
Router::get('/user/{id}', 'App\Controller\UserController::info');
```

```php
public function info(int $id)
{
    $user = User::find($id);
    return $user->toArray();
}
```

通过 `route` 方法获取

```php
public function index(RequestInterface $request)
{
        // 存在则返回，不存在则返回默认值 null
        $id = $request->route('id');
        // 存在则返回，不存在则返回默认值 0
        $id = $request->route('id', 0);
}
```

#### 必填参数

我们可以对 `$uri` 进行一些参数定义，通过 `{}` 来声明参数，如 `/user/{id}` 则声明了 `id` 值为一个必填参数。

#### 可选参数

有时候您可能会希望这个参数是可选的，您可以通过 `[]` 来声明中括号内的参数为一个可选参数，如 `/user/[{id}]`。

#### 校验参数

您也可以使用正则表达式对参数进行校验，以下是一些例子
```php
use Hyperf\HttpServer\Router\Router;

// 可以匹配 /user/42, 但不能匹配 /user/xyz
Router::addRoute('GET', '/user/{id:\d+}', 'handler');

// 可以匹配 /user/foobar, 但不能匹配 /user/foo/bar
Router::addRoute('GET', '/user/{name}', 'handler');

// 也可以匹配 /user/foo/bar as well
Router::addRoute('GET', '/user/{name:.+}', 'handler');

// 这个路由
Router::addRoute('GET', '/user/{id:\d+}[/{name}]', 'handler');
// 等同于以下的两个路由
Router::addRoute('GET', '/user/{id:\d+}', 'handler');
Router::addRoute('GET', '/user/{id:\d+}/{name}', 'handler');

// 多个可选的嵌套也是允许的
Router::addRoute('GET', '/user[/{id:\d+}[/{name}]]', 'handler');

// 这是一条无效的路由, 因为可选部分只能出现在最后
Router::addRoute('GET', '/user[/{id:\d+}]/{name}', 'handler');
```

#### 获取路由信息

如果安装了 devtool 组件，可使用 `php bin/hyperf.php describe:routes` 命令获取路由列表信息，
并且提供 path 可选项，方便获取单个路由信息，对应的命令 `php bin/hyperf.php describe:routes --path=/foo/bar`。

## HTTP 异常

在路由匹配不到路由时，如 `路由找不到(404)`、`请求方法不允许(405)` 等 HTTP 异常，Hyperf 会统一抛出一个 `Hyperf\HttpMessage\Exception\HttpException` 异常类的子类，您需要通过 ExceptionHandler 机制来管理这些异常并做对应的响应处理，默认情况下可以直接使用组件提供的 `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` 来进行异常捕获处理，注意这个异常处理器需要您自行配置到 `config/autoload/exceptions.php` 配置文件中去，并保障多个异常处理器之间的顺序链路是正确的。   
当您需要对 `路由找不到(404)`、`请求方法不允许(405)` 等 HTTP 异常情况的响应进行自定义处理时，您可直接根据 `HttpExceptionHandler` 的代码实现您自己的异常处理器，并配置您自己的异常处理器。关于异常处理器的逻辑和使用说明，可具体查阅 [异常处理](zh-cn/exception-handler.md)
