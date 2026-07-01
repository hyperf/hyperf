# Session Management

HTTP is a stateless protocol, meaning that the server does not retain any state during transactions with the client. Therefore, when developing HTTP Server applications, we usually use Session to achieve the sharing of user data between multiple requests. You can implement Session functionality through [hyperf/session](https://github.com/hyperf/session). The Session component currently only adapts to two storage drivers: `File` and `Redis`. The default is the `File` driver. In a production environment, we strongly recommend that you use `Redis` as the storage driver, as it performs better and is more suitable for use under cluster architectures.

# Installation

```bash
composer require hyperf/session
```

# Configuration

The configuration of the Session component is stored in the `config/autoload/session.php` file. If the file does not exist, you can publish the Session component's configuration file to the Skeleton via the `php bin/hyperf.php vendor:publish hyperf/session` command.

## Configuring Session Middleware

Before using Session, you need to configure the `Hyperf\Session\Middleware\SessionMiddleware` middleware as a global middleware for the HTTP Server, so that the component can intervene in the request process and perform corresponding processing. The `config/autoload/middlewares.php` configuration file example is as follows:

```php
<?php

return [
    // 'http' here corresponds to the default server name. If you need to use Session on other servers, you need corresponding global middleware configuration
    'http' => [
        \Hyperf\Session\Middleware\SessionMiddleware::class,
    ],
];
```

## Configuring Storage Driver

Change the `handler` configuration in the configuration file to modify different Session storage drivers, and the specific configuration items of the corresponding Handler are determined by different configuration items in `options`.

### Using File Storage Driver

> The file storage driver is the default storage driver, but it is recommended to use the Redis driver in a production environment.

When the value of `handler` is `Hyperf\Session\Handler\FileHandler`, it means the `File` storage driver is used. All Session data files will be generated and stored in the folder corresponding to the `options.path` configuration value. The default configured folder is within the `runtime/session` folder under the root directory.

### Using Redis Driver

Before using the `Redis` storage driver, you need to install the [hyperf/redis](https://github.com/hyperf/redis) component. When the value of `handler` is `Hyperf\Session\Handler\RedisHandler`, it means the `Redis` storage driver is used. You can adjust the `Redis` connection to be used by the driver by configuring the `options.connection` configuration value. This connection matches the key naming in the `config/autoload/redis.php` configuration of the [hyperf/redis](https://github.com/hyperf/redis) component.

# Usage

## Obtaining Session Object

You can obtain the Session object by injecting `Hyperf\Contract\SessionInterface`, and then call the methods defined by the interface to use it:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\SessionInterface;

class IndexController
{
    #[Inject]
    private SessionInterface $session;

    public function index()
    {
        // Use directly via $this->session
    } 
}
```

## Storing Data

When you wish to store data in the Session, you can implement it by calling the `set(string $name, $value): void` method:

```php
<?php

$this->session->set('foo', 'bar');
```

## Obtaining Data

When you wish to obtain data from the Session, you can implement it by calling the `get(string $name, $default = null)` method:

```php
<?php

$this->session->get('foo', $default = null);
```

### Obtaining All Data

You can obtain all stored data from the Session at once by calling the `all(): array` method:

```php
<?php

$data = $this->session->all();
```

## Determining Whether a Value Exists in Session

To determine whether a value exists in the Session, you can use the `has(string $name): bool` method. If the value exists and is not null, the `has` method returns `true`:

```php
<?php

if ($this->session->has('foo')) {
    //
}
```

## Obtaining and Deleting a Piece of Data

By calling the `remove(string $name)` method, you can obtain and delete a piece of data from the Session using only one method:

```php
<?php

$data = $this->session->remove('foo');
```

## Deleting One or More Pieces of Data

By calling the `forget(string|array $name): void` method, you can delete one or more pieces of data from the Session using only one method. When a string is passed, it means only one piece of data is deleted. When an array of key strings is passed, it means multiple pieces of data are deleted:

```php
<?php

$this->session->forget('foo');
$this->session->forget(['foo', 'bar']);
```

## Clearing Current Session Data

When you wish to clear all data in the current Session, you can implement it by calling the `clear(): void` method:

```php
<?php

$this->session->clear();
```

## Obtaining Current Session ID

When you wish to obtain the current Session ID to handle some logic yourself, you can obtain the current Session ID by calling the `getId(): string` method:

```php
<?php

$sessionId = $this->session->getId();
```
