# 安裝

## 伺服器要求

Hyperf 對系統環境有一些要求，僅可運行於 Linux 和 Mac 環境下，但由於 Docker 虛擬化技術的發展，在 Windows 下也可以通過 Docker for Windows 來作為執行環境，通常來說 Mac 環境下，我們更推薦本地環境部署，以避免 Docker 共享磁碟緩慢導致 Hyperf 啟動速度慢的問題。   

[hyperf\hyperf-docker](https://github.com/hyperf/hyperf-docker) 專案內已經為您準備好了各種版本的 Dockerfile ，或直接基於已經構建好的 [hyperf\hyperf](https://hub.docker.com/r/hyperf/hyperf) 映象來執行。   

當您不想採用 Docker 來作為執行的環境基礎時，您需要確保您的執行環境達到了以下的要求：   

 - PHP >= 7.2
 - Swoole PHP 擴充套件 >= 4.5，並關閉了 `Short Name`
 - OpenSSL PHP 擴充套件
 - JSON PHP 擴充套件
 - PDO PHP 擴充套件 （如需要使用到 MySQL 客戶端）
 - Redis PHP 擴充套件 （如需要使用到 Redis 客戶端）
 - Protobuf PHP 擴充套件 （如需要使用到 gRPC 服務端或客戶端）


## 安裝 Hyperf

Hyperf 使用 [Composer](https://getcomposer.org) 來管理專案的依賴，在使用 Hyperf 之前，請確保你的執行環境已經安裝好了 Composer。

### 通過 `Composer` 建立專案

[hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 專案是我們已經為您準備好的一個骨架專案，內建了一些常用的元件及相關配置的檔案及結構，是一個可以快速用於業務開發的 Web 專案基礎，在安裝時，您可根據您自身的需求，對元件依賴進行選擇。   
執行下面的命令可以於當前所在位置建立一個 hyperf-skeleton 專案
```
composer create-project hyperf/hyperf-skeleton 
```

### Docker 下開發

假設您的本機環境並不能達到 Hyperf 的環境要求，或對於環境配置不是那麼熟悉，那麼您可以通過以下方法來執行及開發 Hyperf 專案：

```
# 下載並執行 hyperf/hyperf 映象，並將映象內的專案目錄繫結到宿主機的 /tmp/skeleton 目錄
docker run -v /tmp/skeleton:/hyperf-skeleton -p 9501:9501 -it --entrypoint /bin/sh hyperf/hyperf:latest

# 映象容器執行後，在容器內安裝 Composer
wget https://github.com/composer/composer/releases/download/1.8.6/composer.phar
chmod u+x composer.phar
mv composer.phar /usr/local/bin/composer
# 將 Composer 映象設定為阿里雲映象，加速國內下載速度
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer

# 通過 Composer 安裝 hyperf/hyperf-skeleton 專案
composer create-project hyperf/hyperf-skeleton

# 進入安裝好的 Hyperf 專案目錄
cd hyperf-skeleton
# 啟動 Hyperf
php bin/hyperf.php start
```

接下來，就可以在 `/tmp/skeleton` 中看到您安裝好的程式碼了。由於 Hyperf 是持久化的 CLI 框架，當您修改完您的程式碼後，通過 `CTRL + C` 終止當前啟動的程序例項，並重新執行 `php bin/hyperf.php start` 啟動命令即可。

## 存在相容性問題的擴充套件

由於 Hyperf 基於 Swoole 協程實現，而 Swoole 4 帶來的協程功能是 PHP 前所未有的，所以與不少擴充套件都仍存在相容性的問題。   
以下擴充套件（包括但不限於）都會造成一定的相容性問題，不能與之共用或共存：

- xhprof
- xdebug
- blackfire
- trace
- uopz
