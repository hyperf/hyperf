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