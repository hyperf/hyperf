# Guide to foreword

In order to help developers better develop components for Hyperf and build an ecosystem, we have provided this guide to guide developers in component development. Before reading this guide, you need to have a comprehensive version of Hyperf's documentation. Reading, especially in the [Coroutine](en/coroutine.md) and [Dependency Injection](en/di.md) chapters, if you do not fully understand the basic components of Hyperf, it may lead to errors in development.

# Purpose of component development

In the traditional PHP-FPM architecture, usually when we need to use third-party libraries to solve our needs, we will introduce a corresponding `Library` directly through Composer, but under Hyperf, due to `lasting The application` and `the coroutine feature` result in some differences in the application life cycle and mode, so not all Library can be used directly in Hyperf. Of course, some excellent designs `Library` can also be used directly. After reading this guide, you will know how to identify whether the `Library` can be used directly in the project. If not, how to make changes.

# Component development preparation

The development preparation work referred to here, in addition to the basic operating conditions of Hyperf, here is more about how to organize the structure of the code more conveniently to facilitate the development of the component, note that the following methods may not be able to jump due to *soft connection Question* does not apply to development environments under Windows for Docker.  
In the code organization, we recommend Clone [hyperf-cloud/hyperf-skeleton](https://github.com/hyperf-cloud/hyperf-skeleton) project skeleton and [hyperf-cloud/hyperf] in the same directory. Https://github.com/hyperf-cloud/hyperf) Project component library two projects. Do the following and have the following structure:

```bash
// Install skeleton and configure it
composer create-project hyperf/hyperf-skeleton 

// To clone the hyperf component library project, remember to replace hyperf-cloud for your Github ID, which is the project that clones your Fork.
git clone git@github.com:hyperf-cloud/hyperf.git
```

The following structure:

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

The purpose of this is to allow the `hyperf-skeleton` project to be directly loaded by the `path` source, allowing Composer to be loaded into the `hyperf-skelton` project by the project in the `hyperf` folder as a dependency. In the ` directory, we add a `repositories` to the `composer.json` file in `hyperf-skelton` as follows:

```json
{
    "repositories": {
        "hyperf": {
            "type": "path",
            "url": "../hyperf/src/*"
        },
        "packagist": {
            "type": "composer",
            "url": "https://mirrors.aliyun.com/composer"
        }
    }
}
```
Then delete the `composer.lock` file and the `vendor` folder in the `hyperf-skeleton` project, and then execute `composer update` to let the dependency be updated again. The command is as follows:

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```

Eventually the project folders in the `hyperf-skeleton/vendor/hyperf` folder are all connected to the `hyperf` folder via `softlink`. We can verify that the `softlink` has been successfully established by the `ls -l` command:

```bash
cd vendor/hyperf/
ls -l
```

When we see a connection like this, it means that the `softlink` was successfully created:

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

At this point, we can directly modify the files in `vendor/hyperf` in the IDE, but the purpose of the code in `hyperf` is modified, so that we can directly work inside the `hyperf` project. `commit`, then submit the `Pull Request(PR)` to the trunk.
