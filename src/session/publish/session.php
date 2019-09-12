<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Date: 2019/9/10
 * Time: 18:02
 * Email: languageusa@163.com
 * Author: Dickens7
 */

use Hyperf\Session\Handler\RedisHandler;

return [
    'prefix' => 'SESSION:',
    'cookiePath' => '/',
    'cookieExpires' => 0,
    'cookieDomain' => '',
    'cookieSecure' => false,
    'cookieHttpOnly' => true,
    'maxLifetime' => 7200,
    'handler' => [
        'class' => RedisHandler::class,
        'poolName' => 'session'
    ]
];