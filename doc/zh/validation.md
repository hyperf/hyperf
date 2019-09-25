# 验证

Hyperf Validation组件让您的项目轻松验证请求数据。

# 安装

```bash
composer require hyperf/validation
```

> 该组件依赖`hyperf/translation`组件。



### 自定义错误消息
>你可以通过重写 messages 方法自定义表单请求使用的错误消息，该方法应该返回属性/规则对数组及其对应错误消息：
```php
/**
 * 获取被定义验证规则的错误消息
 *
 * @return array
 * @translator laravelacademy.org
 */
public function messages(){
    return [
        'title.required' => 'A title is required',
        'body.required'  => 'A message is required',
    ];
}
```


### 自定义验证属性
>如果你想要将验证消息中的 :attribute 部分替换为自定义的属性名，可以通过重写 attributes 方法来指定自定义的名称。该方法会返回属性名及对应自定义名称键值对数组：
```php
/**
 * Get custom attributes for validator errors.
 *
 * @return array
 */
public function attributes()
{
    return [
        'email' => 'email address',
    ];
}
```


## 手动创建验证器
>如果你不想使用请求实例上的 validate 方法，可以使用 Validator 门面手动创建一个验证器实例，该门面提供的 make 方法可用于生成一个新的验证器实例：
```php
<?php

namespace App\Http\Controllers;

use Hyperf\Validation\ValidatorFactory;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller{
    
    protected $validationFactory;
    
    public function __construct(ContainerInterface $container) {
        $this->validationFactory = $container->get(ValidatorFactory::class);
    }
    
    /**
     * 存储新的博客文章
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(RequestInterface $request)
    {
        $validator = $this->validationFactory->make($request->input->all(), [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ]);

        if ($validator->fails()) {
            // todo 
        }
        // todo
    }
}
```