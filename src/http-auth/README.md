# Hyperf 下 http 认证组件

- 基于 [fx/hyperf-http-auth](https://github.com/nfangxu/hyperf-http-auth)

- 仿照 laravel auth 组件, 抽离出其中的核心逻辑, 形成当前扩展包

- 将 UserProvider 与 Guard 抽离出去, 形成单独扩展包, 方便扩展, 默认使用以下组合

    * [fx/eloquent-provider](https://github.com/nfangxu/hyperf-auth-eloquent-provider) 使用 Eloquent ORM ;

    * [fx/session-guard](https://github.com/nfangxu/hyperf-auth-session-guard) 使用 session 作为 guard ;

## 使用
### 安装

```bash
composer require hyperf/http-auth
```

### 发布配置文件

```bash
php bin/hyperf.php vendor:publish hyperf/http-auth
```

### 创建用户 model 并修改为以下配置

```php
<?php
declare (strict_types=1);

namespace App\Model;

use Hyperf\HttpAuth\Contract\Authenticatable;
use Hyperf\DbConnection\Model\Model;

class User extends Model implements Authenticatable
{
    use \Hyperf\HttpAuth\Authenticatable;
}
```

### 配置依赖扩展

- `fx/session-guard` 依赖 `hyperf/session` 需要正确配置其相关内容 [官方文档](https://hyperf.wiki/#/zh-cn/session?id=%e9%85%8d%e7%bd%ae)

### 在 controller 中使用

```
<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\User;
use Hyperf\HttpAuth\Contract\HttpAuthContract;
use Hyperf\Di\Annotation\Inject;

class IndexController extends AbstractController
{
    /**
     * @Inject()
     * @var HttpAuthContract
     */
    protected $auth;

    public function index()
    {
        return $this->data();
    }

    /**
     * 登录
     */
    public function login()
    {
        /** 方式 1 */
        // 等价于 auth()->login(User::first());
        $this->auth->login(User::first());

        /** 方式 2 */
        // 等价于 auth()->attempt(['email' => 'xxx', 'password' => '123456']);
        $this->auth->attempt(['email' => 'xxx', 'password' => '123456']);

        return $this->data();
    }

    /**
     * 登出
     */
    public function logout()
    {
        // 等价于 auth()->logout();
        $this->auth->logout();
        return $this->data();
    }

    protected function data()
    {
        return [
            'user' => auth()->user(),
            'is_login' => auth()->check(),
        ];
    }
}
```

## 扩展 UserProvider

- 实现 `Hyperf\HttpAuth\Contract\UserProvider` 这个抽象类

- 添加 `Hyperf\HttpAuth\Annotation\UserProviderAnnotation` 类注解, 该注解接收一个参数, 为该驱动的名称

- 可参考: [fx/eloquent-provider](https://github.com/nfangxu/hyperf-auth-eloquent-provider)


## 扩展 Guard

- 实现 `Hyperf\HttpAuth\Contract\StatefulGuard` 这个抽象类

- 添加 `Hyperf\HttpAuth\Annotation\GuardAnnotation` 类注解, 该注解接收一个参数, 为该驱动的名称

- 可参考: [fx/session-guard](https://github.com/nfangxu/hyperf-auth-session-guard)
