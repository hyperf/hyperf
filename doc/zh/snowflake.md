# Snowflake

## 安装

```
composer require hyperf/snowflake
```

## 使用

框架提供了 `MetaGeneratorInterface` 和 `IdGeneratorInterface`，`MetaGeneratorInterface` 会生成 `ID` 的 `Meta` 文件，`IdGeneratorInterface` 则会根据对应的 `Meta` 文件生成 `分布式ID`。

框架默认使用的 `MetaGeneratorInterface` 是基于 `Redis` 实现的 `毫秒级别生成器`。配置如下：

```php
<?php

declare(strict_types=1);

use Hyperf\Snowflake\MetaGenerator\RedisMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;

return [
    'begin_second' => MetaGeneratorInterface::DEFAULT_BEGIN_SECOND,
    RedisMilliSecondMetaGenerator::class => [
        'pool' => 'default',
    ],
];

```

框架中使用 `Snowfalke` 十分简单，只需要从 `DI` 中取出 `IdGeneratorInterface` 对象即可。

```php
<?php
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(IdGeneratorInterface::class);

$id = $generator->generate();
```

当知道 `ID` 需要反推对应的 `Meta` 时，只需要调用 `degenerate` 即可。

```php
<?php
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(IdGeneratorInterface::class);

$meta = $generator->degenerate($id);
```

## 重写 `Meta` 生成器

`分布式ID` 的实现方式多种多样，虽然都是 `Snowflake` 算法，但也不尽相同。比如有人可能会根据 `UserId` 生成 `Meta`，而非 `WorkerId`。接下来，让我们实现一个简单的 `MetaGenerator`。
简单的来讲，`UserId` 绝对会超过 `10 bit`，所以默认的 `DataCenterId` 和 `WorkerId` 肯定是装不过来的，所以就需要对 `UserId` 取模。

```php
<?php

declare(strict_types=1);

use Hyperf\Snowflake\IdGenerator;

class UserDefinedIdGenerator
{
    /**
     * @var IdGenerator\SnowflakeIdGenerator
     */
    protected $idGenerator;

    public function __construct(IdGenerator\SnowflakeIdGenerator $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function generate(int $userId)
    {
        $meta = $this->idGenerator->getMetaGenerator()->generate();

        return $this->idGenerator->generate($meta->setWorkerId($userId % 31));
    }

    public function degenerate(int $id)
    {
        return $this->idGenerator->degenerate($id);
    }
}

use Hyperf\Utils\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(UserDefinedIdGenerator::class);
$userId = 20190620;

$id = $generator->generate($userId);

```