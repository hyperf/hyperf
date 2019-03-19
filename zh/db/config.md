# 配置

默认配置如下，数据库支持多库配置，默认为`default`。

|        配置项        |     默认值      |        备注        |
|:--------------------:|:---------------:|:------------------:|
|        driver        |       无        |     数据库引擎     |
|         host         |       无        |     数据库地址     |
|       database       |       无        |    数据库默认DB    |
|       username       |       无        |    数据库用户名    |
|       password       |      null       |     数据库密码     |
|       charset        |      utf8       |     数据库编码     |
|      collation       | utf8_unicode_ci |     数据库编码     |
|        prefix        |    空字符串     |   数据库模型前缀   |
| pool.min_connections |        1        | 连接池内最少连接数 |
| pool.max_connections |       10        | 连接池内最大连接数 |
| pool.connect_timeout |      10.0       |  连接等待超时时间  |
|  pool.wait_timeout   |       3.0       |      超时时间      |
|    pool.heartbeat    |       -1        |        心跳        |
|  pool.max_idle_time  |      60.0       |    最大闲置时间    |

~~~php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8'),
        'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('DB_MAX_IDLE_TIME', 60),
        ]
    ],
];
~~~