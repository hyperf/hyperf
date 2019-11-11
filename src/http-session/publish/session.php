<?php
declare(strict_types=1);

use Hyperf\HttpSession\Handler\RedisHandler;

return [
    'prefix' => 'SESSION_ID:',
    'cookiePath' => '/',
    'cookieExpires' => 0,
    'cookieDomain' => '',
    'cookieSecure' => false,
    'cookieHttpOnly' => true,
    'maxLifetime' => 7200,
    'handler' => [
        'class' => RedisHandler::class,
        'poolName' => 'default'
    ]
];
