# 指南前言

为了帮助开发者更好的为 Hyperf 开发组件，共建生态，我们提供了本指南用于指导开发者进行组件开发，在阅读本指南前，需要您对 Hyperf 的文档进行了 **全面** 的阅读，特别是 [协程](zh-cn/coroutine.md) 和 [依赖注入](zh-cn/di.md) 章节，如果对 Hyperf 的基础组件缺少充分的理解，可能会导致开发时出现错误。

# 组件开发的目的

在传统的 PHP-FPM 架构下的开发，通常在我们需要借助第三方库来解决我们的需求时，都会通过 Composer 来直接引入一个对应的 `库(Library)`，但是在 Hyperf 下，由于 `持久化应用` 和 `协程` 这两个特性，导致了应用的生命周期和模式存在一些差异，所以并不是所有的 `库(Library)` 都能在 Hyperf 里直接使用，当然，一些设计优秀的 `库(Library)` 也是可以被直接使用的。通读本指南，便可知道如何甄别一些 `库(Library)` 是否能直接用于项目内，不能的话该进行如何的改动。

# 组件开发准备工作

这里所指的开发准备工作，除了 Hyperf 的基础运行条件外，这里关注的更多是如何更加便捷的组织代码的结构以便于组件的开发工作，注意以下方式可能会由于 *软连接无法跳转的问题* 而并不适用于 Windows for Docker 下的开发环境。   
在代码组织上，我们建议在同一个目录下 Clone [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 项目骨架和 [hyperf/hyperf](https://github.com/hyperf/hyperf) 项目组件库两个项目。进行下面的操作并呈以下结构：

```bash
// 安装 skeleton，并配置完成
composer create-project hyperf/hyperf-skeleton 

// 克隆 hyperf 组件库项目，这里记得要替换 hyperf 为您的 Github ID，也就是克隆您所 Fork 的项目
git clone git@github.com:hyperf/hyperf.git
```

呈以下结构：

```
.
├── hyperf
│   ├── bin
│   └── src
└── hyperf-skeleton
    ├── app
    ├── bin
    ├── config
    ├── runtime
    ├── test
    └── vendor
```

这样做的目的是为了让 `hyperf-skeleton` 项目可以直接通过 `path` 来源的形式，让 Composer 直接通过 `hyperf` 文件夹内的项目作为依赖项被加载到 `hyperf-skelton`  项目的 `vendor` 目录中，我们对 `hyperf-skelton` 内的 `composer.json` 文件增加一个 `repositories` 项，如下：

```json
{
    "repositories": {
        "hyperf": {
            "type": "path",
            "url": "../hyperf/src/*"
        }
    }
}
```
然后再在 `hyperf-skeleton` 项目内删除 `composer.lock` 文件和 `vendor` 文件夹，再执行 `composer update` 让依赖重新更新，命令如下：

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```
   
最终使 `hyperf-skeleton/vendor/hyperf` 文件夹内的项目文件夹全部通过 `软连接(softlink)` 连接到 `hyperf` 文件夹内。我们可以通过 `ls -l` 命令来验证 `软连接(softlink)` 是否已经建立成功：

```bash
cd vendor/hyperf/
ls -l
```

当我们看到类似下面这样的连接关系，即表明 `软连接(softlink)` 建立成功了：

```
cache -> ../../../hyperf/src/cache
command -> ../../../hyperf/src/command
config -> ../../../hyperf/src/config
contract -> ../../../hyperf/src/contract
database -> ../../../hyperf/src/database
db-connection -> ../../../hyperf/src/db-connection
devtool -> ../../../hyperf/src/devtool
di -> ../../../hyperf/src/di
dispatcher -> ../../../hyperf/src/dispatcher
event -> ../../../hyperf/src/event
exception-handler -> ../../../hyperf/src/exception-handler
framework -> ../../../hyperf/src/framework
guzzle -> ../../../hyperf/src/guzzle
http-message -> ../../../hyperf/src/http-message
http-server -> ../../../hyperf/src/http-server
logger -> ../../../hyperf/src/logger
memory -> ../../../hyperf/src/memory
paginator -> ../../../hyperf/src/paginator
pool -> ../../../hyperf/src/pool
process -> ../../../hyperf/src/process
redis -> ../../../hyperf/src/redis
server -> ../../../hyperf/src/server
testing -> ../../../hyperf/src/testing
utils -> ../../../hyperf/src/utils
```

此时，我们便可达到在 IDE 内直接对 `vendor/hyperf` 内的文件进行修改，而修改的却是 `hyperf` 内的代码的目的，这样最终我们便可直接对 `hyperf` 项目内进行 `commit`，然后向主干提交 `Pull Request(PR)` 了。
