# 常見問題

## Swoole 短名未關閉

```
[ERROR] Swoole short name have to disable before start server, please set swoole.use_shortname = 'Off' into your php.ini.
```

這可能是因為你按以下的方式設置了

```
// 這些都是錯誤的，注意 `大小寫` 和 `引號`
swoole.use_shortname = 'off'
swoole.use_shortname = off
swoole.use_shortname = Off
// 下面的才是正確的
swoole.use_shortname = 'Off'
```

> 注意該配置必須於 php.ini 內配置，無法通過 ini_set() 函數來重寫

## 代理類緩存

代理類緩存一旦生成，將不會再重新覆蓋。所以當你修改了已經生成代理類的文件時，需要手動清理。

代理類位置如下

```
runtime/container/proxy/
```

重新生成緩存命令，新緩存會覆蓋原目錄

```bash
vendor/bin/init-proxy.sh
```

刪除代理類緩存

```bash
rm -rf ./runtime/container/proxy
```

所以單測命令可以使用以下代替：

```bash
vendor/bin/init-proxy.sh && composer test
```

同理，啟動命令可以使用以下代替

```bash
vendor/bin/init-proxy.sh && php bin/hyperf.php start
```

## PHP7.3 下預先生成代理的腳本 執行失敗

`php bin/hyperf.php di:init-proxy` 腳本在 `PHP7.3` 的 `Docker` 打包時，會因為返回碼是 `1` 而失敗。

> 具體原因還在定位中

以下通過重寫 `init-proxy.sh` 腳本繞過這個問題。

```bash
#!/usr/bin/env bash

php /opt/www/bin/hyperf.php di:init-proxy

echo Started.
```

對應的 `Dockerfile` 修改以下代碼，省略無用的代碼展示。

```dockerfile
RUN composer install --no-dev \
    && composer dump-autoload -o \
    && ./init-proxy.sh
```
