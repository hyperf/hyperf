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
namespace Hyperf\Nsq;

use Hyperf\Nsq\Listener\BeforeMainServerStartListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                BeforeMainServerStartListener::class => 99,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for nsq.',
                    'source' => __DIR__ . '/../publish/nsq.php',
                    'destination' => BASE_PATH . '/config/autoload/nsq.php',
                ],
            ],
        ];
    }
}
