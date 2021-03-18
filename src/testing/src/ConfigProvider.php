<?php

declare(strict_types=1);

namespace Hyperf\Testing;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'Runtime',
                    'description' => 'The message bag for validation.',
                    'source' => __DIR__ . '/../publish/classmap/Runtime.php',
                    'destination' => BASE_PATH . '/classmap/Runtime.php',
                ],
                [
                    'id' => 'Selector',
                    'description' => 'The message bag for validation.',
                    'source' => __DIR__ . '/../publish/classmap/Selector.php',
                    'destination' => BASE_PATH . '/classmap/Selector.php',
                ],
                [
                    'id' => 'Xdebug2Driver',
                    'description' => 'The message bag for validation.',
                    'source' => __DIR__ . '/../publish/classmap/Xdebug2Driver.php',
                    'destination' => BASE_PATH . '/classmap/Xdebug2Driver.php',
                ],
                [
                    'id' => 'Xdebug3Driver',
                    'description' => 'The message bag for validation.',
                    'source' => __DIR__ . '/../publish/classmap/Xdebug3Driver.php',
                    'destination' => BASE_PATH . '/classmap/Xdebug3Driver.php',
                ],
            ],
        ];
    }
}
