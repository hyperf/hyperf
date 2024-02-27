# 安裝

## 伺服器要求

Hyperf 對系統環境有一些要求，當您使用 Swoole 網路引擎驅動時，僅可運行於 Linux 和 Mac 環境下，但由於 Docker 虛擬化技術的發展，在 Windows 下也可以透過 Docker for Windows 來作為執行環境，通常來說 Mac 環境下，我們更推薦本地環境部署，以避免 Docker 共享磁碟緩慢導致 Hyperf 啟動速度慢的問題。當您使用 Swow 網路引擎驅動時，則可在 Windows、Linux、Mac 下執行。

[hyperf/hyperf-docker](https://github.com/hyperf/hyperf-docker) 專案內已經為您準備好了各種版本的 Dockerfile ，或直接基於已經構建好的 [hyperf/hyperf](https://hub.docker.com/r/hyperf/hyperf) 映象來執行。   

當您不想採用 Docker 來作為執行的環境基礎時，也可以考慮使用 [Box](zh-tw/eco/box.md) 來作為執行的基礎環境，如果您希望自行完成環境搭建，則您需要確保您的執行環境達到了以下的要求：   

 - PHP >= 8.1
 - 以下任一網路引擎
   - [Swoole PHP 擴充套件](https://github.com/swoole/swoole-src) >= 5.0，並關閉了 `Short Name`
   - [Swow PHP 擴充套件](https://github.com/swow/swow) >= 1.4
 - JSON PHP 擴充套件
 - Pcntl PHP 擴充套件（僅在 Swoole 引擎時）
 - OpenSSL PHP 擴充套件（如需要使用到 HTTPS）
 - PDO PHP 擴充套件 （如需要使用到 MySQL 客戶端）
 - Redis PHP 擴充套件 （如需要使用到 Redis 客戶端）
 - Protobuf PHP 擴充套件 （如需要使用到 gRPC 服務端或客戶端）

## 安裝 Hyperf

Hyperf 使用 [Composer](https://getcomposer.org) 來管理專案的依賴，在使用 Hyperf 之前，請確保你的執行環境已經安裝好了 Composer。

### 透過 `Composer` 建立專案

我們已經為您準備好的一個骨架專案，內建了一些常用的元件及相關配置的檔案及結構，是一個可以快速用於業務開發的 Web 專案基礎，在安裝時，您可根據您自身的需求，對元件依賴進行選擇。   
執行下面的命令可以於當前所在位置建立一個 skeleton 專案

基於 Swoole 驅動：   
```
composer create-project hyperf/hyperf-skeleton 
```
基於 Swow 驅動：   
```
composer create-project hyperf/swow-skeleton 
```

> 安裝過程中，對於自己不清楚的選項，請直接使用回車處理，避免因自動添加了部分監聽器，但又沒有正確配置時，導致服務無法啟動的問題。

### Docker 下開發

假設您的本機環境並不能達到 Hyperf 的環境要求，或對於環境配置不是那麼熟悉，那麼您可以透過以下方法來執行及開發 Hyperf 專案：

- 啟動容器

可以根據實際情況，對映到宿主機對應的目錄，以下以 `/workspace/skeleton` 為例

> 如果 docker 啟動時開啟了 selinux-enabled 選項，容器內訪問宿主機資源就會受限，所以啟動容器時可以增加 --privileged -u root 選項

```shell
docker run --name hyperf \
-v /workspace/skeleton:/data/project \
-w /data/project \
-p 9501:9501 -it \
--privileged -u root \
--entrypoint /bin/sh \
hyperf/hyperf:8.1-alpine-v3.18-swoole
```

- 建立專案

```shell
composer create-project hyperf/hyperf-skeleton
```

- 啟動專案

```shell
cd hyperf-skeleton
php bin/hyperf.php start
```

接下來，就可以在宿主機 `/workspace/skeleton/hyperf-skeleton` 中看到您安裝好的程式碼了。
由於 Hyperf 是持久化的 CLI 框架，當您修改完您的程式碼後，透過 `CTRL + C` 終止當前啟動的程序例項，並重新執行 `php bin/hyperf.php start` 啟動命令即可。

## 存在相容性問題的擴充套件

由於 Hyperf 基於 Swoole 協程實現，而 Swoole 4 帶來的協程功能是 PHP 前所未有的，所以與不少擴充套件都仍存在相容性的問題。   
以下擴充套件（包括但不限於）都會造成一定的相容性問題，不能與之共用或共存：

- xhprof
- xdebug (當 PHP 版本 >= 8.1 且 Swoole 版本大於等於 5.0.2 時可用)
- blackfire
- trace
- uopz
