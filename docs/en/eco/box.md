# box, by Hyperf

Box is committed to helping improve the programming experience of PHP applications, expecially for Hyperf, managing the PHP environment and related dependencies, providing the ability to package PHP applications as binary programs, and also providing reverse proxy services for managing and deploying Swoole/Swow applications.

## This is still an early experimental version, have fun ~

### Usage

#### Install box

##### Mac

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_macos -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```

##### Linux x86_64

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```
##### Linux aarch64

At present, we are short of AARCH64 Github Actions Runner, so we cannot timely construct the bin file of AARCH64 version.

```bash
wget https://github.com/hyperf/box/releases/download/v0.0.3/box_php8.1_aarch64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```

##### Windows

```powershell
curl -o box.exe https://github.com/hyperf/box/releases/download/v0.5.5/box_x64_windows.exe
// Put `box.exe` into any path in $PATH env that you want, and use `box.exe` instead of `box` when executing on Windows
```

#### Init Github Access Token

Box needs a Github Access Token to request github api, to retrieve the versions of the package.

1. [Create Github Access Token](https://github.com/settings/tokens/new), the `workflow` scope have to be selected.
2. Run `box config set github.access-token <Your Token>` to init the token.
3. Have fun ~

#### Setting the Box Kernel

By default, Box is supported by Swow Kernel, but we also provide Swoole Kernel, you can switch to Swoole Kernel by `box config set kernel swoole`, but it should be noted that Swoole Kernel only supports PHP 8.1 version, and The Build Binaries feature and Windows Systems are not supported.

```bash
// set to Swow Kernel [default]
box config set kernel swow

// set to Swoole Kernel (NOT supported on Windows)
box config set kernel swoole
````

### Commands

- `box get pkg@version` to install the package from remote automatically, `pkg` is the package name, and `version` is the version of package, `box get pkg` means to install the latest version of pkg, for example, run `box get php@8.1` to install the PHP 8.1, run `box get composer` to install the latest composer bin
- `box build-prepare` to get ready for `build` and `build-self` command
- `box build-self` to build the `box` bin itself
- `box build <path>` to build a Hyperf application into a binary file
- `box self-update` to update the `box` bin to latest version
- `box config list` to dump the config file
- `box config get <key>` to retrieve the value by key from config file
- `box config set <key> <value>` to set value by key into the config file
- `box config unset <key>` to unset the config value by key
- `box config set-php-version <version>` to set the current PHP version of box, available value: 8.0 | 8.1
- `box config get-php-version <version>` to get the current PHP version of box
- `box reverse-proxy -u <upsteamHost:upstreamPort>` to start a reverse proxy HTTP server for the upstream servers
- `box php <argument>` to run any PHP command via current PHP version of box
- `box composer <argument>` to run any Composer command via box, the version of the composer bin depends on the last executed `get composer` command
- `box php-cs-fixer <argument>` to run any `php-cs-fixer` command via box, the version of the composer bin depends on the last executed `get php-cs-fixer` command
- `box cs-fix <argument>` to run `php-cs-fix fix` command via box, the version of the composer bin depends on the last executed `get php-cs-fixer` command
- `box phpstan <argument>` to run any `phpstan` command via box, the version of the composer bin depends on the last executed `get phpstan` command, since box v0.3.0
- `box pint <argument>` to run any `pint` command via box, the version of the composer bin depends on the last executed `get pint` command, since box v0.3.0
- `box version` to dump the current version of the box bin

### About Swow Skeleton

If you want to experience the full features of Box, you need to run it based on the Swow Kernel, so you need to base your project on [hyperf/swow-skeleton](https://github.com/hyperf/swow-skeleton) to run your project, you can create a Swow skeleton project based on Hyperf 3.0 RC version by `box composer create-project hyperf/swow-skeleton:dev-master` command.
