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
namespace Hyperf\ConfigNacos;

use Hyperf\ConfigNacos\Listener\ConfigReloadListener;
use Hyperf\ConfigNacos\Listener\MainWorkerStartListener;
use Hyperf\ConfigNacos\Listener\OnShutdownListener;
use Hyperf\ConfigNacos\Process\FetchConfigProcess;
use Hyperf\ConfigNacos\Process\InstanceBeatProcess;
use Hyperf\ConfigNacos\Service\IPReader;
use Hyperf\ConfigNacos\Service\IPReaderInterface;
use Hyperf\Nacos\Application;

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
