# Introduction to the Guide

To help developers better create components for Hyperf and build the ecosystem together, we have provided this guide to instruct developers on component development. Before reading this guide, you should have **comprehensively** read the Hyperf documentation, especially the [Coroutine](../coroutine.md) and [Dependency Injection](../di.md) chapters. A lack of sufficient understanding of Hyperf's basic components may lead to errors during development.

# Purpose of Component Development

In traditional PHP-FPM architecture development, when we need to use third-party libraries to solve our needs, we usually introduce a corresponding `Library` via Composer. However, in Hyperf, due to the two characteristics of `persistent application` and `coroutine`, there are some differences in the application lifecycle and mode. Therefore, not all `Libraries` can be used directly in Hyperf, although some well-designed `Libraries` can indeed be used directly. By reading this guide thoroughly, you will know how to discern whether a `Library` can be used directly in a project and, if not, what modifications should be made.

# Preparation for Component Development

The development preparation work referred to here, in addition to the basic running conditions of Hyperf, focuses more on how to organize the code structure more conveniently to facilitate component development. Note that the following method may not be suitable for development environments under Windows for Docker due to *issues with soft link traversal*.

Regarding code organization, we suggest cloning the [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) project skeleton and the [hyperf/hyperf](https://github.com/hyperf/hyperf) project component library in the same directory. Perform the following operations to achieve the structure below:

```bash
// Install skeleton and complete configuration
composer create-project hyperf/hyperf-skeleton 

// Clone the hyperf component library project, remember to replace hyperf with your Github ID, i.e., clone the project you forked
git clone git@github.com:hyperf/hyperf.git
```

Achieve the following structure:

```
.
├── hyperf
│   ├── bin
│   └── src
└── hyperf-skeleton
    ├── app
    ├── bin
    ├── config
    ├── runtime
    ├── test
    └── vendor
```

The purpose of doing this is to allow the `hyperf-skeleton` project to directly load the projects within the `hyperf` folder as dependencies into the `vendor` directory of the `hyperf-skeleton` project through the `path` source form. We add a `repositories` item to the `composer.json` file in `hyperf-skeleton`, as follows:

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

Then, delete the `composer.lock` file and `vendor` folder in the `hyperf-skeleton` project, and execute `composer update` to update the dependencies again. The commands are as follows:

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```
   
Ultimately, this ensures that all project folders within `hyperf-skeleton/vendor/hyperf` are connected to the `hyperf` folder through `soft links`. We can verify if the `soft links` have been successfully created using the `ls -l` command:

```bash
cd vendor/hyperf/
ls -l
```

When we see connection relationships similar to those below, it indicates that the `soft links` have been successfully established:

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
support -> ../../../hyperf/src/support
```

At this point, we can achieve the goal of directly modifying the files in `vendor/hyperf` within the IDE, while actually modifying the code in `hyperf`. In this way, we can directly `commit` in the `hyperf` project and then submit a `Pull Request(PR)` to the main branch.
