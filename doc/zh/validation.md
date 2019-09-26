# 验证

Hyperf Validation 组件让您的项目轻松验证请求数据。

# 安装

## 安装组件

```bash
composer require hyperf/validation
```

## 添加ValidationMiddleware中间件

```php
<?php
return [
    // http 对应 config/server.php 内每个 server 的 name 属性对应的值，该配置仅应用在该 Server 中
    'http' => [
        // 数组内配置您的全局中间件，顺序根据该数组的顺序
        \Hyperf\Validation\Middleware\ValidationMiddleware::class
    ],
];
```

## 发布验证器语言文件

```bash
php bin/hyperf.php vendor:publish hyperf/validation
```
上面的命令将会发布验证器的语言文件validation.php到对应的语言文件目录，`en` 指英文语言文件，`zh-CN` 指中文简体的语言文件，你可以按照实际需要对`validation.php` 文件内容进行修改和自定义。

```
/storage
    /languages
        /en
            validation.php
        /zh-CN
            validation.php

```

> 该组件依赖`hyperf/translation`组件。

# 使用

## 表单请求验证
> 对于更复杂的验证场景，你可能想要创建一个“表单请求”。表单请求是包含验证逻辑的自定义请求类，要创建表单验证类，可以使用命令

```bash
php bin/hyperf.php gen:request FooRequest
```
生成的类位于 app\Request 目录下，如果该目录不存在，运行 `gen:request` 命令时会替我们生成。接下来我们添加验证规则到该类的 `rules` 方法：

```php
/**
 * 获取应用到请求的验证规则
 *
 * @return array
 */
public function rules(): array {
    return [
        'foo' => 'required|max:255',
        'bar' => 'required',
    ];
}
// todo 注：你可以在 rules 方法签名签名中注入任何依赖，它们会通过服务容器自动解析。
```

那么，验证规则如何生效呢？你所要做的就是在控制器方法中类型提示该请求类。这样表单输入请求会在控制器方法被调用之前被验证，这就是说你不需要将控制器方法和验证逻辑杂糅在一起：

```php
namespace App\Controller;

use App\Request\FooRequest;

class IndexController extends Controller
{
    public function index(FooRequest $request)
    {
        $foo = $this->request->input('foo');
        $bar = $this->request->input('bar');

        // do something

        return [
            'foo' => $foo,
            'bar' => $bar
        ];
    }
}
```

如果验证失败，验证器会抛一个 `ValidationException` 异常，你要做的就是在 `app\Exception\Handler` 目录中添加一个异常处理类；我们也提供了一个`ValidationExceptionHandler` 来处理 `ValidationException` 异常;默认的 `ValidationExceptionHandler` 不一定满足你的需求，你可以根据情况自定义验证失败后的行为；然后将异常处理类添加到 `config/autoload/exceptions.php` 中。

### 自定义错误消息
> 你可以通过重写 `messages` 方法自定义表单请求使用的错误消息，该方法应该返回属性/规则对数组及其对应错误消息：

```php
/**
 * 获取被定义验证规则的错误消息
 *
 * @return array
 * @translator laravelacademy.org
 */
public function messages(){
    return [
        'foo.required' => 'my foo is required',
        'bar.required'  => 'your bar is required',
    ];
}
```

### 自定义验证属性
>如果你想要将验证消息中的 `:attribute` 部分替换为自定义的属性名，可以通过重写 `attributes` 方法来指定自定义的名称。该方法会返回属性名及对应自定义名称键值对数组：
```php
/**
 * Get custom attributes for validator errors.
 *
 * @return array
 */
public function attributes()
{
    return [
        'foo' => 'Foo request',
    ];
}
```

## 手动创建验证器
> 如果你不想使用 `Request` 实例上的自动验证，可以使用 `ValidatorFactoryInterface` 接口实现 `ValidatorFactory` 手动创建一个验证器实例，该接口提供的 `make` 方法可用于生成一个新的验证器实例：
```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController extends Controller
{
    /**
     * @Inject()
     * @var ValidationFactoryInterface
     */
    protected $validationFactory;

    public function foo(RequestInterface $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'foo.required' => 'my foo is required',
                'bar.required' => 'your bar is required',
            ]
        );

        if (!$validator->fails()){
            // handle exception
            $errMsg = $validator->errors()->first();  
        }
        // do something
    }

}

```

## 自定义验证器


# 验证规则

## 使用方法

### 规则名

### 数组

### 回调



参考 Laravel 5.8 验证



