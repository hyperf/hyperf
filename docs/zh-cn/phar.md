# phar打包

## 安装

```bash
composer require hyperf/phar
```

## 使用

### 默认打包

```shell
  php bin/hyperf.php phar:build
```

### 指定包名

```shell
  php bin/hyperf.php phar:build --name=your_project.phar
```

### 指定启动文件

```shell
  php bin/hyperf.php phar:build --bin=bin/hyperf.php
```
### 指定打包目录

```shell
  php bin/hyperf.php phar:build --path=BASE_PATH
```

## 运行

```shell
  php your_project.phar start
```

## 注意事项

打包后是以phar包的形式运行，不同与源代码模式运行，phar包中的runtime目录是不可写的，
所以以前存在runtime目录的pid，log都需要指定到其他地方。

* `server.php`中的`'pid_file' => BASE_PATH . '/runtime/hyperf.pid'`修改为`'pid_file' => '/tmp/runtime/hyperf.pid'`;

* `logger.php`中的` BASE_PATH . '/runtime/logs/hyperf.log'`修改为` '/tmp/runtime/logs/hyperf.log'`

打包的时候会修改`config.php`配置中的`scan_cacheable`配置项，把它设置为`true`.
所以这个文件中的配置格式建议使用推荐格式，不建议修改，修改后可能无法运行成功。
如果已经修改了这个文件，打包后无法运行phar包成功，可以设置环境变量`SCAN_CACHEABLE=true`.