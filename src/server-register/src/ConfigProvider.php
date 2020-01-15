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

namespace Hyperf\ServerRegister;

use Hyperf\ServerRegister\Listener\RegisterServerListener;

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
            'listeners' => [
                RegisterServerListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for server register.',
                    'source' => __DIR__ . '/../publish/server_register.php',
                    'destination' => BASE_PATH . '/config/autoload/server_register.php',
                ],
            ],
        ];
    }
}
