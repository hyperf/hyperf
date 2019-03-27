<?php

return [
    'default' => [
        'driver' => Hyperf\Cache\Driver\RedisDriver::class,
        'packer' => Hyperf\Cache\Packer\PhpSerializer::class,
        'prefix' => 'c:',
    ],
];
