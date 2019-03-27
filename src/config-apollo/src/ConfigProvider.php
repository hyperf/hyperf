<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ConfigApollo;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ClientInterface::class => ClientFactory::class,
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
            'configs' => [
                'hyperf/config-apollo' => [
                    __DIR__ . '/../config/apollo.php' => BASE_PATH . '/config/autoload/apollo.php',
                ],
            ],
        ];
    }
}
