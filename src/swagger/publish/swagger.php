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
    'enable' => true,
    'port' => 9500,
    'json_dir' => BASE_PATH . '/storage/swagger',
    'html' => null,
    'url' => '/swagger',
    'auto_generate' => true,
    'scan' => [
        'paths' => null,
    ],
    'processors' => [
        // users can append their own processors here
    ],
    'server' => [
        'http' => [
            'servers' => [
                [
                    'url' => 'http://127.0.0.1:9501',
                    'description' => 'Test Server',
                ],
            ],
            'info' => [
                'title' => 'Sample API',
                'description' => 'This is a sample API using OpenAPI 3.0 specification',
                'version' => '1.0.0',
            ],
        ],
    ],
];
