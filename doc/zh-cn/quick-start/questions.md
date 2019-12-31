# 常见问题

## Swoole 短名未关闭

```
[ERROR] Swoole short name have to disable before start server, please set swoole.use_shortname = 'Off' into your php.ini.
```

这可能是因为你按以下的方式设置了

```
// 这些都是错误的，注意 `大小写` 和 `引号`
swoole.use_shortname = 'off'
swoole.use_shortname = off
swoole.use_shortname = Off
// 下面的才是正确的
swoole.use_shortname = 'Off'
```

> 注意该配置必须于 php.ini 内配置，无法通过 ini_set() 函数来重写

## 代理类缓存

代理类缓存一旦生成，将不会再重新覆盖。所以当你修改了已经生成代理类的文件时，需要手动清理。

代理类位置如下

```
runtime/container/proxy/
```

重新生成缓存命令，新缓存会覆盖原目录

```bash
vendor/bin/init-proxy.sh
```

删除代理类缓存

```bash
rm -rf ./runtime/container/proxy
```

所以单测命令可以使用以下代替：

```bash
vendor/bin/init-proxy.sh && composer test
```

同理，启动命令可以使用以下代替

```bash
vendor/bin/init-proxy.sh && php bin/hyperf.php start
```

## PHP7.3 下预先生成代理的脚本 执行失败

`php bin/hyperf.php di:init-proxy` 脚本在 `PHP7.3` 的 `Docker` 打包时，会因为返回码是 `1` 而失败。

> 具体原因还在定位中

以下通过重写 `init-proxy.sh` 脚本绕过这个问题。

```bash
#!/usr/bin/env bash

php /opt/www/bin/hyperf.php di:init-proxy

echo Started.
```

对应的 `Dockerfile` 修改以下代码，省略无用的代码展示。

```dockerfile
RUN composer install --no-dev \
    && composer dump-autoload -o \
    && ./init-proxy.sh
```
