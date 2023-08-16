# Preface to the guide

In order to help developers better develop components for Hyperf and build an ecosystem together, we provide this guide to guide developers in component development. Before reading this guide, you need to have a **comprehensive** review of the Hyperf documentation Read, especially the [coroutine](en/coroutine.md) and [Dependency Injection](en/di.md) chapters, if you lack a sufficient understanding of the basic components of Hyperf, it may cause problems during development mistake.

# The purpose of component development

In the development under the traditional PHP-FPM architecture, usually when we need to use a third-party library to solve our needs, we will directly introduce a corresponding library through Composer. However, under Hyperf, due to the persistent The two characteristics of application` and `coroutine` lead to some differences in the life cycle and mode of the application, so not all `Library` can be used directly in Hyperf, of course, some well-designed `Library` can also be used directly. After reading this guide, you will know how to identify whether some `Library` can be used directly in the project, and how to make changes if not.

# Component development preparations

The development preparation referred to here, in addition to the basic operating conditions of Hyperf, focuses more on how to organize the structure of the code more conveniently to facilitate the development of components. Note that the following methods may not be able to jump due to the *soft link Issue* and does not apply to the development environment under Windows for Docker.
In terms of code organization, we recommend Clone [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) project skeleton and [hyperf/hyperf](https://github. com/hyperf/hyperf) project component library two projects. Do the following and have the following structure:

```bash
// Install the skeleton and configure it
composer create-project hyperf/hyperf-skeleton

// Clone the hyperf component library project, remember to replace hyperf with your Github ID, that is, clone the project you forked
git clone git@github.com:hyperf/hyperf.git
```

It has the following structure:

```
.
├── hyperf
│ ├── bin
│ └── src
└── hyperf-skeleton
     ├── app
     ├── bin
     ├──config
     ├── runtime
     ├── test
     └── vendor
```

The purpose of this is to allow the `hyperf-skeleton` project to be directly sourced through the `path` form, so that Composer can be directly loaded into the `vendor` of the `hyperf-skelton` project through the project in the `hyperf` folder as a dependency ` directory, we add a `repositories` item to the `composer.json` file in `hyperf-skelton`, as follows:

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
Then delete the `composer.lock` file and the `vendor` folder in the `hyperf-skeleton` project, and then execute `composer update` to update the dependencies again. The command is as follows:

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```

Finally, all the project folders in the `hyperf-skeleton/vendor/hyperf` folder are connected to the `hyperf` folder through `softlinks`. We can use the `ls -l` command to verify whether `softlink (softlink)` has been successfully established:

```bash
cd vendor/hyperf/
ls -l
```

When we see a connection relationship like the following, it means that the `soft link (softlink)` has been established successfully:

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

At this point, we can directly modify the files in `vendor/hyperf` in the IDE, but what we modify is the code in `hyperf`, so that we can directly modify the `hyperf` project in the end. `commit`, and then submit a `Pull Request (PR)` to the trunk.