

## Usage

## Install


```

composer require hyperf/distributed-lock


@Lock(mutex="",value="",ttl="")


```


## Configuration


### publish config file
```

php bin/hyperf.php vendor:publish hyperf/distributed-lock

```

### config file description

```
return [
    'prefix' => 'lock',         // 锁前缀
    'ttl' => 10,                // 锁超时自动释放
    'separator' => ':',         // 锁分隔符 如 redis 常用":"  consul、etcd常用 "/"
    'driver' => 'redis',        // 驱动名现有 redis|consul 可选
    'redis' => [                // redis驱动配置
        'drift_factor' => 0.01,
        'retry' => 0,           // 重试次数
        'retry_delay' => 200,   // 重试间隔
        'pools' => [
            'default',          // redis连接池名，可以多个，跟redis配置中对应
        ],
    ],
    'consul' => [               // consul驱动配置
        'retry' => 0,
        'retry_delay' => 200,   
    ],
];

```




## Lock annotation description

|Filed|type|desc(demo)|
|:---:|:---:|:---:|
|mutex|string|resource name|
|ttl| int |lock timeout(s) 10|
|failedCallback|callback|null|
|value| string |#{id}#{name}|



## Usage

```

use Hyperf\DistributedLock\Annotation\Lock;

class IndexController extends Controller
{
    /**
     * @return array
     *
     * Author: wangyi <chunhei2008@qq.com>
     *
     * @Lock(mutex="index",ttl=10,failedCallback={IndexController::class,"callback"})
     */
    public function index()
    {
        $user   = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method'  => $method,
            'message' => "Hello {$user}.",
        ];
    }

    public static function callback()
    {
        echo 'Lock failed', PHP_EOL;
    }
}

```