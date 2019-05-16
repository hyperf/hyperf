# Guzzle HTTP 客户端

[hyperf/guzzle](https://github.com/hyperf-cloud/guzzle) 组件基于 Guzzle 进行协程处理，通过 Swoole HTTP 客户端作为协程驱动替换到 Guzzle 内，以达到 HTTP 客户端的协程化。

## 安装

```bash
composer require hyperf/guzzle
```

## 使用

只需要该组件内的 `Hyperf\Guzzle\CoroutineHandler` 作为处理器设置到 Guzzle 客户端内即可转为协程化运行，为了方便创建协程的 Guzzle 对象，我们提供了一个工厂类 `Hyperf\Guzzle\ClientFactory` 来便捷的创建客户端，代码示例如下：

```php
<?php 
use Hyperf\Guzzle\ClientFactory;

class Foo {
    /**
     * @var \Hyperf\Guzzle\ClientFactory
     */
    private $clientFactory;
    
    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    
    public function bar()
    {
        // $options 等同于 GuzzleHttp\Client 构造函数的 $config 参数
        $options = [];
        // $client 为协程化的 GuzzleHttp\Client 对象
        $client = $this->clientFactory->create($options);
    }
}
```