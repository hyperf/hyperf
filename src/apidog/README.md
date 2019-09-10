# hyperf apidog 

## Api Watch Dog
一个 [Hyperf](https://github.com/hyperf-cloud/hyperf) 框架的 Api 参数校验及 swagger 文档生成扩展

1.  根据注解自动进行Api参数的校验, 业务代码更纯粹.
2.  根据注解自动生成Swagger文档, 让接口文档维护更省心.

## 安装

```
composer require hyperf/apidog dev-master
```

## 配置

```php
// config/autoload/middlewares.php 定义使用中间件
<?php
declare(strict_types=1);

return [
    'http' => [
        Hyperf\Apidog\Middleware\ValidationMiddleware::class,
    ],
];

// config/autoload/swagger.php  swagger 基础信息
<?php
declare(strict_types=1);

return [
    'output_file' => BASE_PATH . '/public/swagger.json',
    'swagger' => '2.0',
    'info' => [
        'description' => 'hyperf swagger api desc',
        'version' => '1.0.0',
        'title' => 'HYPERF API DOC',
    ],
    'host' => 'apidog.com',
    'schemes' => ['http']
];

// config/dependencies.php  重写 DispathcerFactory 依赖
<?php
declare(strict_types=1);

return [
    'dependencies' => [
        Hyperf\HttpServer\Router\DispatcherFactory::class => Hyperf\Apidog\DispathcerFactory::class
    ],
];

```

## 使用

```php
<?php
declare(strict_types = 1);
namespace App\Controller;

use Hyperf\Apidog\Annotation\ApiController;
use Hyperf\Apidog\Annotation\ApiResponse;
use Hyperf\Apidog\Annotation\Body;
use Hyperf\Apidog\Annotation\DeleteApi;
use Hyperf\Apidog\Annotation\FormData;
use Hyperf\Apidog\Annotation\GetApi;
use Hyperf\Apidog\Annotation\Header;
use Hyperf\Apidog\Annotation\PostApi;
use Hyperf\Apidog\Annotation\Query;

/**
 * @ApiController(tag="用户管理", description="用户的新增/修改/删除接口")
 */
class UserController extends Controller
{

    /**
     * @PostApi(path="/user", description="添加一个用户")
     * @Header(key="token|接口访问凭证", rule="required|cb_checkToken")
     * @FormData(key="name|名称", rule="required|trim|max_width[10]|min_width[2]")
     * @FormData(key="age|年龄", rule="int|enum[0,1]")
     * @ApiResponse(code="-1", description="参数错误")
     * @ApiResponse(code="0", description="创建成功", schema={"id":1})
     */
    public function add()
    {
        return [
            'code' => 0,
            'id' => 1
        ];
    }

    /**
     * @DeleteApi(path="/user", description="删除用户")
     * @Body(rules={
     *     "id|用户id":"require|int|gt[0]"
     * })
     * @ApiResponse(code="-1", description="参数错误")
     * @ApiResponse(code="0", description="删除成功", schema={"id":1})
     */
    public function delete()
    {
        return [
            'code' => 0,
            'id' => 1
        ];
    }

    /**
     * @GetApi(path="/user", description="获取用户详情")
     * @Query(key="id", rule="required|int|gt[0]")
     * @ApiResponse(code="-1", description="参数错误")
     * @ApiResponse(code="0", schema={"id":1,"name":"张三","age":1})
     */
    public function get()
    {
        return [
            'code' => 0,
            'id' => 1,
            'name' => '张三',
            'age' => 1
        ];
    }
}
```

## 实现思路

api参数的自动校验: 通过中间件拦截 http 请求, 根据注解中的参数定义, 通过 `valiation` 自动验证和过滤, 如果验证失败, 则拦截请求. 其中`valiation` 包含 规则校验, 参数过滤, 自定义校验 三部分. 

swagger文档生成: 在`php bin/hyperf.php start` 启动http-server时, 系统会扫描所有控制器注解, 通过注解中的 访问类型, 参数格式, 返回类型 等, 自动组装swagger.json结构, 最后输出到 `config/autoload/swagger.php` 定义的文件路径中

### validation详解

1.  参数校验 `src/Validation.php`中定义的 rule_** 格式的方法名

    `any`  任意类型

    `required` 必填

    `uri`  uri 格式

    `url` url 格式

    `email` 邮件格式

    `extended_json`  注释类型json字符串

     `json` json格式字符串

     `array` 数组

     `date`  2019-09-01 格式日志

    `datetime`, 

    `safe_password`  安全密码

    `in`  在 *** 之中, 例如 `type|类型=required|int|in[1,2,3]`

     `max_width` 最大长度

    `min_width` 最小长度

    `natural`自然数

    `alpha` 字母

    `alpha_number` 数字字母

    `alhpa_dash`, 数字字母下划线

    `number` 数字

    `match`匹配, 参数中 key1 与 key2 校验相同时使用, 例如`key2=match[key1]` 

    `mobile`手机号

    `gt` 大于

    `ge` 等于

    `lt` 小于

    `le` 小于等于

    `enum` 其中直接 `key=enum[1,2]` 类似`in`

2.  参数过滤 `src/Validation.php`中定义的 filter_** 格式的方法名

    `bool` 布尔过滤

    `int` int过滤

3.  控制器中定义的 自定义校验方法 例如rule为 `required|int|cb_customCheck`, 控制器中对应的 `checkCustom`方法, 将会自动调用

### swagger生成

1.  api类型定义 `GetApi`, `PostApi`, `PutApi`, `DeleteApi`
2.  参数定义 `Header`, `Query`, `FormData`, `Body`, `Path`
3.  返回结果定义 `ApiResponse` 

## Swagger展示

![swagger](http://tva1.sinaimg.cn/large/007X8olVly1g6j91o6xroj31k10u079l.jpg)

## TODO
- 多层级参数的校验
- swagger更多属性的支持
