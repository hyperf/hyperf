## PHP XxlJob Client

基于 [Hyperf](https://github.com/hyperf/hyperf) 框架的 xxlJob PHP 客户端

##### 优点

- 分布式任务调度平台
- 任务可以随时关闭与开启
- 日志可通过服务端查看

##### 缺点

- 不能取消正在执行的任务


## 安装

```
composer require hyperf/xxljob
```

## 使用

#### 发布配置文件

```bash
php bin/hyperf.php vendor:publish hyperf/xxljob
```
##### 配置信息
> config/autoload/xxl_job.php
```php
return [
    // enable false 将不会启动服务
    'enable' => true,
    //服务端地址
    'admin_address' => 'http://127.0.0.1:8769/xxl-job-admin',
    //执行器名称
    'app_name' => 'xxl-job-demo',
    //客户端请求前缀
    'prefix_url' => 'php-xxl-job',
    //access_token
    'access_token' => null,
    'log' => [
        'filename' => BASE_PATH . '/runtime/logs/xxl-job/job.log',
        //日志最大留存天数 0:不删除
        'maxDay' => 30,
    ],
];
```

#### BEAN模式(类形式)
Bean模式任务，支持基于类的开发方式，每个任务对应一个PHP类。
##### 步骤一：新建目录，开发Job类：
```php
class DemoJob extends AbstractJobHandler{}
```
##### 步骤二：调度中心，新建调度任务
```
1. 编写job类继承AbstractJobHandler
2. 注解配置：为Job类添加注解 "#[JobHandler('自定义jobhandler名称')]"，注解value值对应的是调度中心新建任务的JobHandler属性的值。
3. 执行日志：需要通过 "$this->getXxlJobHelper()->log('...')" 打印执行日志;
```
对新建的任务进行参数配置，运行模式选中 “BEAN模式”，JobHandler属性填写任务注解“#[JobHandler]”中定义的值
![hMvJnQ](https://www.xuxueli.com/doc/static/xxl-job/images/img_ZAsz.png)

#### 完整示例
```php
namespace App\Job;

use Hyperf\XxlJob\Annotation\JobHandler;
use Hyperf\XxlJob\Handler\AbstractJobHandler;

#[JobHandler('demoJob')]
class DemoJob extends AbstractJobHandler
{
    
    public function handle(): void
    {
        //获取参数
        $params = $this->getParams();
        //处理...
        for ($i=1;$i<5;$i++) {
            $this->getXxlJobHelper()->log($i);
            $this->getXxlJobHelper()->log("demoJob");
        }

    }
}
```
详细文档 [xxl-job](https://www.xuxueli.com/xxl-job) 
