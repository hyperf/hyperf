# Guide Preface

In order to help developers better develop components for Hyperf and build an ecosystem together, we provide this guide to guide developers in component development. Before reading this guide, you need to **comprehend** the Hyperf documentation. Read, especially the [Coroutines](en/coroutine.md) and [Dependency Injection](en/di.md) chapters, a lack of a good understanding of the underlying components of Hyperf may lead to development errors.

# The purpose of component development

In the development under the traditional PHP-FPM architecture, usually when we need to use third-party libraries to solve our needs, we will directly introduce a corresponding `Library` through Composer, but under Hyperf, due to `persistence' The two characteristics of `Application` and `Coroutine` lead to some differences in the life cycle and mode of the application, so not all `Library` can be used directly in Hyperf. Of course, some well-designed ones `Library` can also be used directly. After reading this guide, you will know how to identify whether some `Library` can be used directly in the project, and how to change it if not.

# Component development preparations

The development preparation work referred to here, in addition to the basic operating conditions of Hyperf, focuses more on how to organize the structure of the code more conveniently to facilitate the development of components. Note that the following methods may not be able to jump due to *soft connections Problem* and does not apply to development environments under Windows for Docker.
In terms of code organization, we recommend Clone [hyperf-cloud/hyperf-skeleton](https://github.com/hyperf-cloud/hyperf-skeleton) project skeleton and [hyperf-cloud/hyperf]( https://github.com/hyperf-cloud/hyperf) project component library two projects. Do the following and have the following structure:

```bash
// Install the skeleton and configure it
composer create-project hyperf/hyperf-skeleton

// Clone the hyperf component library project, remember to replace hyperf-cloud with your Github ID, that is, clone the project you fork
git clone git@github.com:hyperf-cloud/hyperf.git
```

Has the following structure:

```
.
├── hyperf
│ ├── bin
│ └── src
└── hyperf-skeleton
    ├── app
    ├── bin
    ├── config
    ├── runtime
    ├── test
    └── vendor
```

The purpose of this is to make the `hyperf-skeleton` project available directly through the `path` source form, and let Composer directly pass the projects in the `hyperf` folder as dependencies to be loaded into the `vendor of the `hyperf-skelton` project ` directory, we add a `repositories` item to the `composer.json` file in `hyperf-skelton`, as follows:

```json
{
    "repositories": {
        "hyperf": {
            "type": "path",
            "url": "../hyperf/src/*"
        }
        "packagist": {
            "type": "composer",
            "url": "https://mirrors.aliyun.com/composer"
        }
    }
}
```
Then delete the `composer.lock` file and `vendor` folder in the `hyperf-skeleton` project, and then execute `composer update` to update the dependencies again, the command is as follows:

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```
   
Finally, the project folders in the `hyperf-skeleton/vendor/hyperf` folder are all connected to the `hyperf` folder through a `softlink`. We can use the `ls -l` command to verify whether the `softlink` has been established successfully:

```bash
cd vendor/hyperf/
ls -l
```

When we see a connection relationship like the following, it means that the `softlink` has been established successfully:

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

At this point, we can directly modify the files in `vendor/hyperf` in the IDE, but the code in `hyperf` is modified, so that in the end we can directly modify the `hyperf` project. `commit`, and then submit a `Pull Request(PR)` to the trunk.