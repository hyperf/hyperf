# 開發者工具

## 安裝

```
composer require hyperf/devtool
```

# 支持的命令

```bash
php bin/hyperf.php
```

通過執行上面的命令可獲得 Command 所支持的所有命令，其中返回結果 `gen` 系列命令和 `vendor:publish` 命令主要為 `devtool` 組件提供支持

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
  gen:process        Create a new process class
 vendor
  vendor:publish     Publish any publishable configs from vendor packages.
```
