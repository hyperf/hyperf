# box, by Hyperf

Box is committed to helping improve the programming experience of PHP applications, especially for Hyperf applications. It manages PHP environments and related dependencies, provides the ability to package PHP applications into binary programs, and also provides reverse proxy services to manage and deploy Swoole/Swow services.

### Usage

#### Install box

##### Mac

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_macos -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Ensure /usr/local/bin/box is in your $PATH, or put `box` in any $PATH directory you prefer.
```

##### Linux x86_64

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Ensure /usr/local/bin/box is in your $PATH, or put `box` in any $PATH directory you prefer.
```

##### Linux aarch64

We currently lack an AARCH64 Github Actions Runner, so we cannot timely build AARCH64 versions of binary files.

```bash
wget https://github.com/hyperf/box/releases/download/v0.0.3/box_php8.1_aarch64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Ensure /usr/local/bin/box is in your $PATH, or put `box` in any $PATH directory you prefer.
```

##### Windows

```powershell
curl -o box.exe https://github.com/hyperf/box/releases/download/v0.5.5/box_x64_windows.exe
// Put `box.exe` in any Path environment variable directory you prefer. Note that on Windows, you need to use `box.exe` instead of `box` in the command line.
```

#### Initialize Github Access Token

Box requires a Github Access Token to request the Github API to retrieve package versions.

1. [Create Github Access Token](https://github.com/settings/tokens/new), and the `workflow` scope needs to be checked;
2. Run `box config set github.access-token <Your Token>` command to set your token;
3. Have fun ~

#### Set Box Kernel

By default, Box is powered by the Swow Kernel, but we also provide a Swoole Kernel. You can switch to the Swoole Kernel via `box config set kernel swoole`. Note that the Swoole Kernel only supports PHP 8.1, and does not support binary program construction or Windows system environments.

```bash
// Set to Swow Kernel [Default]
box config set kernel swow

// Set to Swoole Kernel (Windows not supported)
box config set kernel swoole
```

### Commands

- `box get pkg@version` Install a package from a remote source. `pkg` is the package name, `version` is the package version. `box get pkg` means installing the latest version of the pkg. For example, run `box get php@8.1` to install PHP 8.1, run `box get composer` to install the latest composer binary.
- `box build-prepare` Prepare the relevant environment for the `build` and `build-self` commands.
- `box build-self` Build the `box` binary itself.
- `box build <path>` Build a Hyperf application into a binary program.
- `box self-update` Update the `box` binary to the latest version.
- `box config list` Output all content of the box configuration file.
- `box config get <key>` Retrieve the value by key from the configuration file.
- `box config set <key> <value>` Set a value to the configuration file via key.
- `box config unset <key>` Delete the configuration value by key.
- `box config set-php-version <version>` Set the current PHP version for box. Available values: 8.0 | 8.1
- `box config get-php-version <version>` Get the currently set PHP version for box.
- `box reverse-proxy -u <upsteamHost:upstreamPort>` Start a reverse proxy HTTP server to forward HTTP requests to the specified multiple upstream servers.
- `box php <argument>` Run any PHP command through the current PHP version of box.
- `box composer <argument>` Run any Composer command through the current PHP version of box. The version of the composer binary depends on the last executed `get composer` command.
- `box php-cs-fixer <argument>` Run any `php-cs-fixer` command through the current PHP version of box. The version of the composer binary depends on the last executed `get php-cs-fixer` command.
- `box cs-fix <argument>` Run `php-cs-fixer fix` command through the current PHP version of box. The version of the composer binary depends on the last executed `get php-cs-fixer` command.
- `box phpstan <argument>` Run any `phpstan` command through the current PHP version of box. The version of the composer binary depends on the last executed `get phpstan` command. This command is only available in box v0.3.0 and higher.
- `box pint <argument>` Run any `pint` command through the current PHP version of box. The version of the composer binary depends on the last executed `get pint` command. This command is only available in box v0.3.0 and higher.
- `box version` Output the version number of the current `box` binary.

### About Swow-Skeleton

Friends who want to experience the full functionality of Box need to run it via the Swow Kernel, so you need to run your project based on [hyperf/swow-skeleton](https://github.com/hyperf/swow-skeleton). You can create a Hyperf 3.0-based Swow skeleton project via the `box composer create-project hyperf/swow-skeleton` command.
