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
namespace Hyperf\Nacos\Process;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nacos\Api\NacosInstance;
use Hyperf\Nacos\Contract\LoggerInterface;
use Hyperf\Nacos\Instance;
use Hyperf\Nacos\Service;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;

class InstanceBeatProcess extends AbstractProcess
{
    /**
     * @var string
     */
    public $name = 'nacos-beat';

    public function handle(): void
    {
        $instance = $this->container->get(Instance::class);
        $nacosInstance = $this->container->get(NacosInstance::class);
        $service = $this->container->get(Service::class);

        $config = $this->container->get(ConfigInterface::class);
        $logger = $this->container->get(LoggerInterface::class);
        while (ProcessManager::isRunning()) {
            sleep($config->get('nacos.client.beat_interval', 5));
            $send = $nacosInstance->beat($service, $instance);
            if ($send) {
                $logger && $logger->debug('nacos send beat success!', compact('instance'));
            } else {
                $logger && $logger->error('nacos send beat fail!', compact('instance'));
            }
        }
    }

    public function isEnable($server): bool
    {
        $config = $this->container->get(ConfigInterface::class);
        return $config->get('nacos.enable', true) && $config->get('nacos.client.beat_enable', false);
    }
}
