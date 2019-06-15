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

namespace Hyperf\ConfigAliyunAcm;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ClientInterface::class => Client::class,
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for aliyun acm.',
                    'source' => __DIR__ . '/../publish/aliyun_acm.php',
                    'destination' => BASE_PATH . '/config/autoload/aliyun_acm.php',
                ],
            ],
        ];
    }
}
