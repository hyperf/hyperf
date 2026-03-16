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

namespace Hyperf\ConfigCenter;

use Hyperf\ConfigCenter\Listener\CreateMessageFetcherLoopListener;
use Hyperf\ConfigCenter\Listener\FetchConfigOnBootListener;
use Hyperf\ConfigCenter\Listener\OnPipeMessageListener;
use Hyperf\ConfigCenter\Process\ConfigFetcherProcess;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'processes' => [
                ConfigFetcherProcess::class,
            ],
            'listeners' => [
                FetchConfigOnBootListener::class,
                CreateMessageFetcherLoopListener::class,
                OnPipeMessageListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config-center',
                    'description' => 'The configuration file for config center.',
                    'source' => __DIR__ . '/../publish/config_center.php',
                    'destination' => BASE_PATH . '/config/autoload/config_center.php',
                ],
            ],
        ];
    }
}
