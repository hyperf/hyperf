
通過 `hyperf/nano` 可以在無骨架、零配置的情況下快速搭建 Hyperf 應用。

## 安裝

```php
composer install hyperf/nano
```

## 快速開始

```php
<?php
// index.php
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create('0.0.0.0', 9051);

$app->get('/', function () {

    $user = $this->request->input('user', 'nano');
    $method = $this->request->getMethod();

    return [
        'message' => "hello {$user}",
        'method' => $method,
    ];

});

$app->run();
```

啟動：

```bash
php index.php start
```

簡潔如此。

## 特性

* 無骨架
* 零配置
* 快速啟動
* 閉包風格
* 支持註解外的全部 Hyperf 功能
* 兼容全部 Hyperf 組件
* Phar 友好

## 更多示例

### 路由

$app 集成了 Hyperf 路由器的所有方法。

```php
<?php
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->addGroup('/nano', function () use ($app) {
    $app->addRoute(['GET', 'POST'], '/{id:\d+}', function($id) {
        return '/nano/'.$id;
    });
    $app->put('/{name:.+}', function($name) {
        return '/nano/'.$name;
    });
});

$app->run();
```

### DI 容器
```php
<?php
use Hyperf\Nano\ContainerProxy;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

class Foo {
    public function bar() {
        return 'bar';
    }   
}

$app = AppFactory::create();
$app->getContainer()->set(Foo::class, new Foo());

$app->get('/', function () {
    /** @var ContainerProxy $this */
    $foo = $this->get(Foo::class);
    return $foo->bar();
});

$app->run();
```
> 所有 $app 管理的閉包回調中，$this 都被綁定到了 `Hyperf\Nano\ContainerProxy` 上。

### 中間件
```php
<?php
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function () {
    return $this->request->getAttribute('key');
});

$app->addMiddleware(function ($request, $handler) {
    $request = $request->withAttribute('key', 'value');
    return $handler->handle($request);
});

$app->run();
```

> 除了閉包之外，所有 $app->addXXX() 方法還接受類名作為參數。可以傳入對應的 Hyperf 類。

### 異常處理

```php
<?php
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function () {
    throw new \Exception();
});

$app->addExceptionHandler(function ($throwable, $response) {
    return $response->withStatus('418')
        ->withBody(new SwooleStream('I\'m a teapot'));
});

$app->run();
```

### 命令行

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->addCommand('echo', function(){
    $this->get(StdoutLoggerInterface::class)->info('A new command called echo!');
});

$app->run();
```

執行

```bash
php index.php echo
```

### 事件監聽

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->addListener(BootApplication::class, function($event){
    $this->get(StdoutLoggerInterface::class)->info('App started');
});

$app->run();
```

### 自定義進程
```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->addProcess(function(){
    while (true) {
        sleep(1);
        $this->get(StdoutLoggerInterface::class)->info('Processing...');
    }
});

$app->run();
```

### 定時任務

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->addCrontab('* * * * * *', function(){
    $this->get(StdoutLoggerInterface::class)->info('execute every second!');
});

$app->run();
```

### 使用 Hyperf 組件.

```php
<?php
use Hyperf\DB\DB;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->config([
    'db.default' => [
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
    ]
]);

$app->get('/', function(){
    return DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);
});

$app->run();
```
