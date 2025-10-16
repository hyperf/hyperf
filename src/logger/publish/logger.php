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
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\SyslogFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Level;
use Monolog\Processor\PsrLogMessageProcessor;

use function Hyperf\Support\env;

return [
    // Default Log Channel
    'default' => env('LOG_CHANNEL', 'stack'),

    // Log Channels
    'channels' => [
        'stack' => [
            'handlers' => explode(',', (string) env('LOG_STACK', 'single')),
        ],

        'single' => [
            'handler' => [
                'class' => StreamHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'level' => Level::Debug,
                ],
            ],
            'formatter' => [
                'class' => LineFormatter::class,
                'constructor' => [],
            ],
            'processors' => [],
        ],

        'daily' => [
            'handler' => [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'level' => Level::Debug,
                ],
            ],
            'formatter' => [
                'class' => LineFormatter::class,
                'constructor' => [],
            ],
            'processors' => [],
        ],

        'stderr' => [
            'handler' => [
                'class' => StreamHandler::class,
                'constructor' => [
                    'stream' => 'php://stderr',
                    'level' => Level::Debug,
                ],
            ],
            'formatter' => [
                'class' => LineFormatter::class,
                'constructor' => [],
            ],
            'processors' => [
                PsrLogMessageProcessor::class,
            ],
        ],

        'syslog' => [
            'handler' => [
                'class' => SyslogHandler::class,
                'constructor' => [
                    'level' => Level::Debug,
                    'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
                    'replace_placeholders' => true,
                ],
            ],
            'formatter' => [
                'class' => SyslogFormatter::class,
                'constructor' => [],
            ],
            'processors' => [],
        ],

        'null' => [
            'handler' => ['class' => NullHandler::class],
        ],
    ],
];
