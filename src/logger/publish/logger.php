<?php

return [
    'default' => [
        'handler' => [
            'class' => \Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => \Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => [
            'class' => \Monolog\Formatter\LineFormatter::class,
            'constructor' => [],
        ],
    ],
];
