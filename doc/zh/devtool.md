# 开发者工具

## 安装

```
composer require hyperf/devtool
```

# 支持的命令

```bash
php bin/hyperf.php
```

通过执行上面的命令可获得 Command 所支持的所有命令，其中返回结果 `gen` 系列命令和 `vendor:publish` 命令主要为 `devtool` 组件提供支持

```
 gen
  gen:amqp-consumer  Create a new amqp consumer class
  gen:amqp-producer  Create a new amqp producer class
  gen:aspect         Create a new aspect class
  gen:command        Create a new command class
  gen:controller     Create a new controller class
  gen:job            Create a new job class
  gen:listener       Create a new listener class
  gen:middleware     Create a new middleware class
  gen:migration      
  gen:process        Create a new process class
 migrate
  migrate:fresh      
  migrate:install    
  migrate:refresh    
  migrate:reset      
  migrate:rollback   
  migrate:status     
 queue
  queue:flush        Delete all message from failed queue.
  queue:info         Delete all message from failed queue.
  queue:reload       Reload all failed message into waiting queue.
 vendor
  vendor:publish     Publish any publishable configs from vendor packages.
```x
