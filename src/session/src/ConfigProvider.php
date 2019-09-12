<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * Date: 2019/9/11
 * Time: 18:05
 * Email: languageusa@163.com
 * Author: Dickens7
 */

namespace Hyperf\Session;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
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