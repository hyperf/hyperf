# Hyperf Async Event

## 这是基于 async-queue 的 event 的异步框架
[event](https://hyperf.wiki/2.0/#/zh-cn/event)
[async-queue](https://hyperf.wiki/2.0/#/zh-cn/async-queue)

## Installation
```
composer require hyperf/event
composer require hyperf/async-queue
composer require hyperf/async-event
```
## Configuration
### Publish config
```
php bin/hyperf.php vendor:publish hyperf/async-event
```
## Config files:
```
+ ./config/autoload/async_queue.php
+ ./config/autoload/dependencies.php
+ ./config/autoload/processer.php
+ ./config/autoload/redis.php
```
## Usage
```php
$event = new \StdClass();
$event->async_delay = 0;
$event->data = 'test';
\Hyperf\Utils\ApplicationContext::getContainer()->get(EventDispatcherInterface::class)->dispatch($event);
```

### More configure
[Only some events are asynchronous?](./README_CUSTOM.MD)
