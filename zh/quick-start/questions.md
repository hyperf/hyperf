# 常见问题

## Swoole短名显示未关闭

有小伙伴在关闭短名后，还是会显示以下提示

```
ERROR Swoole short name have to disable before start server, please set swoole.use_shortname = 'Off' into your php.ini.
```

这是因为你可能是这么设置的

```
swoole.use_shortname = 'off'
swoole.use_shortname = off
swoole.use_shortname = Off
```

以上这些都是不可以的，注意 `大小写` 和 `引号`