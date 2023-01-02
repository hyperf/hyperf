# box, by Hyperf

Box 致力于帮助提升 PHP 应用程序的编程体验，尤其有助于 Hyperf 应用，管理 PHP 环境和相关依赖，同时提供将 PHP 应用程序打包为二进制程序的能力，还提供反向代理服务来管理和部署 Swoole/Swow 服务。

### 使用

#### 安装 box

##### Mac

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_macos -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// 确保 /usr/local/bin/box 在你的 $PATH 环境中，或者将 `box` 放到你想要的任意 $PATH 路径中
```

##### Linux x86_64

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// 确保 /usr/local/bin/box 在你的 $PATH 环境中，或者将 `box` 放到你想要的任意 $PATH 路径中
```
##### Linux aarch64

目前我们缺少 AARCH64 Github Actions Runner，所以无法及时构建 AARCH64 版本的 bin 文件。

```bash
wget https://github.com/hyperf/box/releases/download/v0.0.3/box_php8.1_aarch64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// 确保 /usr/local/bin/box 在你的 $PATH 环境中，或者将 `box` 放到你想要的任意 $PATH 路径中
```

##### Windows

```powershell
curl -o box.exe https://github.com/hyperf/box/releases/download/v0.5.5/box_x64_windows.exe
// 将 `box.exe` 放到你想要的任意 Path 环境变量路径中，同时 Windows 版本在执行时需要在命令行中使用 `box.exe` 而不是 `box`
```

#### 初始化 Github Access Token

Box 需要一个 Github 访问令牌来请求 Github API，以检索包的版本。

1. [创建 Github Access Token](https://github.com/settings/tokens/new)，`workflow` 范围需要勾选；
2. 运行 `box config set github.access-token <Your Token>` 命令来设置您的 token；
3. 玩得开心 ~

#### 设置 Box Kernel

默认情况下，Box 由 Swow Kernel 提供支持，但是我们也提供了 Swoole Kernel，您可以通过 `box config set kernel swoole` 来切换为 Swoole Kernel，但是需要注意的是，Swoole Kernel 仅支持 PHP 8.1 版本，且不支持构建二进制程序功能和 Windows 系统环境。

```bash
// 设置为 Swow Kernel [默认]
box config set kernel swow

// 设置为 Swoole Kernel (不支持 Windows)
box config set kernel swoole
```

### 命令

- `box get pkg@version`从远程安装包，`pkg`是包名，`version`是包的版本，`box get pkg`表示安装最新版本的 pkg，例如, 运行 `box get php@8.1` 安装 PHP 8.1, 运行 `box get composer` 安装最新的 composer bin
- `box build-prepare` 为 `build` 和 `build-self` 命令做好相关环境的准备
- `box build-self` 构建 `box` bin 本身
- `box build <path>` 将 Hyperf 应用程序构建成二进制文件
- `box self-update` 将 `box` bin 更新至最新版本
- `box config list` 输出 box 配置文件的所有内容
- `box config get <key>` 从配置文件中按键检索值
- `box config set <key> <value>`通过 key 设置 value 到配置文件中
- `box config unset <key>`按 key 删除配置值
- `box config set-php-version <version>`设置 box 的当前 PHP 版本，可用值：8.0 | 8.1
- `box config get-php-version <version>`获取 box 的当前设置的 PHP 版本
- `box reverse-proxy -u <upsteamHost:upstreamPort>` 启动一个反向代理 HTTP 服务器，用于将 HTTP 请求转发到指定的多个上游服务器
- `box php <argument>` 通过当前 box 的 PHP 版本运行任何 PHP 命令
- `box composer <argument>`通过当前 box 的 PHP 版本运行任何 Composer 命令，composer bin 的版本取决于最后执行的`get composer`命令
- `box php-cs-fixer <argument>` 通过当前 box 的 PHP 版本运行任何 `php-cs-fixer` 命令，composer bin 的版本取决于最后执行的 `get php-cs-fixer` 命令
- `box cs-fix <argument>` 通过当前 box 的 PHP 版本运行 `php-cs-fixer fix` 命令，composer bin 的版本取决于最后执行的 `get php-cs-fixer` 命令
- `box phpstan <argument>` 通过当前 box 的 PHP 版本运行任何 `phpstan` 命令，composer bin 的版本取决于最后执行的 `get phpstan` 命令，此命令仅在 box v0.3.0 及以上的版本中可用
- `box pint <argument>` 通过当前 box 的 PHP 版本运行任何 `pint` 命令，composer bin 的版本取决于最后执行的 `get pint` 命令，此命令仅在 box v0.3.0 及以上的版本中可用
- `box version` 输出当前 box bin 的版本号

### 关于 Swow-Skeleton

希望体验 Box 完整功能的朋友，需要通过 Swow Kernel 来运行，因此您需要基于 [hyperf/swow-skeleton](https://github.com/hyperf/swow-skeleton) 来运行您的项目，可通过 `box composer create-project hyperf/swow-skeleton:dev-master` 命令来创建一个基于 Hyperf 3.0 RC 版的 Swow 骨架项目。
