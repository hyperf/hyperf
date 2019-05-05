# 请求对象

`请求对象(Request)` 是完全基于 [PSR-7](https://www.php-fig.org/psr/psr-7/) 标准实现的，由 [hyperf/http-message](https://github.com/hyperf-cloud/http-message) 组件提供实现支持。

> 注意 [PSR-7](https://www.php-fig.org/psr/psr-7/) 标准为 `请求(Request)` 进行了 `immutable 机制` 的设计，所有以 `with` 开头的方法的返回值都是一个新对象，不会修改原对象的值

## 安装

该组件完全独立，适用于任何一个框架项目。

```bash
composer require hyperf/http-message
```

> 如用于其它框架项目则仅支持 PSR-7 提供的 API，具体可直接查阅 PSR-7 的相关规范，该文档所描述的使用方式仅限于使用 Hyperf 时的用法。

## 获得请求对象

可以通过容器注入 `Hyperf\HttpServer\Contract\RequestInterface` 获得 对应的 `Hyperf\HttpServer\Request`，实际注入的对象为一个代理对象，代理的对象为每个请求的 `PSR-7 请求对象(Request)`，也就意味着仅可在 `onRequest` 声明周期内可获得此对象，下面是一个获取示例：

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * @AutoController()
 */
class IndexController
{
    public function info(RequestInterface $request)
    {
        // ...
    }
}
```

### 依赖注入与参数

如果希望通过控制器方法参数获取路由参数，可以在依赖项之后列出对应的参数，框架会自动将对应的参数注入到方法参数内，比如您的路由是这样定义的：

```php
// 注解方式
/**
 * @GetMapping(path="/user/{id:\d+}")
 */
 
// 配置方式
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'HEAD'], '/user/{id:\d+}', [\App\Controller\IndexController::class, 'user']);
```

则可以通过在方法参数上声明 `$id` 参数获得 `Query` 参数 `id`，如下所示：

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * @AutoController()
 */
class IndexController
{
    public function info(RequestInterface $request, int $id)
    {
        // ...
    }
}
```

### 请求路径 & 方法

`Hyperf\HttpServer\Contract\RequestInterface` 除了使用 [PSR-7](https://www.php-fig.org/psr/psr-7/) 标准定义的 `APIs` 之外，还提供了多种方法来检查请求，下面我们提供一些方法的示例：

#### 获取请求路径

`path()` 方法返回请求的路径信息。也就是说，如果传入的请求的目标地址是 `http://domain.com/foo/bar?baz=1`，那么 `path()` 将会返回 `foo/bar`：

```php
$uri = $request->path();
```

`is(...$patterns)` 方法可以验证传入的请求路径和指定规则是否匹配。使用这个方法的时，你也可以传递一个 `*` 字符作为通配符：

```php
if ($request->is('user/*')) {
    // ...
}
```

#### 获取请求的 URL

你可以使用 `url()` 或 `fullUrl()` 方法去获取传入请求的完整 `URL`。`url()` 方法返回不带有 `Query 参数` 的 `URL`，而 `fullUrl()` 方法的返回值包含 `Query 参数` ：

```php
// 没有查询参数
$url = $request->url();

// 带上查询参数
$url = $request->fullUrl();
```

#### 获取请求方法

`getMethod()` 方法将返回 `HTTP` 的请求方法。你也可以使用 `isMethod(string $method)` 方法去验证 `HTTP` 的请求方法与指定规则是否匹配：

```php
$method = $request->getMethod();

if ($request->isMethod('post')) {
    //
}
```

### PSR-7 请求及方法

[hyperf/http-message](https://github.com/hyperf-cloud/http-message) 组件本身是一个实现了 [PSR-7](https://www.php-fig.org/psr/psr-7/) 标准的组件，相关方法都可以通过注入的 `请求对象(Request)` 来调用。   
如果注入时声明为 [PSR-7](https://www.php-fig.org/psr/psr-7/) 标准的 `Psr\Http\Message\ServerRequestInterface` 接口，则框架会自动转换为等同于 `Hyperf\HttpServer\Contract\RequestInterface` 的 `Hyperf\HttpServer\Request` 对象。   

> 建议使用 `Hyperf\HttpServer\Contract\RequestInterface` 来注入，这样可获得 IDE 对专属方法的自动完成提醒支持。

## 输入预处理 & 规范化

## 获取输入

### 获取所有输入

您可以使用 `all()` 方法以 `数组` 形式获取到所有输入数据:

```php
$all = $request->all();
```

### 获取指定输入值

通过 `input(string $key, $default = null)` 和 `inputs(array $keys, $default = null): array` 获取 `一个` 或 `多个` 任意形式的输入值：

```php
// 存在则返回，不存在则返回 null
$name = $request->input('name');
// 存在则返回，不存在则返回默认值 Hyperf
$name = $request->input('name', 'Hyperf');
```

如果传输表单数据中包含「数组」形式的数据，那么可以使用「点」语法来获取数组：

```php
$name = $request->input('products.0.name');

$names = $request->input('products.*.name');
```
### 从查询字符串获取输入

使用 `input`, `inputs` 方法可以从整个请求中获取输入数据（包括 `Query 参数`），而 `query(?string $key = null, $default = null)` 方法可以只从查询字符串中获取输入数据：

```php
// 存在则返回，不存在则返回 null
$name = $request->query('name');
// 存在则返回，不存在则返回默认值 Hyperf
$name = $request->query('name', 'Hyperf');
// 不传递参数则以关联数组的形式返回所有 Query 参数
$name = $request->query();
```

### 获取 `JSON` 输入信息

如果请求的 `Body` 数据格式是 `JSON`，则只要 `请求对象(Request)` 的 `Content-Type` `Header值` 正确设置为 `application/json`，就可以通过  `input(string $key, $default = null)` 方法访问 `JSON` 数据，你甚至可以使用 「点」语法来读取 `JSON` 数组：

```php
// 存在则返回，不存在则返回 null
$name = $request->input('user.name');
// 存在则返回，不存在则返回默认值 Hyperf
$name = $request->input('user.name', 'Hyperf');
// 不传递参数则以数组形式返回所有 Json 数据
$name = $request->input();
```

### 确定是否存在输入值

要判断请求是否存在某个值，可以使用 `has($keys)` 方法。如果请求中存在该值则返回 `true`，不存在则返回 `false`，`$keys` 可以传递一个字符串，或传递一个数组包含多个字符串，只有全部存在才会返回 `true`：

```php
// 仅判断单个值
if ($request->has('name')) {
    //
}
// 同时判断多个值
if ($request->has(['name', 'email'])) {
    //
}
```

## Cookies

### 从请求中获取 Cookie

## 文件

### 获取上传文件

### 验证成功上传

### 文件路径 & 扩展名

### 存储上传文件