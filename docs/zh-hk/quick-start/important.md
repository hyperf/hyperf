# 編程須知

這裏收集各種通過 Hyperf 編程前應該知曉的知識點或內容點。

## 不能通過全局變量獲取屬性參數

在 `PHP-FPM` 下可以通過全局變量獲取到請求的參數，服務器的參數等，在 `Hyperf` 和 `Swoole` 內，都 **無法** 通過 `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`等`$_`開頭的變量獲取到任何屬性參數。

## 通過容器獲取的類都是單例

通過依賴注入容器獲取的都是進程內持久化的，是多個協程共享的，所以不能包含任何的請求唯一的數據或協程唯一的數據，這類型的數據都通過協程上下文去處理，具體請仔細閲讀 [依賴注入](zh-hk/di.md) 和 [協程](zh-hk/coroutine.md) 章節。

## 項目部署

> 官方的 Dockerfile 已經完成了以下操作。

線上代碼部署時，請務必開啟 `scan_cacheable`。

開啟此配置後，首次掃描時會生成代理類和註解緩存，再次啟動時，則可以直接使用緩存，極大優化內存使用率和啟動速度。因為跳過了掃描階段，所以會依賴 `Composer Class Map`，故我們必須要執行 `--optimize-autoloader` 優化索引。

綜上，線上更新代碼，重啟項目前，需要執行以下命令

```bash
# 優化 Composer 索引
composer dump-autoload -o
# 生成代理類和註解緩存
php bin/hyperf.php
```
