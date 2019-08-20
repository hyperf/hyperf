<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Session;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of session.',
                    'source' => __DIR__ . '/../publish/session.php',
                    'destination' => BASE_PATH . '/config/autoload/session.php',
                ],
            ],
        ];
    }
}
