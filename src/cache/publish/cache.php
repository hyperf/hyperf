<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => [
        'driver' => Hyperf\Cache\Driver\RedisDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
        'prefix' => 'c:',
        'skip_cache_results' => [],
    ],
    // 'sqlite' => [
    //     'driver' => Hyperf\Cache\Driver\SqliteDriver::class,
    //     'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
    //     'prefix' => 'c:',
    //     'database' => ':memory:',
    //     'table' => 'hyperf_cache',
    //     'options' => [
    //         PDO::ATTR_CASE => PDO::CASE_NATURAL,
    //         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //         PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
    //         PDO::ATTR_STRINGIFY_FETCHES => false,
    //         PDO::ATTR_EMULATE_PREPARES => false,
    //     ],
    //     'max_connections' => 10,
    // ],
];
