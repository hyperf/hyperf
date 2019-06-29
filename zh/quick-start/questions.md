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

清理命令
```
php bin/hyperf.php di:init-proxy
```

所以单测命令可以使用以下代替
```
php bin/hyperf.php di:init-proxy && composer test
```

同理，启动命令可以使用以下代替
```
php bin/hyperf.php di:init-proxy && php bin/hyperf.php start
```

## Docker打包失败

显示 `wget: error getting response: Connection reset by peer`

修改我们默认的 `Dockerfile`，重新安装一下 `wget`，增加以下代码即可。

```
&& apk add wget \
```