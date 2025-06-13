# 安裝

## 服務器要求

Hyperf 對系統環境有一些要求，當您使用 Swoole 網絡引擎驅動時，僅可運行於 Linux 和 Mac 環境下，但由於 Docker 虛擬化技術的發展，在 Windows 下也可以通過 Docker for Windows 來作為運行環境，通常來説 Mac 環境下，我們更推薦本地環境部署，以避免 Docker 共享磁盤緩慢導致 Hyperf 啓動速度慢的問題。當您使用 Swow 網絡引擎驅動時，則可在 Windows、Linux、Mac 下運行。

[hyperf/hyperf-docker](https://github.com/hyperf/hyperf-docker) 項目內已經為您準備好了各種版本的 Dockerfile ，或直接基於已經構建好的 [hyperf/hyperf](https://hub.docker.com/r/hyperf/hyperf) 鏡像來運行。   

當您不想採用 Docker 來作為運行的環境基礎時，也可以考慮使用 [Box](zh-hk/eco/box.md) 來作為運行的基礎環境，如果您希望自行完成環境搭建，則您需要確保您的運行環境達到了以下的要求：   

 - PHP >= 8.1
 - 以下任一網絡引擎
   - [Swoole PHP 擴展](https://github.com/swoole/swoole-src) >= 5.0，並關閉了 `Short Name`
   - [Swow PHP 擴展](https://github.com/swow/swow) >= 1.4
 - JSON PHP 擴展
 - Pcntl PHP 擴展（僅在 Swoole 引擎時）
 - OpenSSL PHP 擴展（如需要使用到 HTTPS）
 - PDO PHP 擴展 （如需要使用到 MySQL 客户端）
 - Redis PHP 擴展 （如需要使用到 Redis 客户端）
 - Protobuf PHP 擴展 （如需要使用到 gRPC 服務端或客户端）

## 安裝 Hyperf

Hyperf 使用 [Composer](https://getcomposer.org) 來管理項目的依賴，在使用 Hyperf 之前，請確保你的運行環境已經安裝好了 Composer。

### 通過 `Composer` 創建項目

我們已經為您準備好的一個骨架項目，內置了一些常用的組件及相關配置的文件及結構，是一個可以快速用於業務開發的 Web 項目基礎，在安裝時，您可根據您自身的需求，對組件依賴進行選擇。   
執行下面的命令可以於當前所在位置創建一個 skeleton 項目

基於 Swoole 驅動：   
```
composer create-project hyperf/hyperf-skeleton 
```
基於 Swow 驅動：   
```
composer create-project hyperf/swow-skeleton 
```

> 安裝過程中，對於自己不清楚的選項，請直接使用回車處理，避免因自動添加了部分監聽器，但又沒有正確配置時，導致服務無法啓動的問題。

### Docker 下開發

假設您的本機環境並不能達到 Hyperf 的環境要求，或對於環境配置不是那麼熟悉，那麼您可以通過以下方法來運行及開發 Hyperf 項目：

- 啓動容器

可以根據實際情況，映射到宿主機對應的目錄，以下以 `/workspace/skeleton` 為例

> 如果 docker 啓動時開啓了 selinux-enabled 選項，容器內訪問宿主機資源就會受限，所以啓動容器時可以增加 --privileged -u root 選項

```shell
docker run --name hyperf \
-v /workspace/skeleton:/data/project \
-w /data/project \
-p 9501:9501 -it \
--privileged -u root \
--entrypoint /bin/sh \
hyperf/hyperf:8.1-alpine-v3.18-swoole
```

- 創建項目

```shell
composer create-project hyperf/hyperf-skeleton
```

- 啓動項目

```shell
cd hyperf-skeleton
php bin/hyperf.php start
```

接下來，就可以在宿主機 `/workspace/skeleton/hyperf-skeleton` 中看到您安裝好的代碼了。
由於 Hyperf 是持久化的 CLI 框架，當您修改完您的代碼後，通過 `CTRL + C` 終止當前啓動的進程實例，並重新執行 `php bin/hyperf.php start` 啓動命令即可。

## 存在兼容性問題的擴展

由於 Hyperf 基於 Swoole 協程實現，而 Swoole 4 帶來的協程功能是 PHP 前所未有的，所以與不少擴展都仍存在兼容性的問題。   
以下擴展（包括但不限於）都會造成一定的兼容性問題，不能與之共用或共存：

- xhprof
- xdebug (當 PHP 版本 >= 8.1 且 Swoole 版本大於等於 5.0.2 時可用)
- blackfire
- trace
- uopz
