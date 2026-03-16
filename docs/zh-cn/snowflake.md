# Snowflake

## 算法介绍

`Snowflake` 是由 Twitter 提出的一个分布式全局唯一 ID 生成算法，算法生成 `ID` 的结果是一个 `64bit` 大小的长整，标准算法下它的结构如下图：

![snowflake](imgs/snowflake.jpeg)

- `1 位`，不用。
  - 二进制中最高位为符号位，我们生成的 `ID` 一般都是正整数，所以这个最高位固定是 0。
  
- `41 位`，用来记录时间戳（毫秒）。
  - `41 位` 可以表示 `2^41 - 1` 个数字。
  - 也就是说 `41 位` 可以表示 `2^41 - 1` 个毫秒的值，转化成单位年则是 `(2^41 - 1) / (1000 * 60 * 60 * 24 * 365)` 约为 `69` 年。
  
- `10 位`，用来记录工作机器 `ID`。
  - 可以部署在 `2^10` 共 `1024` 个节点，包括 `5` 位 `DatacenterId` 和 `5` 位 `WorkerId`。
  
- `12 位`，序列号，用来记录同毫秒内产生的不同 `id`。
  - `12 位` 可以表示的最大正整数是 `2^12 - 1` 共 `4095` 个数字，来表示同一机器同一时间截（毫秒)内产生的 `4095` 个 `ID` 序号。

`Snowflake` 可以保证：

 - 所有生成的 `ID` 按时间趋势递增。
 - 整个分布式系统内不会产生重复 `ID`（因为有 `DatacenterId (5 bits)` 和 `WorkerId (5 bits)` 来做区分）。
 
Hyperf 的 [hyperf/snowflake](https://github.com/hyperf/snowflake) 组件在设计上提供了很好的可扩展性，允许您通过简单的扩展就能实现其它基于 Snowflake 的变体算法。

## 安装

```
composer require hyperf/snowflake
```

## 使用

框架提供了 `MetaGeneratorInterface` 和 `IdGeneratorInterface`，`MetaGeneratorInterface` 会生成 `ID` 的 `Meta` 文件，`IdGeneratorInterface` 则会根据对应的 `Meta` 文件生成 `分布式 ID`。

框架默认使用的 `MetaGeneratorInterface` 是基于 `Redis` 实现的 `毫秒级别生成器`。    
配置文件位于 `config/autoload/snowflake.php`，如配置文件不存在可通过执行 `php bin/hyperf.php vendor:publish hyperf/snowflake` 命令创建默认配置，配置文件内容如下：

```php
<?php

declare(strict_types=1);

use Hyperf\Snowflake\MetaGenerator\RedisMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGenerator\RedisSecondMetaGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;

return [
    'begin_second' => MetaGeneratorInterface::DEFAULT_BEGIN_SECOND,
    RedisMilliSecondMetaGenerator::class => [
        // Redis Pool
        'pool' => 'default',
        // 用于计算 WorkerId 的 Key 键
        'key' => RedisMilliSecondMetaGenerator::DEFAULT_REDIS_KEY
    ],
    RedisSecondMetaGenerator::class => [
        // Redis Pool
        'pool' => 'default',
        // 用于计算 WorkerId 的 Key 键
        'key' => RedisMilliSecondMetaGenerator::DEFAULT_REDIS_KEY
    ],
];

```

框架中使用 `Snowflake` 十分简单，只需要从 `DI` 中取出 `IdGeneratorInterface` 对象即可。

```php
<?php
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(IdGeneratorInterface::class);

$id = $generator->generate();
```

当知道 `ID` 需要反推对应的 `Meta` 时，只需要调用 `degenerate` 即可。

```php
<?php
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(IdGeneratorInterface::class);

$meta = $generator->degenerate($id);
```

## 重写 `Meta` 生成器

`分布式全局唯一 ID` 的实现方式多种多样，也有很多基于 `Snowflake` 算法的变体算法，虽然都是 `Snowflake` 算法，但也不尽相同。比如有人可能会根据 `UserId` 生成 `Meta`，而非 `WorkerId`。接下来，让我们实现一个简单的 `MetaGenerator`。
简单的来讲，`UserId` 绝对会超过 `10 bit`，所以默认的 `DataCenterId` 和 `WorkerId` 肯定是装不过来的，所以就需要对 `UserId` 取模。

```php
<?php

declare(strict_types=1);

use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;

class UserDefinedIdGenerator
{
    protected SnowflakeIdGenerator $idGenerator;

    public function __construct(SnowflakeIdGenerator $idGenerator)
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

use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(UserDefinedIdGenerator::class);
$userId = 20190620;

$id = $generator->generate($userId);

```

## 在数据库模型中应用

配置好 Snowflake 以后，我们可以让数据库模型直接使用雪花 id 作为主键。

```php
<?php
use Hyperf\Database\Model\Model;
use Hyperf\Snowflake\Concern\Snowflake;

class User extends Model {
    use Snowflake;
}
```

上述 User 模型在创建时便会默认使用 Snowflake 算法生成主键。

因为 Snowflake 中会复写 `creating` 方法，而用户有需要自己设置 `creating` 方法时，就会出现无法生成 `ID` 的问题。这里需要用户按照以下方式自行处理即可

```php
<?php
use Hyperf\Database\Model\Model;
use Hyperf\Snowflake\Concern\Snowflake;

class User extends Model {
    use Snowflake {
        creating as create;
    }

    public function creating()
    {
        $this->create();
        // Do something ...
    }
}
```
