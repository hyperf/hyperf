# Session management

HTTP is a stateless protocol, meaning that the server does not retain any state during transactions with clients. However, when developing web applications there's often a need to share information between multiple requests, which is usually done via session storage. You can implement the session functionality with [hyperf/session](https://github.com/hyperf/session). The session component currently only implements two storage drivers, namely `file` and `Redis`. The default is `file` driver. In a production environment, we strongly recommend that you use `Redis` as it has much better performance compared to the `file` alternative and is also better suited for cluster architecture.

# Installation

```bash
composer require hyperf/session
```

# Configuration

The configuration of the session component is stored in the `config/autoload/session.php` file. If the file does not exist, you can use the `php bin/hyperf.php vendor:publish hyperf/session` command to publish the configuration file of the session component.

## Configure session middleware

Before using session, you need to configure the `Hyperf\Session\Middleware\SessionMiddleware` middleware as the global middleware of the HTTP Server so that the component can intercept the request for processing. You can define middlewares in `config/autoload/middlewares.php` configuration file. Example configuration:

```php
<?php

return [
    // Here http corresponds to the default server name. If you need to use session on other servers, you need to configure the corresponding global middleware
    'http' => [
        \Hyperf\Session\Middleware\SessionMiddleware::class,
    ],
];
```

## Configure storage driver

Modify different session storage drivers by changing the `handler` configuration in the configuration file, and the specific configuration items of the corresponding handler are determined by the different configuration items in the `options`.

### Use file storage driver

> The file storage driver is the default storage driver, but it is recommended to use the Redis driver in a production environment

When the value of `handler` is `Hyperf\Session\Handler\FileHandler`, it indicates that the `file` storage driver is used and all session data files will be generated and stored in the folder corresponding to the `options.path` configuration value. The default configuration folder is in the `runtime/session` folder under the root directory.

### Use Redis driver

Before using the `Redis` storage driver, you need to install the [hyperf/redis](https://github.com/hyperf/redis) component. To use this storage driver set the value of `handler` to `Hyperf\Session\Handler\RedisHandler`. You can adjust the `Redis` connection used by the driver by configuring the `options.connection` configuration value. The connections are defined in `config/autoload/redis.php` of the [hyperf/redis](https://github.com/hyperf/redis) component.

# Use

## Get session object

The session object can be accessed by injecting `Hyperf\Contract\SessionInterface`:

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

## Store data

When you want to store data in the session, you can do so by calling the `set(string $name, $value): void` method:

```php
<?php

$this->session->set('foo','bar');
```

## Retrieve data

When you want to get data from the session, you can do so by calling the `get(string $name, $default = null)` method:

```php
<?php

$this->session->get('foo', $default = null);
```

### Get all data

You can get all the stored data from the session at once by calling the `all(): array` method:

```php
<?php

$data = $this->session->all();
```

## Determine whether there is a value in the session

To determine whether a value exists in the session, you can use the `has(string $name): bool` method. If the value exists and is not null, the `has` method will return `true`:

```php
<?php

if ($this->session->has('foo')) {
    //
}
```

## Get and delete a piece of data

By calling the `remove(string $name)` method, you can retrieve and delete a piece of data from the session using only one method:

```php
<?php

$data = $this->session->remove('foo');
```

## Delete one or more pieces of data

By calling the `forget(string|array $name): void` method, one or more pieces of data can be deleted from the session using only one method. When a string is passed, it means that only one piece of data is deleted. When a key string array is passed, it means to delete multiple pieces of data:

```php
<?php

$this->session->forget('foo');
$this->session->forget(['foo','bar']);
```

## Clear the current session data

You can clear  all the data in the current session by calling the `clear(): void` method:

```php
<?php

$this->session->clear();
```

## Get the current session ID

When you want to get the current session ID to handle some logic by yourself, you can get the current session ID by calling the `getId(): string` method:

```php
<?php

$sessionId = $this->session->getId();
```
