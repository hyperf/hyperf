# box, by Hyperf

Box 致力於幫助提升 PHP 應用程式的程式設計體驗，尤其有助於 Hyperf 應用，管理 PHP 環境和相關依賴，同時提供將 PHP 應用程式打包為二進位制程式的能力，還提供反向代理服務來管理和部署 Swoole/Swow 服務。

### 使用

#### 安裝 box

##### Mac

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_macos -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// 確保 /usr/local/bin/box 在你的 $PATH 環境中，或者將 `box` 放到你想要的任意 $PATH 路徑中
```

##### Linux x86_64

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// 確保 /usr/local/bin/box 在你的 $PATH 環境中，或者將 `box` 放到你想要的任意 $PATH 路徑中
```
##### Linux aarch64

目前我們缺少 AARCH64 Github Actions Runner，所以無法及時構建 AARCH64 版本的 bin 檔案。

```bash
wget https://github.com/hyperf/box/releases/download/v0.0.3/box_php8.1_aarch64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// 確保 /usr/local/bin/box 在你的 $PATH 環境中，或者將 `box` 放到你想要的任意 $PATH 路徑中
```

##### Windows

```powershell
curl -o box.exe https://github.com/hyperf/box/releases/download/v0.5.5/box_x64_windows.exe
// 將 `box.exe` 放到你想要的任意 Path 環境變數路徑中，同時 Windows 版本在執行時需要在命令列中使用 `box.exe` 而不是 `box`
```

#### 初始化 Github Access Token

Box 需要一個 Github 訪問令牌來請求 Github API，以檢索包的版本。

1. [建立 Github Access Token](https://github.com/settings/tokens/new)，`workflow` 範圍需要勾選；
2. 執行 `box config set github.access-token <Your Token>` 命令來設定您的 token；
3. 玩得開心 ~

#### 設定 Box Kernel

預設情況下，Box 由 Swow Kernel 提供支援，但是我們也提供了 Swoole Kernel，您可以透過 `box config set kernel swoole` 來切換為 Swoole Kernel，但是需要注意的是，Swoole Kernel 僅支援 PHP 8.1 版本，且不支援構建二進位制程式功能和 Windows 系統環境。

```bash
// 設定為 Swow Kernel [預設]
box config set kernel swow

// 設定為 Swoole Kernel (不支援 Windows)
box config set kernel swoole
```

### 命令

- `box get pkg@version`從遠端安裝包，`pkg`是包名，`version`是包的版本，`box get pkg`表示安裝最新版本的 pkg，例如, 執行 `box get php@8.1` 安裝 PHP 8.1, 執行 `box get composer` 安裝最新的 composer bin
- `box build-prepare` 為 `build` 和 `build-self` 命令做好相關環境的準備
- `box build-self` 構建 `box` bin 本身
- `box build <path>` 將 Hyperf 應用程式構建成二進位制檔案
- `box self-update` 將 `box` bin 更新至最新版本
- `box config list` 輸出 box 配置檔案的所有內容
- `box config get <key>` 從配置檔案中按鍵檢索值
- `box config set <key> <value>`透過 key 設定 value 到配置檔案中
- `box config unset <key>`按 key 刪除配置值
- `box config set-php-version <version>`設定 box 的當前 PHP 版本，可用值：8.0 | 8.1
- `box config get-php-version <version>`獲取 box 的當前設定的 PHP 版本
- `box reverse-proxy -u <upsteamHost:upstreamPort>` 啟動一個反向代理 HTTP 伺服器，用於將 HTTP 請求轉發到指定的多個上游伺服器
- `box php <argument>` 通過當前 box 的 PHP 版本執行任何 PHP 命令
- `box composer <argument>`通過當前 box 的 PHP 版本執行任何 Composer 命令，composer bin 的版本取決於最後執行的 `get composer` 命令
- `box php-cs-fixer <argument>` 通過當前 box 的 PHP 版本執行任何 `php-cs-fixer` 命令，composer bin 的版本取決於最後執行的 `get php-cs-fixer` 命令
- `box cs-fix <argument>` 通過當前 box 的 PHP 版本執行 `php-cs-fixer fix` 命令，composer bin 的版本取決於最後執行的 `get php-cs-fixer` 命令
- `box phpstan <argument>` 通過當前 box 的 PHP 版本執行任何 `phpstan` 命令，composer bin 的版本取決於最後執行的 `get phpstan` 命令，此命令僅在 box v0.3.0 及以上的版本中可用
- `box pint <argument>` 通過當前 box 的 PHP 版本執行任何 `pint` 命令，composer bin 的版本取決於最後執行的 `get pint` 命令，此命令僅在 box v0.3.0 及以上的版本中可用
- `box version` 輸出當前 box bin 的版本號

### 關於 Swow-Skeleton

希望體驗 Box 完整功能的朋友，需要透過 Swow Kernel 來執行，因此您需要基於 [hyperf/swow-skeleton](https://github.com/hyperf/swow-skeleton) 來執行您的專案，可透過 `box composer create-project hyperf/swow-skeleton` 命令來建立一個基於 Hyperf 3.0 版的 Swow 骨架專案。
