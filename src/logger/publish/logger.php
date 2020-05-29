<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\StreamHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'level' => Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [],
                ],
            ],
        ],
        // multi processor
        // 'processors' => [
        //     [
        //         'class' => \Monolog\Processor\MemoryPeakUsageProcessor::class,
        //     ],
        //     function (array $record) {
        //         $record['extra']['foo'] = 'bar';
        //     }
        // ],
        // single processor
        // 'processor' => []
    ],
];
