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
namespace Hyperf\Nacos;

use Hyperf\Nacos\Listener\ConfigReloadListener;
use Hyperf\Nacos\Listener\MainWorkerStartListener;
use Hyperf\Nacos\Listener\OnShutdownListener;
use Hyperf\Nacos\Process\FetchConfigProcess;
use Hyperf\Nacos\Process\InstanceBeatProcess;
use Hyperf\Nacos\Service\IPReader;
use Hyperf\Nacos\Service\IPReaderInterface;
use Hyperf\NacosSdk\Application;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                MainWorkerStartListener::class,
                OnShutdownListener::class,
                ConfigReloadListener::class,
            ],
            'processes' => [
                InstanceBeatProcess::class,
                FetchConfigProcess::class,
            ],
            'dependencies' => [
                Application::class => ClientFactory::class,
                IPReaderInterface::class => IPReader::class,
            ],
            'annotations' => [],
            'publish' => [
                [
                    'id' => 'nacos',
                    'description' => 'The config for nacos.',
                    'source' => __DIR__ . '/../publish/nacos.php',
                    'destination' => BASE_PATH . '/config/autoload/nacos.php',
                ],
            ],
        ];
    }
}
