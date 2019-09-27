# 验证器

## 前言

> [hyperf/validation](https://github.com/hyperf-cloud/validation) 衍生于 [illuminate/validation](https://github.com/illuminate/validation)，我们对它进行了一些改造，但保持了验证规则的相同。在这里感谢一下 Laravel 开发组，实现了如此强大好用的验证器组件。

## 安装

### 引入组件包

```bash
composer require hyperf/validation
```

### 添加中间件

您需要为使用到验证器组件的 Server 加上一个全局中间件 `Hyperf\Validation\Middleware\ValidationMiddleware` 配置，如下为 `http` Server 加上对应的全局中间件。如没有正确设置全局中间件，可能会导致 `表单请求(FormRequest)` 的使用方式无效。

```php
<?php
return [
    // http 对应 config/autoload/server.php 内每个 server 的 name 属性对应的值，该配置仅应用在该 Server 中
    'http' => [
        // 数组内配置您的全局中间件，顺序根据该数组的顺序
        \Hyperf\Validation\Middleware\ValidationMiddleware::class
        // 这里隐藏了其它中间件
    ],
];
```

### 添加异常处理器



### 发布验证器语言文件

```bash
php bin/hyperf.php vendor:publish hyperf/validation
```

执行上面的命令会将验证器的语言文件 `validation.php` 发布到对应的语言文件目录，`en` 指英文语言文件，`zh-CN` 指中文简体的语言文件，您可以按照实际需要对 `validation.php` 文件内容进行修改和自定义。

```
/storage
    /languages
        /en
            validation.php
        /zh-CN
            validation.php

```

> 由于存在多语言的功能，故该组件依赖 [hyperf/translation](https://github.com/hyperf-cloud/translation) 组件。


## 使用

### 表单请求验证

对于复杂的验证场景，您可以创建一个 `表单请求(FormRequest)`，表单请求是包含验证逻辑的一个自定义请求类，您可以通过执行下面的命令创建一个名为 `FooRequest` 的表单验证类：

```bash
php bin/hyperf.php gen:request FooRequest
```

表单验证类会生成于 `app\Request` 目录下，如果该目录不存在，运行命令时会自动创建目录。   
接下来我们添加一些验证规则到该类的 `rules` 方法：

```php
/**
 * 获取应用到请求的验证规则
 */
public function rules(): array
{
    return [
        'foo' => 'required|max:255',
        'bar' => 'required',
    ];
}
```

那么，验证规则要如何生效呢？您所要做的就是在控制器方法中通过类型提示声明该请求类为参数。这样在控制器方法被调用之前会验证传入的表单请求，这意味着你不需要在控制器中写任何验证逻辑并很好的解耦了这两部分的代码：

```php
<?php
namespace App\Controller;

use App\Request\FooRequest;

class IndexController
{
    public function index(FooRequest $request)
    {
        // 传入的请求通过验证...
        
        // 获取通过验证的数据...
        $validated = $request->validated();
    }
}
```

如果验证失败，验证器会抛一个 `Hyperf\Validation\ValidationException` 异常，您可以在通过添加一个自定义的异常处理类来处理该异常，与此同时，我们也提供了一个`Hyperf\Validation\ValidationExceptionHandler` 异常处理器来处理该异常，您也可以直接配置我们提供的异常处理器来处理。但默认提供的异常处理器不一定能够满足您的需求，您可以根据情况通过自定义异常处理器自定义处理验证失败后的行为。

#### 自定义错误消息

您可以通过重写 `messages` 方法来自定义表单请求使用的错误消息，该方法应该返回属性/规则对数组及其对应错误消息：

```php
/**
 * 获取已定义验证规则的错误消息
 */
public function messages(): array
{
    return [
        'foo.required' => 'foo is required',
        'bar.required'  => 'bar is required',
    ];
}
```

#### 自定义验证属性

如果您希望将验证消息中的 `:attribute` 部分替换为自定义的属性名，则可以通过重写 `attributes` 方法来指定自定义的名称。该方法会返回属性名及对应自定义名称键值对数组：

```php
/**
 * 获取验证错误的自定义属性
 */
public function attributes(): array
{
    return [
        'foo' => 'foo of request',
    ];
}
```

### 手动创建验证器

如果您不想使用 `表单请求(FormRequest)` 的自动验证功能，可以通过注入 `ValidationFactoryInterface` 接口类来获得验证器工厂类，然后通过 `make` 方法手动创建一个验证器实例：

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
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
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        if ($validator->fails()){
            // Handle exception
            $errorMessage = $validator->errors()->first();  
        }
        // Do something
    }
}
```

传给 `make` 方法的第一个参数是需要验证的数据，第二个参数则是该数据的验证规则。

#### 自定义错误消息

如果有需要，你也可以使用自定义错误信息代替默认值进行验证。有几种方法可以指定自定义信息。首先，你可以将自定义信息作为第三个参数传递给 `make` 方法：

```php
<?php
$messages = [
    'required' => 'The :attribute field is required.',
];

$validator = $this->validationFactory->make($request->all(), $rules, $messages);
```

在这个例子中， `:attribute` 占位符会被验证字段的实际名称替换。除此之外，你还可以在验证消息中使用其它占位符。例如：

```php
$messages = [
    'same'    => 'The :attribute and :other must match.',
    'size'    => 'The :attribute must be exactly :size.',
    'between' => 'The :attribute value :input is not between :min - :max.',
    'in'      => 'The :attribute must be one of the following types: :values',
];
```

#### 为给定属性指定自定义信息

有时候你可能只想为特定的字段自定义错误信息。只需在属性名称后使用「点」来指定验证的规则即可：

```php
$messages = [
    'email.required' => 'We need to know your e-mail address!',
];
```

#### 在 PHP 文件中指定自定义信息

在大多数情况下，您可能会在文件中指定自定义信息，而不是直接将它们传递给 `Validator` 。为此，需要把你的信息放置于 `storage/languages/xx/validation.php` 语言文件内的 `custom` 数组中。

#### 在 PHP 文件中指定自定义属性

如果你希望将验证信息的 `:attribute` 部分替换为自定义属性名称，你可以在 `storage/languages/xx/validation.php` 语言文件的 `attributes` 数组中指定自定义名称：

```php
'attributes' => [
    'email' => 'email address',
],
```

### 验证后钩子

验证器还允许你添加在验证成功之后允许的回调函数，以便你进行下一步的验证，甚至在消息集合中添加更多的错误消息。使用它只需在验证实例上使用 `after` 方法：

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
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
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        $validator->after(function ($validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }
        });
        
        if ($validator->fails()) {
            //
        }
    }
}
```

## 处理错误消息

通过 `Validator` 实例调用 `errors` 方法，会返回 `Hyperf\Utils\MessageBag` 实例，它拥有各种方便的方法处理错误信息。

### 查看特定字段的第一个错误信息

要查看特定字段的第一个错误消息，可以使用 `first` 方法：

```php
$errors = $validator->errors();

echo $errors->first('foo');
```

### 查看特定字段的所有错误消息

如果你需要获取指定字段的所有错误信息的数组，则可以使用 `get` 方法：

```php
foreach ($errors->get('foo') as $message) {
    //
}
```

如果要验证表单的数组字段，你可以使用 `*` 来获取每个数组元素的所有错误消息：

```php
foreach ($errors->get('foo.*') as $message) {
    //
}
```

### 查看所有字段的所有错误消息

如果你想要得到所有字段的所有错误消息，可以使用 `all` 方法：

```php
foreach ($errors->all() as $message) {
    //
}
```

### 判断特定字段是否含有错误消息

`has` 方法可以被用来判断指定字段是否存在错误信息:

```php
if ($errors->has('foo')) {
    //
}
```



