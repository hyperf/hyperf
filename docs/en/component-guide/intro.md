# Guide foreword

In order to help developers better develop components for Hyperf and build an ecosystem together, we have provided this guide for developers. Before reading this guide, you need to have a thorough understanding of the Hyperf documentation. The [Coroutine](en/coroutine.md) and [Dependency Injection](en/di.md) chapters are especially important and  if you lack a sufficient understanding of the basic components of Hyperf, it may cause errors during development.

# Purpose of component development

When developing an application using the PHP-FPM architecture it is usually simple to introduce 3rd party composer packages directly into the project. In the case of Hyperf however, including packages is not always so straightforward because the developer has to consider the lifecycle of persistent objects as well as the usage of coroutines.  Therefore, not all libraries can be used directly in Hyperf unless they're well designed. Reading through this guide, you will know how to identify whether a `Library` can be used directly in the project and how to implement changes for incompatible packages.

# Component development preparation

Besides just introducing the basics of how Hyperf operates, this guide is ment to provide standardized ways of structuring your codebase so as to facilitate development. `[PLACEHOLDER]` This guide does not apply to the development environment under Windows for Docker. In terms of code organization, we recommend cloning [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) project skeleton and [hyperf/hyperf](https://github.com/hyperf/hyperf) in the same directory. Run the following commands to initialize the project:

```bash
// Install skeleton and complete configuration
composer create-project hyperf/hyperf-skeleton

// Clone the hyperf component library project, remember to replace hyperf with your fork
git clone git@github.com:hyperf/hyperf.git
```

You will end up with the following structure:

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

The reason for using this sort of structure is to enable the `hyperf-skeleton` project's composer to directly source Hyperf from a directory path. To do this, you must define the `repositories` object in the `composer.json` file in `hyperf-skeleton`:

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
Then delete the `composer.lock` file and `vendor` folder in the `hyperf-skeleton` project before executing `composer update` to update the dependencies:

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```

Finally, all the project folders in the `hyperf-skeleton/vendor/hyperf` folder are connected to the `hyperf` folder through a `softlink`. We can use the `ls -l` command to verify whether the `softlink` has been established successfully:

```bash
cd vendor/hyperf/
ls -l
```

When you see a connection relationship similar to the following, it means that the `softlink` has been established successfully:

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

This allows us to directly modify the files in `vendor/hyperf` while applying the changes a to the forked Hyperf repository. These changes can then be propagetd into the main Hyperf project repository via a `Pull Request (PR)`.
