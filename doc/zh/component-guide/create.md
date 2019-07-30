# 创建新的组件

`Hyperf` 官方提供了工具来快速创建组件包。

```
composer create-project hyperf/component-creater your_component dev-master
```

执行结果如下：

```
$ composer create-project hyperf/component-creater your_component dev-master
Installing hyperf/component-creater (dev-master 2a626139a08be9cc3b23e9f03592ccf1b7d3158a)
  - Installing hyperf/component-creater (dev-master 2a62613): Cloning 2a626139a0 from cache
Created project in your_component
> Installer\Script::install
Setting up optional packages
What is your component name (hyperf/demo): sample/component
What is your component license (MIT) : MIT
What is your component description : Sample Component
What is your namespace (Sample\Component): Sample\Component
Removing installer development dependencies

  Do you want to use hyperf/framework component ?
  [1] yes
  [n] None of the above
  Make your selection or type a composer package name and version (n): 1
  - Adding package hyperf/framework (1.0.*)

  Do you want to use hyperf/di component ?
  [1] yes
  [n] None of the above
  Make your selection or type a composer package name and version (n): 1
  - Adding package hyperf/di (1.0.*)
Remove installer
Adding .gitattributes
Removing Expressive installer classes, configuration, tests and docs
Loading composer repositories with package information
Updating dependencies (including require-dev)
Package operations: 85 installs, 0 updates, 0 removals
  - Installing ocramius/package-versions (1.4.0): Loading from cache
  - Installing swoft/swoole-ide-helper (dev-master 9de6d57): Cloning 9de6d57310 from cache
  - Installing doctrine/inflector (v1.3.0): Loading from cache
  - Installing psr/log (1.1.0): Loading from cache
  - Installing psr/event-dispatcher (1.0.0): Loading from cache
  - Installing psr/container (1.0.0): Loading from cache
  - Installing hyperf/contract (v1.0.6): Loading from cache
  - Installing hyperf/utils (v1.0.6): Loading from cache
  - Installing psr/http-message (1.0.1): Loading from cache
  - Installing fig/http-message-util (1.1.3): Loading from cache
  - Installing hyperf/framework (v1.0.6): Loading from cache
  - Installing psr/http-server-handler (1.0.1): Loading from cache
  - Installing psr/http-server-middleware (1.0.1): Loading from cache
  - Installing hyperf/dispatcher (v1.0.6): Loading from cache
  - Installing hyperf/di (v1.0.6): Loading from cache
  - Installing hyperf/event (v1.0.5): Loading from cache
  - Installing doctrine/instantiator (1.2.0): Loading from cache
  - Installing php-di/phpdoc-reader (2.1.0): Loading from cache
  - Installing symfony/finder (v4.3.3): Loading from cache
  - Installing doctrine/lexer (1.0.2): Loading from cache
  - Installing doctrine/annotations (v1.6.1): Loading from cache
  - Installing nikic/php-parser (v4.2.2): Loading from cache
  - Installing symfony/service-contracts (v1.1.5): Loading from cache
  - Installing symfony/polyfill-php73 (v1.11.0): Loading from cache
  - Installing symfony/polyfill-mbstring (v1.11.0): Loading from cache
  - Installing symfony/console (v4.3.3): Loading from cache
  - Installing symfony/stopwatch (v4.3.3): Loading from cache
  - Installing symfony/process (v4.3.3): Loading from cache
  - Installing symfony/polyfill-php72 (v1.11.0): Loading from cache
  - Installing paragonie/random_compat (v9.99.99): Loading from cache
  - Installing symfony/polyfill-php70 (v1.11.0): Loading from cache
  - Installing symfony/options-resolver (v4.3.3): Loading from cache
  - Installing symfony/polyfill-ctype (v1.11.0): Loading from cache
  - Installing symfony/filesystem (v4.3.3): Loading from cache
  - Installing symfony/event-dispatcher-contracts (v1.1.5): Loading from cache
  - Installing symfony/event-dispatcher (v4.3.3): Loading from cache
  - Installing php-cs-fixer/diff (v1.3.0): Loading from cache
  - Installing composer/xdebug-handler (1.3.3): Loading from cache
  - Installing composer/semver (1.5.0): Loading from cache
  - Installing friendsofphp/php-cs-fixer (v2.15.1): Loading from cache
  - Installing hyperf/server (v1.0.6): Loading from cache
  - Installing zendframework/zend-stdlib (3.2.1): Loading from cache
  - Installing zendframework/zend-mime (2.7.1): Loading from cache
  - Installing hyperf/http-message (v1.0.6): Loading from cache
  - Installing hyperf/exception-handler (v1.0.1): Loading from cache
  - Installing nikic/fast-route (v1.3.0): Loading from cache
  - Installing hyperf/http-server (v1.0.6): Loading from cache
  - Installing sebastian/version (2.0.1): Loading from cache
  - Installing sebastian/resource-operations (2.0.1): Loading from cache
  - Installing sebastian/recursion-context (3.0.0): Loading from cache
  - Installing sebastian/object-reflector (1.1.1): Loading from cache
  - Installing sebastian/object-enumerator (3.0.3): Loading from cache
  - Installing sebastian/global-state (2.0.0): Loading from cache
  - Installing sebastian/exporter (3.1.0): Loading from cache
  - Installing sebastian/environment (4.2.2): Loading from cache
  - Installing sebastian/diff (3.0.2): Loading from cache
  - Installing sebastian/comparator (3.0.2): Loading from cache
  - Installing phpunit/php-timer (2.1.2): Loading from cache
  - Installing phpunit/php-text-template (1.2.1): Loading from cache
  - Installing phpunit/php-file-iterator (2.0.2): Loading from cache
  - Installing theseer/tokenizer (1.1.3): Loading from cache
  - Installing sebastian/code-unit-reverse-lookup (1.0.1): Loading from cache
  - Installing phpunit/php-token-stream (3.1.0): Loading from cache
  - Installing phpunit/php-code-coverage (6.1.4): Loading from cache
  - Installing webmozart/assert (1.4.0): Loading from cache
  - Installing phpdocumentor/reflection-common (1.0.1): Loading from cache
  - Installing phpdocumentor/type-resolver (0.4.0): Loading from cache
  - Installing phpdocumentor/reflection-docblock (4.3.1): Loading from cache
  - Installing phpspec/prophecy (1.8.1): Loading from cache
  - Installing phar-io/version (2.0.1): Loading from cache
  - Installing phar-io/manifest (1.0.3): Loading from cache
  - Installing myclabs/deep-copy (1.9.1): Loading from cache
  - Installing phpunit/phpunit (7.5.14): Loading from cache
  - Installing hyperf/testing (v1.0.2): Loading from cache
  - Installing phpstan/phpdoc-parser (0.3.5): Loading from cache
  - Installing nette/utils (v3.0.1): Loading from cache
  - Installing nette/finder (v2.5.0): Loading from cache
  - Installing nette/robot-loader (v3.2.0): Loading from cache
  - Installing nette/schema (v1.0.0): Loading from cache
  - Installing nette/php-generator (v3.2.3): Loading from cache
  - Installing nette/neon (v3.0.0): Loading from cache
  - Installing nette/di (v3.0.0): Loading from cache
  - Installing nette/bootstrap (v3.0.0): Loading from cache
  - Installing jean85/pretty-package-versions (1.2): Loading from cache
  - Installing phpstan/phpstan (0.10.8): Loading from cache
hyperf/utils suggests installing symfony/var-dumper (Required to use the dd function (^4.1).)
hyperf/framework suggests installing hyperf/command (Required to use Command annotation.)
hyperf/di suggests installing hyperf/config (Require this component for annotation scan progress to retrieve the scan path.)
symfony/service-contracts suggests installing symfony/service-implementation
symfony/console suggests installing symfony/lock
paragonie/random_compat suggests installing ext-libsodium (Provides a modern crypto API that can be used to generate random bytes.)
symfony/event-dispatcher suggests installing symfony/dependency-injection
symfony/event-dispatcher suggests installing symfony/http-kernel
friendsofphp/php-cs-fixer suggests installing php-cs-fixer/phpunit-constraint-isidenticalstring (For IsIdenticalString constraint.)
friendsofphp/php-cs-fixer suggests installing php-cs-fixer/phpunit-constraint-xmlmatchesxsd (For XmlMatchesXsd constraint.)
zendframework/zend-mime suggests installing zendframework/zend-mail (Zend\Mail component)
sebastian/global-state suggests installing ext-uopz (*)
phpunit/php-code-coverage suggests installing ext-xdebug (^2.6.0)
phpunit/phpunit suggests installing phpunit/php-invoker (^2.0)
phpunit/phpunit suggests installing ext-xdebug (*)
nette/bootstrap suggests installing tracy/tracy (to use Configurator::enableTracy())
Writing lock file
Generating autoload files
ocramius/package-versions:  Generating version class...
ocramius/package-versions: ...done generating version class
Do you want to remove the existing VCS (.git, .svn..) history? [Y,n]? Y
```
