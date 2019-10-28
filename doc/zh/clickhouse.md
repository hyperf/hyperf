# Clickhouse

ClickHouse is an open source column-oriented database management system capable of real time generation of analytical data reports using SQL queries.

## 安装

```
composer require hyperf/clickhouse
```

## 配置文件

```
php bin/hyperf.php vendor:publish hyperf/clickhouse
```

|   配置   |  类型  |    默认值     | 备注 |
|:--------:|:------:|:-------------:|:----:|
|   host   | string | `'localhost'` |  无  |
|   port   | string |   `'8123'`    |      |
| username | string |  `'default'`  |      |
| password | string |     `''`      |      |
| setting  | object |     `{}`      |      |

## 快速开始

创建 Client

```php
<?php

use Hyperf\Clickhouse\ClickhouseFactory;
use Hyperf\Utils\ApplicationContext;

$factory = ApplicationContext::getContainer()->get(ClickhouseFactory::class);

$db = $factory->create();
```

选择数据库

```php
$db->database('default');
```

查看所有表

```php
$db->showTables();
```

创建表

```php
$db->write('
    CREATE TABLE IF NOT EXISTS summing_url_views (
        event_date Date DEFAULT toDate(event_time),
        event_time DateTime,
        site_id Int32,
        site_key String,
        views Int32,
        v_00 Int32,
        v_55 Int32
    )
    ENGINE = SummingMergeTree(event_date, (site_id, site_key, event_time, event_date), 8192)
');
```

查看表

```php
$db->showCreateTable('summing_url_views');
```

插入数据

```php
$stat = $db->insert('summing_url_views',
    [
        [time(), 'HASH1', 2345, 22, 20, 2],
        [time(), 'HASH2', 2345, 12, 9,  3],
        [time(), 'HASH3', 5345, 33, 33, 0],
        [time(), 'HASH3', 5345, 55, 0, 55],
    ],
    ['event_time', 'site_key', 'site_id', 'views', 'v_00', 'v_55']
);
```

如果需要插入 `UInt64` 的数据，可以使用 `ClickHouseDB\Type\UInt64`

```php
use ClickHouseDB\Type\UInt64;

$statement = $db->insert('table_name',
    [
        [time(), UInt64::fromString('18446744073709551615')],
    ],
    ['event_time', 'uint64_type_column']
);

UInt64::fromString('18446744073709551615');
```

查询

```php
$statement = $db->select('SELECT * FROM summing_url_views LIMIT 2');

// Count select rows
$statement->count();

// Count all rows
$statement->countAll();

// fetch one row
$statement->fetchOne();

// get extremes min
print_r($statement->extremesMin());

// totals row
print_r($statement->totals());

// result all
print_r($statement->rows());

// totalTimeRequest
print_r($statement->totalTimeRequest());

// raw answer JsonDecode array, for economy memory
print_r($statement->rawData());

// raw curl_info answer
print_r($statement->responseInfo());

// human size info
print_r($statement->info());

// if clickhouse-server version >= 54011
$db->settings()->set('output_format_write_statistics',true);
print_r($statement->statistics());

$statement = $db->select('
    SELECT event_date, site_key, sum(views), avg(views)
    FROM summing_url_views
    WHERE site_id < 3333
    GROUP BY event_date, url_hash
    WITH TOTALS
');

print_r($statement->rowsAsTree('event_date.site_key'));

/*
(
    [2016-07-18] => Array
        (
            [HASH2] => Array
                (
                    [event_date] => 2016-07-18
                    [url_hash] => HASH2
                    [sum(views)] => 12
                    [avg(views)] => 12
                )
            [HASH1] => Array
                (
                    [event_date] => 2016-07-18
                    [url_hash] => HASH1
                    [sum(views)] => 22
                    [avg(views)] => 22
                )
        )
)
*/
```

删除表

```php
$db->write('DROP TABLE IF EXISTS summing_url_views');
```


